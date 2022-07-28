<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * RateRepositoryInterface implements all methods to interact with Rates.
 */
interface RateRepositoryInterface
{
    /**
     * getWatchTime gets the watch time for the user based on the episodes he gave a rate.
     *
     * @param int $userID
     * @return string
     */
    public function getWatchTimeByUserID(int $userID): string;

    /**
     * Get Average Rate for the given userID.
     */
    public function getAvgRateAndRatesCountByUserID(int $userID): array;

    /**
     * Get lasts rates for the given user.
     *
     * @param int $userID
     * @return array
     */
    public function getLastRatesByUserID(int $userID): array;

    /**
     * Get a charts of every rate/count for the given userID.
     *
     * @param int $userID
     * @return Collection
     */
    public function getChartRatesByUserID(int $userID): Collection;

    /**
     * Get rates aggregate by show for the given user.
     * The second parameter change the order. You can select any field in the request.
     *
     * @param int $userID
     * @param string $order
     *
     * @return array
     */
    public function getRatesAggregateShowByUserID(int $userID, string $order): array;

    /**
     * Get all rates of a user.
     *
     * @param int $userID
     * @return Builder
     */
    public function getAllRateByUserID(int $userID): Builder;
}
