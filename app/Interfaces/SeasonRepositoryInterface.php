<?php

namespace App\Interfaces;

/**
 * SeasonRepositoryInterface implements all methods to interact with Seasons.
 */
interface SeasonRepositoryInterface
{
    /**
     * @param $id
     *
     * @return mixed
     */
    public function getSeasonsCountEpisodesForShowByID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getSeasonByID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getSeasonShowEpisodesBySeasonID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getSeasonWithShowByID($id);

    /**
     * @param $showID
     * @param $seasonName
     *
     * @return mixed
     */
    public function getSeasonEpisodesBySeasonNameAndShowIDWithCommentCounts($showID, $seasonName);

    /**
     * @param $showID
     * @param $seasonName
     *
     * @return mixed
     */
    public function getSeasonEpisodesBySeasonNameAndShowID($showID, $seasonName);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getRateBySeasonID($id);

    /**
     * @param $order
     *
     * @return mixed
     */
    public function getRankingSeasons($order);
}
