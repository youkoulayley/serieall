<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\SeasonRepositoryInterface;
use App\Models\Season;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Class SeasonRepository.
 */
class SeasonRepository implements SeasonRepositoryInterface
{
    public const RANKING_SEASONS_CACHE_KEY = 'RANKING_SEASONS_CACHE_KEY';

    /**
     * SeasonRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * Récupère les saisons d'une série grâce à son ID.
     * On ajoute également le nombre d'épisodes.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]|Season
     */
    public function getSeasonsCountEpisodesForShowByID($id)
    {
        return Season::where('show_id', '=', $id)
            ->withCount('episodes')
            ->with(['comments' => function ($q) {
                $q->select('thumb', 'commentable_id', 'commentable_type', \DB::raw('count(*) as count_thumb'));
                $q->groupBy('thumb', 'commentable_id', 'commentable_type');
            }])
            ->orderBy('seasons.name', 'asc')
            ->get();
    }

    /**
     * Récupère une saison par son ID.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Season
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSeasonByID($id)
    {
        return Season::findOrFail($id);
    }

    /**
     * Récupère une saison, la série associée et les épisodes associés via l'ID de la saison.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Season
     */
    public function getSeasonShowEpisodesBySeasonID($id)
    {
        return Season::with('show', 'episodes')
            ->findOrFail($id);
    }

    /**
     * Récupère une saison via son ID et récuèper également la série associée.
     *
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Season
     */
    public function getSeasonWithShowByID($id)
    {
        return Season::with('show')
            ->findOrFail($id);
    }

    /**
     * @param $showID
     * @param $seasonName
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSeasonEpisodesBySeasonNameAndShowIDWithCommentCounts($showID, $seasonName)
    {
        return Season::with(['episodes' => function ($q) {
            $q->with(['comments' => function ($q) {
                $q->select('thumb', 'commentable_id', 'commentable_type', \DB::raw('count(*) as count_thumb'));
                $q->groupBy('thumb', 'commentable_id', 'commentable_type');
            }]);
        }])
            ->withCount('episodes')
            ->where('seasons.name', '=', $seasonName)
            ->where('seasons.show_id', '=', $showID)
            ->first();
    }

    /**
     * @param $showID
     * @param $seasonName
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSeasonEpisodesBySeasonNameAndShowID($showID, $seasonName)
    {
        return Season::with(['episodes'])
            ->withCount('episodes')
            ->where('seasons.name', '=', $seasonName)
            ->where('seasons.show_id', '=', $showID)
            ->first();
    }

    /**
     * Récupère la note de la saison en cours.
     *
     * @param $id
     *
     * @return array
     */
    public function getRateBySeasonID($id)
    {
        return Season::with(['users' => function ($q) {
            $q->orderBy('updated_at', 'desc');
            $q->limit(20);
        }, 'users.episode' => function ($q) {
            $q->select('id', 'numero');
        }, 'users.user' => function ($q) {
            $q->select('id', 'username', 'user_url', 'email');
        }])
            ->where('id', '=', $id)
            ->first()
            ->toArray();
    }

    /**
     * @param $order
     *
     * @return Season
     */
    public function getRankingSeasons($order)
    {
        return Cache::remember(self::RANKING_SEASONS_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return Season::orderBy('moyenne', $order)
                ->orderBy('nbnotes', $order)
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementSeasons'))
                ->limit(15)
                ->get();
        });
    }
}
