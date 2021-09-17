<?php

namespace App\Packages\TMDB;

/**
 * Class TMDBArtist.
 *
 * Represents an artist in TMDB
 */
class TMDBArtist
{
    private string $name;
    private string $url;
    private string $profession;
    private string $role;

    public function __construct(
        string $name,
        string $url,
        string $profession,
        string $role
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->profession = $profession;
        $this->role = $role;
    }
}
