<?php

namespace App\Packages\TMDB;

use App\Models\Show;

/**
 * Class TMDBShow.
 *
 * Represents a show in TMDB
 */
class TMDBShow
{
    private Show $show;
    private array $genres;
    private array $creators;
    private array $nationalities;
    private array $channels;
    private int $seasons_count;
    private int $episodes_count;

    public function __construct(Show $show, array $genres, array $creators, array $nationalities, array $channels, int $seasons_count, int $episodes_count)
    {
        $this->show = $show;
        $this->genres = $genres;
        $this->creators = $creators;
        $this->nationalities = $nationalities;
        $this->channels = $channels;
        $this->seasons_count = $seasons_count;
        $this->episodes_count = $episodes_count;
    }
}
