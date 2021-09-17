<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Episode;
use App\Models\Episode_user;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Class RateRepository.
 */
class RateRepository
{
    /** Constant for cache*/
    public const SHOW_MOMENT_CACHE_KEY = 'SHOW_MOMENT_CACHE_KEY';
    public const RANKING_SHOWS_REDAC_CACHE_KEY = 'RANKING_SHOWS_REDAC_CACHE_KEY';
    public const RANKING_SEASONS_REDAC_CACHE_KEY = 'RANKING_SEASONS_REDAC_CACHE_KEY';
    public const RANKING_EPISODES_REDAC_CACHE_KEY = 'RANKING_EPISODES_REDAC_CACHE_KEY';
    public const RANKING_SHOW_CHANNEL_CACHE_KEY = 'RANKING_SHOW_CHANNEL_CACHE_KEY';

    protected $showRepository;
    protected $seasonRepository;
    protected $episodeRepository;

    /**
     * RateRepository constructor.
     */
    public function __construct(
        ShowRepository $showRepository,
        SeasonRepository $seasonRepository,
        EpisodeRepository $episodeRepository
    ) {
        $this->showRepository = $showRepository;
        $this->seasonRepository = $seasonRepository;
        $this->episodeRepository = $episodeRepository;
    }

    /**
     * Rate an episode.
     *
     * @param $user_id
     * @param $episode_id
     * @param $rate
     *
     * @return bool
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function RateEpisode($user_id, $episode_id, $rate)
    {
        // La note existe-elle ?
        $rate_ref = Episode_user::where('episode_id', '=', $episode_id)
            ->where('user_id', '=', $user_id)
            ->first();

        // Objets épisode, saison et séries
        $episode_ref = $this->episodeRepository->getEpisodeByID($episode_id);
        $season_ref = $this->seasonRepository->getSeasonByID($episode_ref->season_id);
        $show_ref = $this->showRepository->getShowByID($season_ref->show_id);

        // Si la note n'exite pas
        if (is_null($rate_ref)) {
            // On l'ajoute
            $episode_ref->users()->attach($user_id, ['rate' => $rate]);

            // On incrémente tous les nombres d'épisodes
            ++$episode_ref->nbnotes;
            ++$season_ref->nbnotes;
            ++$show_ref->nbnotes;
        } else {
            // On la met simplement à jour
            $episode_ref->users()->updateExistingPivot($user_id, ['rate' => $rate]);
        }

        // On calcule sa moyenne et on la sauvegarde dans l'objet
        $mean_episode = Episode_user::where('episode_id', '=', $episode_ref->id)
            ->avg('rate');
        $episode_ref->moyenne = $mean_episode;
        $episode_ref->save();

        // On calcule la moyenne de la saison et on la sauvegarde dans l'objet
        $mean_season = Episode::where('season_id', '=', $season_ref->id)
            ->where('moyenne', '>', 0)
            ->avg('moyenne');

        $season_ref->moyenne = $mean_season;
        $season_ref->save();

        // On calcule la moyenne de la série et on la sauvegarde dans l'objet
        $mean_show = Season::where('show_id', '=', $show_ref->id)
            ->where('moyenne', '>', 0)
            ->avg('moyenne');

        $show_ref->moyenne = $mean_show;
        $show_ref->save();

        return true;
    }

    /**
     * Get Rate of an episode by a user.
     *
     * @param $user_id
     * @param $episode_id
     *
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function getRateByUserIDEpisodeID($user_id, $episode_id)
    {
        return Episode_user::whereEpisodeId($episode_id)
            ->whereUserId($user_id)
            ->pluck('rate')
            ->first();
    }

    /**
     * Get Last 20 Rates.
     *
     * @param $limit
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getLastRates($limit)
    {
        return Episode_user::with(['user', 'episode' => function ($q) {
            $q->with('season');
            $q->with('show');
        }])->limit($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get lasts rates of a user.
     *
     * @param $user_id
     * @param $episode_id
     *
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function getRateByUserID($user_id)
    {
        return Episode_user::with(['user', 'episode' => function ($q) {
            $q->with('season');
            $q->with('show');
        }])
            ->whereUserId($user_id)
            ->limit(15)
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Get all rates of a user.
     *
     * @param $user_id
     * @param $episode_id
     *
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public function getAllRateByUserID($user_id)
    {
        return Episode_user::whereUserId($user_id);
    }

    /**
     * Get rates aggregate by show for a particular user.
     *
     * @param $user_id
     * @param $order
     *
     * @return array
     */
    public function getRatesAggregateByShowForUser($user_id, $order)
    {
        $sql = "select sh.thetvdb_id, sh.name, sh.show_url, sh.format, sh.format * COUNT(eu.rate) AS minutes, COUNT(eu.rate) nb_rate, TRIM(ROUND(AVG(eu.rate),2))+0 avg_rate, u.username
            FROM shows sh, seasons s, episodes e, users u, episode_user eu
            WHERE sh.id = s.show_id
            AND s.id = e.season_id
            AND eu.episode_id = e.id
            AND eu.user_id = u.id
            AND u.id = '$user_id'
            GROUP BY sh.name,sh.show_url, u.username
            ORDER BY $order";

        return DB::select(DB::raw($sql));
    }

