<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\EpisodeRepositoryInterface;
use App\Models\Episode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Class SeasonRepository.
 */
class EpisodeRepository implements EpisodeRepositoryInterface
{
    /** Constant for cache*/
    public const PLANNING_CACHE_KEY = 'PLANNING_CACHE_KEY';

    public const RANKING_EPISODE_CACHE_KEY = 'RANKING_EPISODE_CACHE_KEY';

    protected $episode;

    /**
     * EpisodeRepository constructor.
     *
     * @internal param Season $season
     */
    public function __construct(Episode $episode)
    {
        $this->episode = $episode;
    }

    /**
     * Get episode by its ID.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Episode
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getEpisodeByID($id)
    {
        return $this->episode::findOrFail($id);
    }

    /**
     * Get episode with seasons, artists and show by its id.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Episode
     */
    public function getEpisodeWithSeasonShowByID($id)
    {
        return $this->episode::with(['season', 'directors', 'writers', 'guests', 'show' => function ($q) {
            $q->with('seasons');
        }])->findOrFail($id);
    }

    /**
     * Get episode by its number and he season id.
     *
     * @param $seasonID
     * @param $episodeNumero
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed|static|null
     */
    public function getEpisodeByEpisodeNumeroAndSeasonID($seasonID, $episodeNumero)
    {
        return $this->episode::with('directors', 'writers', 'guests')
            ->where('episodes.numero', '=', $episodeNumero)
            ->where('episodes.season_id', '=', $seasonID)
            ->first();
    }

    /**
     * Get episode by its number, by the season id and the episode id.
     *
     * @param $seasonID
     * @param $episodeNumero
     * @param $episodeID
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed|static|null
     */
    public function getEpisodeByEpisodeNumeroSeasonIDAndEpisodeID($seasonID, $episodeNumero, $episodeID)
    {
        return $this->episode::with('directors', 'writers', 'guests')
            ->where('episodes.numero', '=', $episodeNumero)
            ->where('episodes.id', '=', $episodeID)
            ->where('episodes.season_id', '=', $seasonID)
            ->first();
    }

    /**
     * Get rates by the id of episode.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed|static|null
     */
    public function getRatesByEpisodeID($id)
    {
        return $this->episode::with(['users' => function ($q) {
            $q->orderBy('episode_user.created_at', 'desc');
        }])
            ->where('episodes.id', '=', $id)
            ->first();
    }

    /**
     * Get Episode with its ID and get seasons and shows in the same time.
     *
     * @param $episode_id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getEpisodeByIDWithSeasonIDAndShowID($episode_id)
    {
        return $this->episode::whereId($episode_id)
            ->with('season:seasons.id', 'show')
            ->select('episodes.id', 'episodes.season_id')
            ->first();
    }

    /**
     * @param $diffusion
     *
     * @return Episode[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getEpisodesDiffusion($diffusion)
    {
        return $this->episode
            ->with(['season' => function ($q) {
                $q->with('show');
            }])
            ->whereBetween($diffusion, [
                Carbon::now()->subMonth(1),
                Carbon::now()->addMonth(1), ])
            ->get();
    }

    /**
     * @param $diffusion
     * @param $date
     *
     * @return Episode[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getPlanningHome($diffusion, $date)
    {
        return Cache::remember(self::PLANNING_CACHE_KEY.'_'.$diffusion.'_'.$date, Config::get('constants.cacheDuration.long'), function () use ($diffusion, $date) {
            return $this->episode
                ->with(['season' => function ($q) {
                    $q->with('show');
                }])
                ->where($diffusion, '=', $date)
                ->orderBy('diffusion_us')
                ->get();
        });
    }

    /**
     * @param $order
     *
     * @return Episode
     */
    public function getRankingEpisodes($order)
    {
        return Cache::remember(self::RANKING_EPISODE_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return $this->episode
                ->orderBy('moyenne', $order)
                ->orderBy('nbnotes', $order)
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementEpisodes'))
                ->limit(15)
                ->get();
        });
    }

    /**
     * @param $order
     *
     * @return Episode
     */
    public function getRankingEpisodesByShow($show, $order)
    {
        return $this->episode
            ->join('seasons', 'episodes.season_id', '=', 'seasons.id')
            ->join('shows', 'seasons.show_id', '=', 'shows.id')
            ->select(DB::raw('episodes.moyenne, episodes.nbnotes, episodes.name, episodes.numero, seasons.name as season_name, shows.name as sname, shows.show_url, picture'))
            ->where('episodes.nbnotes', '>', 0)
            ->where('shows.id', '=', $show)
            ->orderBy('moyenne', $order)
            ->orderBy('nbnotes', $order)
            ->limit(15)
            ->get();
    }
}
