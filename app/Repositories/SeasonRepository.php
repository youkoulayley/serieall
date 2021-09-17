<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Season;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Class SeasonRepository.
 */
class SeasonRepository
{
    public const RANKING_SEASONS_CACHE_KEY = 'RANKING_SEASONS_CACHE_KEY';

    /**
     * @var Season
     */
    protected $season;

    /**
     * SeasonRepository constructor.
     */
    public function __construct(Season $season)
    {
        $this->season = $season;
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
        return $this->season::where('show_id', '=', $id)
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
        return $this->season::findOrFail($id);
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
        return $this->season::with('show', 'episodes')
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
        return $this->season::with('show')
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
        return $this->season::with(['episodes' => function ($q) {
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
        return $this->season::with(['episodes'])
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
        return $this->season::with(['users' => function ($q) {
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
        return Cache::remember(SeasonRepository::RANKING_SEASONS_CACHE_KEY.'_'.$order, Config::get('constants.cacheDuration.day'), function () use ($order) {
            return $this->season
                ->orderBy('moyenne', $order)
                ->orderBy('nbnotes', $order)
                ->where('nbnotes', '>', config('param.nombreNotesMiniClassementSeasons'))
                ->limit(15)
                ->get();
        });
    }
}
