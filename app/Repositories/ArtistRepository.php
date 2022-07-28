<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Artist;
use App\Models\Show;
use Illuminate\Support\Facades\DB;

/**
 * Class ArtistRepository.
 */
class ArtistRepository
{
    /**
     * @var Artist
     */
    protected $artist;

    protected $show;

    protected $showRepository;

    /**
     * ArtistRepository constructor.
     */
    public function __construct(
        Artist $artist,
        Show $show,
        ShowRepository $showRepository
    ) {
        $this->artist = $artist;
        $this->show = $show;
        $this->showRepository = $showRepository;
    }

    /**
     * On récupère tous les artistes.
     *
     * @return mixed
     */
    public function getArtists()
    {
        return DB::table('artists')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * On récupère les artistes d'une série en fonction de son ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @internal param $id
     */
    public function getActorsByShowID(Show $show)
    {
        return $show->actors()->get();
    }

    /**
     * On récupère un artiste d'une série en fonction de son ID.
     *
     * @param $actor
     *
     * @return \Illuminate\Database\Eloquent\Collection
     *
     * @internal param $id
     */
    public function getActorByShowID(Show $show, $actor)
    {
        return $show->actors()->findOrFail($actor);
    }
}
