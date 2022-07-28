<?php

namespace App\Packages\TMDB;

/**
 * Class TMDBArtist.
 *
 * Represents an artist in TMDB
 */
class TMDBArtist
{
    public string $name;

    public string $role;

    public string $image;

    public function __construct(
        string $name,
        string $role,
        string $image
    ) {
        $this->name = $name;
        $this->role = $role;
        $this->image = $image;
    }
}