    /**
     * Get Ranking of shows for the redaction team.
     *
     * @param $order
     *
     * @return
     */
    public function getRankingShowRedac($order)
    {
        return Cache::remember(RateRepository::RANKING_SHOWS_REDAC_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return Episode_user::join('users', 'episode_user.user_id', '=', 'users.id')
               ->join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
               ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
               ->join('shows', 'seasons.show_id', '=', 'shows.id')
               ->groupBy('shows.name')
               ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, shows.name, shows.show_url'))
               ->limit(15)
               ->where('users.role', '<', 4)
               ->havingRaw('nbnotes > '.config('param.nombreNotesMiniClassementShows'))
               ->orderBy('moyenne', $order)
               ->get();
        });
    }

    /**
     * Get Ranking of seasons for the redaction team.
     *
     * @param $order
     *
     * @return
     */
    public function getRankingSeasonRedac($order)
    {
        return Cache::remember(RateRepository::RANKING_SEASONS_REDAC_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return Episode_user::join('users', 'episode_user.user_id', '=', 'users.id')
                ->join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
                ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
                ->join('shows', 'seasons.show_id', '=', 'shows.id')
                ->groupBy('shows.name')
                ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, seasons.name, shows.name as sname, shows.show_url'))
                ->limit(15)
                ->where('users.role', '<', 4)
                ->havingRaw('nbnotes > '.config('param.nombreNotesMiniClassementSeasons'))
                ->orderBy('moyenne', $order)
                ->get();
        });
    }

    /**
     * Get Ranking of episodes for the redaction team.
     *
     * @param $order
     *
     * @return
     */
    public function getRankingEpisodeRedac($order)
    {
        return Cache::remember(RateRepository::RANKING_EPISODES_REDAC_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return Episode_user::join('users', 'episode_user.user_id', '=', 'users.id')
                ->join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
                ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
                ->join('shows', 'seasons.show_id', '=', 'shows.id')
                ->groupBy('shows.name')
                ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, episodes.name, episodes.numero, episodes.id, seasons.name as season_name, shows.name as sname, shows.show_url'))
                ->limit(15)
                ->where('users.role', '<', 4)
                ->havingRaw('nbnotes > '.config('param.nombreNotesMiniClassementEpisodes'))
                ->orderBy('moyenne', $order)
                ->get();
        });
    }

    /**
     * Get Ranking of channels.
     *
     * @param $order
     *
     * @return
     */
    public function getRankingShowChannel()
    {
        return Cache::remember(RateRepository::RANKING_SHOW_CHANNEL_CACHE_KEY, Config::get('constants.cacheDuration.day'), function () {
            return Episode_user::join('users', 'episode_user.user_id', '=', 'users.id')
            ->join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->join('channel_show', 'shows.id', '=', 'channel_show.show_id')
            ->join('channels', 'channel_show.channel_id', '=', 'channels.id')
            ->groupBy('shows.name')
            ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, channels.name'))
            ->limit(15)
            ->where('users.role', '<', 4)
            ->havingRaw('nbnotes > '.config('param.nombreNotesMiniClassementShows'))
            ->orderBy('moyenne')
            ->get();
        });
    }

    /**
     * @param $user
     * @param $order
     *
     * @return Show
     */
    public function getRankingShowsByUsers($user, $order)
    {
        return Episode_user::join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->orderBy('moyenne', $order)
            ->orderBy('nbnotes', $order)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('id', '=', $user);
            })
            ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, shows.show_url, shows.name'))
            ->groupBy('shows.name', 'shows.show_url')
            ->limit(10)
            ->get();
    }

    /**
     * @param $user
     * @param $order
     *
     * @return Show
     */
    public function getRankingSeasonsByUsers($user, $order)
    {
        return Episode_user::join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->orderBy('moyenne', $order)
            ->orderBy('nbnotes', $order)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('id', '=', $user);
            })
            ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, shows.show_url, shows.name as sname, seasons.name as season_name'))
            ->groupBy('sname', 'shows.show_url', 'season_name')
            ->limit(10)
            ->get();
    }

    /**
     * @param $user
     * @param $order
     *
     * @return Show
     */
    public function getRankingEpisodesByUsers($user, $order)
    {
        return Episode_user::join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->orderBy('moyenne', $order)
            ->orderBy('nbnotes', $order)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('id', '=', $user);
            })
            ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, shows.show_url, shows.name as sname, seasons.name as season_name, episodes.name, episodes.numero'))
            ->groupBy('sname', 'season_name', 'episodes.name', 'episodes.numero')
            ->limit(10)
            ->get();
    }

    /**
     * @param $user
     * @param $order
     *
     * @return Show
     */
    public function getRankingPilotByUsers($user, $order)
    {
        return Episode_user::join('episodes', 'episode_user.episode_id', '=', 'episodes.id')
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->orderBy('moyenne', $order)
            ->orderBy('nbnotes', $order)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('id', '=', $user);
            })
            ->where('seasons.name', '=', 1)
            ->where('episodes.numero', '=', 1)
            ->select(DB::raw('TRIM(ROUND(avg(episode_user.rate),2))+0 as moyenne, count(episode_user.rate) as nbnotes, shows.show_url, shows.name as sname, seasons.name as season_name, episodes.name, episodes.numero'))
            ->groupBy('sname', 'season_name', 'episodes.name', 'episodes.numero')
            ->limit(10)
            ->get();
    }

    public function getShowsMoment()
    {
//        return Cache::remember(RateRepository::SHOW_MOMENT_CACHE_KEY, Config::get('constants.cacheDuration.long'), function () {
        return Episode_user::leftJoin('episodes', 'episode_user.episode_id', '=', 'episodes.id')
                ->leftJoin('seasons', 'episodes.season_id', '=', 'seasons.id')
                ->leftJoin('shows', 'seasons.show_id', '=', 'shows.id')
                ->select(DB::raw('shows.name, shows.show_url, shows.moyenne, shows.nbnotes ,COUNT(episode_user.rate) nbnotes_last_week'))
                ->whereBetween('episode_user.created_at', [
                    Carbon::now()->subWeek(),
                    Carbon::now(), ])
                ->orderBy('nbnotes_last_week', 'DESC')
//                ->orderBy('nbnotes')
                ->groupBy('shows.name', 'shows.nbnotes', 'shows.moyenne')
                ->limit(10)
                ->get();
//        });
    }
}
