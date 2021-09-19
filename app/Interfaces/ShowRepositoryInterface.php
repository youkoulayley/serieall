<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * RateRepositoryInterface implements all methods to interact with Rates.
 */
interface ShowRepositoryInterface
{
    /**
     * @param $inputs
     */
    public function createShowJob($inputs): bool;

    /**
     * @param $inputs
     */
    public function createManuallyShowJob($inputs): bool;

    /**
     * @param $inputs
     */
    public function updateManuallyShowJob($inputs): bool;

    /**
     * @param $id
     * @param $userID
     */
    public function deleteJob($id, $userID): bool;

    /**
     * @param $show_url
     */
    public function getInfoShowFiche($show_url): array;

    /**
     * @return mixed
     */
    public function getAllShowsWithCountSeasonsAndEpisodes();

    /**
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowByURLWithSeasonsAndEpisodes($show_url);

    /**
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowByURL($show_url);

    /**
     * @param $show_url
     *
     * @return mixed
     */
    public function getShowDetailsByURL($show_url);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getByID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getInfoShowByID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getShowSeasonsEpisodesByShowID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getShowActorsByID($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getShowByID($id);

    /**
     * @param $channel
     * @param $genre
     * @param $nationality
     * @param $tri
     * @param $order
     */
    public function getAllShows($channel, $genre, $nationality, $tri, $order): LengthAwarePaginator;

    /**
     * @param $show_name
     *
     * @return mixed
     */
    public function getByName($show_name);

    /**
     * @param $order
     *
     * @return mixed
     */
    public function getRankingShows($order);

    /**
     * @param $nationality
     *
     * @return mixed
     */
    public function getRankingShowsByNationalities($nationality);

    /**
     * @param $category
     *
     * @return mixed
     */
    public function getRankingShowsByGenres($category);

    /**
     * @param $user
     *
     * @return mixed
     */
    public function getShowFollowedByUser($user);

    /**
     * @param $id
     */
    public function getRateByShowID($id): array;
}
