<?php

namespace App\Packages\TMDB;

/**
 * Class TMDBActor.
 *
 * Represents an actor in TMDB
 */
class TMDBActor
{
    private string $name;
    private string $role;

    public function __construct(string $name, string $role)
    {
        $this->name = $name;
        $this->role = $role;
    }
}
