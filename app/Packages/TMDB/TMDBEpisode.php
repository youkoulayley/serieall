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
    private Episode $episode;
    private array $guests;
    private array $writers;
    private array $directors;

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
