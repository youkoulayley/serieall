<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * RateRepositoryInterface implements all methods to interact with Rates.
 */
interface CommentRepositoryInterface
{
    /**
     * getCountCommentsByUserIDAndThumb returns the summary of comments by counting the number of
     * comments by thumb for the given user.
     *
     * @param int $userID
     * @return Collection
     */
    public function getCountCommentsByUserIDAndThumb(int $userID): Collection;

    /**
     * getCommentsOnShowByUserID gets all comments a user ever made on shows.
     *
     * @param int $userID
     * @param string $pageName
     * @param array $filter
     * @param string $order
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsOnShowByUserID(int $userID, string $pageName, array $filter, string $order): LengthAwarePaginator;

    /**
     * getCommentsOnSeasonByUserID gets all comments a user ever made on seasons.
     *
     * @param int $userID
     * @param string $pageName
     * @param array $filter
     * @param string $order
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsOnSeasonByUserID(int $userID, string $pageName, array $filter, string $order): LengthAwarePaginator;

    /**
     * getCommentsOnEpisodeByUserID gets all comments a user ever made on episodes.
     *
     * @param int $userID
     * @param string $pageName
     * @param array $filter
     * @param string $order
     *
     * @return LengthAwarePaginator
     */
    public function getCommentsOnEpisodeByUserID(int $userID, string $pageName, array $filter, string $order): LengthAwarePaginator;
}
