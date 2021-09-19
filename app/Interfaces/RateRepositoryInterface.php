<?php

namespace App\Interfaces;

/**
 * RateRepositoryInterface implements all methods to interact with Rates.
 */
interface RateRepositoryInterface
{
    /**
     * @param $user_id
     * @param $episode_id
     * @param $rate
     *
     * @return mixed
     */
    public function RateEpisode($user_id, $episode_id, $rate);

    /**
     * @param $user_id
     * @param $episode_id
     *
     * @return mixed
     */
    public function getRateByUserIDEpisodeID($user_id, $episode_id);

    /**
     * @param $limit
     *
     * @return mixed
     */
    public function getLastRates($limit);

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getRateByUserID($user_id);

    /**
     * Get Average Rate for the given userID.
     *
     * @param $userID
     *
     * @return int
     */
    public function getAvgRateByUserID($userID): int;

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getAllRateByUserID($user_id);

    /**
     * @param $user_id
     * @param $order
     *
     * @return mixed
     */
    public function getRatesAggregateByShowForUser($user_id, $order);

    /**
     * @param $order
     *
     * @return mixed
     */
    public function getRankingShowRedac($order);

    /**
     * @param $order
     *
     * @return mixed
     */
    public function getRankingSeasonRedac($order);

    /**
     * @param $order
     *
     * @return mixed
     */
    public function getRankingEpisodeRedac($order);

    /**
     * @return mixed
     */
    public function getRankingShowChannel();

    /**
     * @param $user
     * @param $order
     *
     * @return mixed
     */
    public function getRankingShowsByUsers($user, $order);

    /**
     * @param $user
     * @param $order
     *
     * @return mixed
     */
    public function getRankingSeasonsByUsers($user, $order);

    /**
     * @param $user
     * @param $order
     *
     * @return mixed
     */
    public function getRankingEpisodesByUsers($user, $order);

    /**
     * @param $user
     * @param $order
     *
     * @return mixed
     */
    public function getRankingPilotByUsers($user, $order);

    /**
     * @return mixed
     */
    public function getShowsMoment();
}
