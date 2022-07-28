<?php

namespace App\Packages\TMDB;

use App\Models\Episode;

/**
 * Class TMDBEpisode.
 *
 * Represents an episode in TMDB
 */
class TMDBEpisode
{
    public Episode $episode;

    public array $guests;

    public array $writers;

    public array $directors;

    public function __construct(
        Episode $episode,
        array $guests,
        array $writers,
        array $directors
    ) {
        $this->episode = $episode;
        $this->guests = $guests;
        $this->writers = $writers;
        $this->directors = $directors;
    }
}
