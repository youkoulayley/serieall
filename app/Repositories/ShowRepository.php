<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\ShowRepositoryInterface;
use App\Jobs\ShowAddFromTMDB;
use App\Jobs\ShowAddManually;
use App\Jobs\ShowDelete;
use App\Jobs\ShowUpdateManually;
use App\Models\Comment;
use App\Models\Show;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class ShowRepository.
 */
class ShowRepository implements ShowRepositoryInterface
{
    /** Constant for cache*/
    public const LAST_ADDED_SHOW_CACHE_KEY = 'LAST_ADDED_SHOW_CACHE_KEY';

    public const RANKING_SHOWS_CACHE_KEY = 'RANKING_SHOWS_CACHE_KEY';

    public const THUMB_SHOW_CACHE_KEY = 'THUMB_SHOW_CACHE_KEY';

    /**
     * ShowRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * Get Show followed by user.
     *
     * @param int $userID
     * @return Collection
     */
    public function getShowFollowedByUser(int $userID): Collection
    {
        return Show::join('show_user', 'shows.id', '=', 'show_user.show_id')
            ->join('users', 'users.id', '=', 'show_user.user_id')
            ->orderBy('shows.name')
            ->where('users.id', '=', $userID)
            ->select(DB::raw('shows.id as sid, users.id as uid, shows.name as name, shows.show_url as show_url, show_user.state as state, show_user.message as message'))
            ->get();
    }

    // ***********************
    //    TODO
    // ***********************

    /**
     * On vérifie si la série n'a pas déjà été récupérée.
     * Si c'est le cas, on renvoie une erreur.
     * Sinon, on lance le job d'ajout via TheTVDB et on renvoi un status OK.
     *
     * @param $inputs
     */
    public function createShowJob($inputs): bool
    {
        $checkIDTMDB = Show::where('tmdb_id', $inputs['tmdb_id'])->first();

        if ($checkIDTMDB === null) {
            dispatch(new ShowAddFromTMDB($inputs));

            return true;
        }

        return false;
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
        $verifURLShow = Show::where('show_url', $URLShow)->first();

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
            $seasons = (new SeasonRepository())->getSeasonsCountEpisodesForShowByID($show->id);
        } elseif ('show.details' === Route::current()->getName()) {
            $show = $this->getShowDetailsByURL($show_url);
            if (is_null($show)) {
                //Show not found -> empty array
                return [];
            }
            $seasons = (new SeasonRepository())->getSeasonsCountEpisodesForShowByID($show->id);
        } else {
            $show = $this->getShowByURLWithSeasonsAndEpisodes($show_url);
            $seasons = [];
        }
        $articles = [];

        $nbcomments = Cache::rememberForever(self::THUMB_SHOW_CACHE_KEY.$show->id, function () use ($show) {
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
     * Get Shows with channels, nationalities, and the count of episodes and seasons.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]|Show
     */
    public function getAllShowsWithCountSeasonsAndEpisodes()
    {
        return Show::with('nationalities', 'channels')
            ->withCount('episodes')
            ->withCount('seasons')
            ->get();
    }

    /**
     * Get Show by show_url with channels, nationalities, seasons, episodes and genres.
     *
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowByURLWithSeasonsAndEpisodes($show_url)
    {
        return Show::where('show_url', $show_url)
            ->with('seasons', 'episodes', 'genres', 'nationalities', 'channels')
            ->first();
    }

    /**
     * Get Show by show_url with channels, nationalities, creators and genres.
     *
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowByURL($show_url)
    {
        return Show::where('show_url', $show_url)
            ->with('genres', 'nationalities', 'channels')
            ->first();
    }

    /**
     * Get Show by show_url with channels, nationalities, creators, genres and all the actors.
     *
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowDetailsByURL($show_url)
    {
        return Show::where('shows.show_url', '=', $show_url)->with(['channels', 'nationalities', 'creators', 'genres', 'actors' => function ($q) {
            $q->select('artists.id', 'artists.name', 'artists.artist_url', 'artistables.role')
                ->orderBy('artists.name', 'asc');
        }])->first();
    }

    /**
     * Get Show.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Show
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByID($id)
    {
        return Show::findOrFail($id);
    }

    /**
     * Get shows with channels, nationalities, creators and genres.
     *
     * @param $id
     *
     * @return Show|\Illuminate\Database\Eloquent\Builder|Show
     */
    public function getInfoShowByID($id)
    {
        return Show::where('shows.id', '=', $id)
            ->with(['channels', 'nationalities', 'creators', 'genres'])
            ->first();
    }

    /**
     * Get Show with seasons and episodes.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Show
     */
    public function getShowSeasonsEpisodesByShowID($id)
    {
        return Show::with(['seasons' => function ($q) {
            $q->with('episodes');
        }])
            ->findOrFail($id);
    }

    /**
     * Get Show with actors.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Show
     */
    public function getShowActorsByID($id)
    {
        return Show::with('actors')
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
        return Show::findOrFail($id);
    }

    /**
     * Get shows.
     *
     * @param string $genre
     * @param string $channel
     * @param string $nationality
     * @param string $tri
     */
    public function getAllShows($channel, $genre, $nationality, $tri, $order): LengthAwarePaginator
    {
        $shows = Show::where(function ($q) use ($genre) {
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
     * Get show by name.
     *
     * @param $show_name
     *
     * @return Show|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getByName($show_name)
    {
        return Show::whereName($show_name)->first();
    }

    /**
     * Get ranking Shows.
     *
     * @param $order
     *
     * @return Show
     */
    public function getRankingShows($order)
    {
        return Cache::remember(self::RANKING_SHOWS_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return Show::orderBy('moyenne', $order)
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementShows'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * Get ranking Show by Nationalities.
     *
     * @param $nationality
     */
    public function getRankingShowsByNationalities($nationality)
    {
        return Cache::remember(self::RANKING_SHOWS_CACHE_KEY.'_'.$nationality, Config::get('constants.cacheDuration.day'), function () use ($nationality) {
            return Show::orderBy('moyenne', 'desc')
                ->whereHas('nationalities', function ($q) use ($nationality) {
                    $q->where('name', '=', $nationality);
                })
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementShows'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * Get ranking Show by Genre.
     *
     * @param $category
     */
    public function getRankingShowsByGenres($category)
    {
        return Cache::remember(self::RANKING_SHOWS_CACHE_KEY.'_'.$category, Config::get('constants.cacheDuration.day'), function () use ($category) {
            return Show::orderBy('moyenne', 'desc')
                ->whereHas('genres', function ($q) use ($category) {
                    $q->where('name', '=', $category);
                })
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementShows'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * Get the 12 Last Added Shows.
     *
     * @return mixed
     */
    public function getLastAddedShows()
    {
        return Show::orderBy('created_at', 'desc')->limit(12)->get();
    }

    /**
     * Get Rate by ID.
     *
     * @param $id
     */
    public function getRateByShowID($id): array
    {
        return Show::with(['rates' => function ($q) {
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
