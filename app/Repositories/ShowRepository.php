<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Jobs\ShowAddFromTVDB;
use App\Jobs\ShowAddManually;
use App\Jobs\ShowDelete;
use App\Jobs\ShowUpdateManually;
use App\Models\Comment;
use App\Models\Show;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class ShowRepository.
 */
class ShowRepository
{
    /** Constant for cache*/
    public const LAST_ADDED_SHOW_CACHE_KEY = 'LAST_ADDED_SHOW_CACHE_KEY';
    public const RANKING_SHOWS_CACHE_KEY = 'RANKING_SHOWS_CACHE_KEY';
    public const THUMB_SHOW_CACHE_KEY = 'THUMB_SHOW_CACHE_KEY';

    protected $show;
    protected $seasonRepository;
    protected $articleRepository;

    /**
     * ShowRepository constructor.
     *
     * @param \App\Repositories\SeasonRepository $seasonRepository
     */
    public function __construct(
        Show $show,
        SeasonRepository $seasonRepository,
        ArticleRepository $articleRepository
    ) {
        $this->show = $show;
        $this->seasonRepository = $seasonRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * On vérifie si la série n'a pas déjà été récupérée.
     * Si c'est le cas, on renvoie une erreur.
     * Sinon, on lance le job d'ajout via TheTVDB et on renvoi un status OK.
     *
     * @param $inputs
     */
    public function createShowJob($inputs): bool
    {
        $checkIDTheTVDB = $this->show::where('thetvdb_id', $inputs['thetvdb_id'])->first();

        if (null === $checkIDTheTVDB) {
            dispatch(new ShowAddFromTVDB($inputs));
            $dispatchOK = true;
        } else {
            $dispatchOK = false;
        }

        return $dispatchOK;
    }

    /**
     * On vérifie si la série n'a pas déjà été ajoutée.
     * Si c'est le cas, on renvoie une erreur.
     * Sinon, on lance le job de création manuelle et on renvoi un status OK.
     *
     * @param $inputs
     */
    public function createManuallyShowJob($inputs): bool
    {
        $URLShow = Str::slug($inputs['name']);
        $verifURLShow = $this->show::where('show_url', $URLShow)->first();

        if (null === $verifURLShow) {
            dispatch(new ShowAddManually($inputs));
            $createOK = true;
        } else {
            $createOK = false;
        }

        return $createOK;
    }

    /**
     * On crée un job de mise à jour manuel et on renvoi OK.
     *
     * @param $inputs
     */
    public function updateManuallyShowJob($inputs): bool
    {
        dispatch(new ShowUpdateManually($inputs));

        return true;
    }

    /**
     * On crée un job de suppression d'une série et on renvoi OK.
     *
     * @param $id
     * @param $userID
     *
     * @internal param $inputs
     */
    public function deleteJob($id, $userID): bool
    {
        dispatch(new ShowDelete($id, $userID));

        return true;
    }

    /**
     * SITE.
     */

    /**
     * Récupération des informations de la fiche:
     * Série, saisons, épisodes, genres, nationalités, chaines, note, résumé.
     *
     * @param $show_url
     */
    public function getInfoShowFiche($show_url): array
    {
        // En fonction de la route, on récupère les informations sur la série différemment
        //TODO : ne pas faire ce swicth dans le repository
        if ('show.fiche' === Route::current()->getName()) {
            $show = $this->getShowByURL($show_url);
            if (is_null($show)) {
                //Show not found -> empty array
                return [];
            }
            $seasons = $this->seasonRepository->getSeasonsCountEpisodesForShowByID($show->id);
        } elseif ('show.details' === Route::current()->getName()) {
            $show = $this->getShowDetailsByURL($show_url);
            if (is_null($show)) {
                //Show not found -> empty array
                return [];
            }
            $seasons = $this->seasonRepository->getSeasonsCountEpisodesForShowByID($show->id);
        } else {
            $show = $this->getShowByURLWithSeasonsAndEpisodes($show_url);
            $seasons = [];
        }
        $articles = [];

        $nbcomments = Cache::rememberForever(ShowRepository::THUMB_SHOW_CACHE_KEY.$show->id, function () use ($show) {
            return Comment::groupBy('thumb')
                ->select('thumb', \DB::raw('count(*) as count_thumb'))
                ->where('commentable_id', '=', $show->id)
                ->where('commentable_type', '=', 'App\Models\Show')
                ->get();
        });

        $showPositiveComments = $nbcomments->where('thumb', '=', '1')->first();
        $showNeutralComments = $nbcomments->where('thumb', '=', '2')->first();
        $showNegativeComments = $nbcomments->where('thumb', '=', '3')->first();

        // On récupère les saisons, genres, nationalités et chaines

        $genres = formatRequestInVariable($show->genres);
        $nationalities = formatRequestInVariable($show->nationalities);
        $channels = formatRequestInVariable($show->channels);

        // On récupère la note de la série, et on calcule la position sur le cercle
        $noteCircle = noteToCircle($show->moyenne);

        // Détection du résumé à afficher (fr ou en)
        if (empty($show->synopsis_fr)) {
            $synopsis = $show->synopsis;
        } else {
            $synopsis = $show->synopsis_fr;
        }

        // Faut-il couper le résumé ? */
        $numberCharaMaxResume = config('param.nombreCaracResume');
        if (strlen($synopsis) <= $numberCharaMaxResume) {
            $showSynopsis = $synopsis;
            $fullSynopsis = false;
        } else {
            $showSynopsis = cutResume($synopsis);
            $fullSynopsis = true;
        }

        return compact('show', 'seasons', 'genres', 'nationalities', 'channels', 'noteCircle', 'synopsis', 'showSynopsis', 'fullSynopsis', 'showPositiveComments', 'showNeutralComments', 'showNegativeComments', 'articles');
    }

    /**
     * SITE.
     */

    /**
     * GET FONCTIONS.
     */

    /**
     * Récupère la liste des séries avec le compte des saisons et des épisodes, la ou les nationalités, et la ou les chaînes.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]|Show
     */
    public function getAllShowsWithCountSeasonsAndEpisodes()
    {
        return $this->show::with('nationalities', 'channels')
            ->withCount('episodes')
            ->withCount('seasons')
            ->get();
    }

    /**
     * Récupère la série avec son paramètre URL. On ajoute les saisons, les épisodes, les genres, les nationalités et les chaînes.
     *
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowByURLWithSeasonsAndEpisodes($show_url)
    {
        return $this->show::where('show_url', $show_url)
            ->with('seasons', 'episodes', 'genres', 'nationalities', 'channels')
            ->first();
    }

    /**
     * Récupère la série avec son paramètre URL. On ajoute les genres, les nationalités et les chaînes.
     *
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowByURL($show_url)
    {
        return $this->show::where('show_url', $show_url)
            ->with('genres', 'nationalities', 'channels')
            ->first();
    }

    /**
     * On récupère les détails de la série avec son paramètre URL.
     * La différence avec la requête du dessus est surtout le fait que l'on récupère tout le casting.
     *
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowDetailsByURL($show_url)
    {
        return $this->show::where('shows.show_url', '=', $show_url)->with(['channels', 'nationalities', 'creators', 'genres', 'actors' => function ($q) {
            $q->select('artists.id', 'artists.name', 'artists.artist_url', 'artistables.role')
                ->orderBy('artists.name', 'asc');
        }])->first();
    }

    /**
     * On récupère une série grâce à son ID.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Show
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByID($id)
    {
        return $this->show::findOrFail($id);
    }

    /**
     * On récupère les détails de la série avec son ID.
     *
     * @param $id
     *
     * @return Show|\Illuminate\Database\Eloquent\Builder|Show
     */
    public function getInfoShowByID($id)
    {
        return $this->show::where('shows.id', '=', $id)
            ->with(['channels', 'nationalities', 'creators', 'genres'])
            ->first();
    }

    /**
     * Récupère la série grâce à son ID, les saisons et les épisodes associés.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Show
     */
    public function getShowSeasonsEpisodesByShowID($id)
    {
        return $this->show::with(['seasons' => function ($q) {
            $q->with('episodes');
        }])
            ->findOrFail($id);
    }

    /**
     * Récupère la série grâce à son ID, et les acteurs associés.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Show
     */
    public function getShowActorsByID($id)
    {
        return $this->show::with('actors')
            ->findOrFail($id);
    }

    /**
     * Get show by ID.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getShowByID($id)
    {
        return $this->show::findOrFail($id);
    }

    /**
     * Récupère toutes les séries.
     *
     * @param string $genre
     * @param string $channel
     * @param string $nationality
     * @param string $tri
     */
    public function getAllShows($channel, $genre, $nationality, $tri, $order): LengthAwarePaginator
    {
        $shows = $this->show::where(function ($q) use ($genre) {
            $q->whereHas('genres', function ($q) use ($genre) {
                $q->where('name', 'like', '%'.$genre.'%');
            });
            if (empty($genre)) {
                $q->orDoesntHave('genres');
            }
        })
        ->where(function ($q) use ($channel) {
            $q->whereHas('channels', function ($q) use ($channel) {
                $q->where('name', 'like', '%'.$channel.'%');
            });

            if (empty($channel)) {
                $q->orDoesntHave('channels');
            }
        })
        ->where(function ($q) use ($nationality) {
            $q->whereHas('nationalities', function ($q) use ($nationality) {
                $q->where('name', 'like', '%'.$nationality.'%');
            });

            if (empty($nationality)) {
                $q->orDoesntHave('nationalities');
            }
        })
        ->orderBy($tri, $order)
        ->paginate(12);

        return $shows;
    }

    /**
     * @param $show_name
     *
     * @return Show|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getByName($show_name)
    {
        return $this->show->whereName($show_name)->first();
    }

    /**
     * @param $order
     *
     * @return Show
     */
    public function getRankingShows($order)
    {
        return Cache::remember(ShowRepository::RANKING_SHOWS_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return $this->show
                ->orderBy('moyenne', $order)
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementShows'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * @param $nationality
     *
     * @return Show
     */
    public function getRankingShowsByNationalities($nationality)
    {
        return Cache::remember(ShowRepository::RANKING_SHOWS_CACHE_KEY.'_'.$nationality, Config::get('constants.cacheDuration.day'), function () use ($nationality) {
            return $this->show
                ->orderBy('moyenne', 'desc')
                ->whereHas('nationalities', function ($q) use ($nationality) {
                    $q->where('name', '=', $nationality);
                })
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementShows'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * @param $category
     *
     * @return Show
     */
    public function getRankingShowsByGenres($category)
    {
        return Cache::remember(ShowRepository::RANKING_SHOWS_CACHE_KEY.'_'.$category, Config::get('constants.cacheDuration.day'), function () use ($category) {
            return $this->show
                ->orderBy('moyenne', 'desc')
                ->whereHas('genres', function ($q) use ($category) {
                    $q->where('name', '=', $category);
                })
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementShows'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * @param $user
     *
     * @return mixed
     */
    public function getShowFollowedByUser($user)
    {
        return $this->show
            ->join('show_user', 'shows.id', '=', 'show_user.show_id')
            ->join('users', 'users.id', '=', 'show_user.user_id')
            ->orderBy('shows.name')
            ->where('users.id', '=', $user)
            ->select(DB::raw('shows.id as sid, users.id as uid, shows.name as name, shows.show_url as show_url, show_user.state as state, show_user.message as message'))
            ->get();
    }

    public function getLastAddedShows()
    {
//        return Cache::remember(ShowRepository::LAST_ADDED_SHOW_CACHE_KEY, Config::get('constants.cacheDuration.medium'), function () {
        return $this->show->orderBy('created_at', 'desc')->limit(12)->get();
//        });
    }

    /**
     * Récupère la note de la série en cours.
     *
     * @param $id
     *
     * @return array
     */
    public function getRateByShowID($id)
    {
        return $this->show::with(['rates' => function ($q) {
            $q->orderBy('updated_at', 'desc');
            $q->limit(20);
        }, 'rates.episode' => function ($q) {
            $q->select('id', 'numero', 'season_id');
            $q->with(['season' => function ($s) {
                $s->select('id', 'name');
            }]);
        }, 'rates.user' => function ($q) {
            $q->select('id', 'username', 'user_url', 'email');
        }])
            ->where('id', '=', $id)
            ->first()
            ->toArray();
    }
}
