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
    public Show $show;

    public string $poster;

    public string $banner;

    public array $genres;

    public array $creators;

    public array $nationalities;

    public array $channels;

    public array $actors;

    public int $seasons_count;

    public int $episodes_count;

    public function __construct(
        Show $show,
        string $poster,
        string $banner,
        array $genres,
        array $creators,
        array $nationalities,
        array $channels,
        array $actors,
        int $seasons_count,
        int $episodes_count
    ) {
        $this->show = $show;
        $this->poster = $poster;
        $this->banner = $banner;
        $this->genres = $genres;
        $this->creators = $creators;
        $this->nationalities = $nationalities;
        $this->channels = $channels;
        $this->actors = $actors;
        $this->seasons_count = $seasons_count;
        $this->episodes_count = $episodes_count;
    }
}
