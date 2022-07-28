<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

/**
 * RateRepositoryInterface implements all methods to interact with Rates.
 */
interface ShowRepositoryInterface
{
    /**
     * Get Show followed by user.
     *
     * @param int $userID
     * @return Collection
     */
    public function getShowFollowedByUser(int $userID): Collection;
}
