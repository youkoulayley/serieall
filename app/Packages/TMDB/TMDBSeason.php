<?php

namespace App\Packages\TMDB;

use App\Models\Season;

/**
 * Class TMDBSeason.
 *
 * Represents a season in TMDB
 */
class TMDBSeason
{
    private Season $season;
    private array $episodes;

    public function __construct(
        Season $season,
        array $episodes
    ) {
        $this->season = $season;
        $this->episodes = $episodes;
    }
}
