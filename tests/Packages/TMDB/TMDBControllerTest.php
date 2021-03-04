<?php

namespace Http\Controllers;

use App\Packages\TMDB\TMDBController;
use TestCase;

/**
 * Class TMDBControllerTest.
 */
class TMDBControllerTest extends TestCase
{
    public function testGetShow()
    {
        $TMDBController = new TMDBController(
            '1bc0a1c4c4d70f2951d96c3fa4510d05',
        );

        $show = $TMDBController->getShow(4614);
        var_dump($show);
    }

    public function testGetActors()
    {
        $TMDBController = new TMDBController(
            '1bc0a1c4c4d70f2951d96c3fa4510d05',
        );

        $show = $TMDBController->getActors(4614);

        var_dump($show);
    }

    public function testGetSeasons()
    {
        $TMDBController = new TMDBController(
            '1bc0a1c4c4d70f2951d96c3fa4510d05',
        );

        $season = $TMDBController->getSeasonsByShow(4614, 1);

        var_dump($season);
    }
}
