<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArtistCreateRequest;
use App\Http\Requests\ArtistUpdateRequest;
use App\Jobs\ArtistStore;
use App\Jobs\ArtistUnlink;
use App\Jobs\ArtistUpdate;
use App\Repositories\ArtistRepository;
use App\Repositories\ShowRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * Class AdminArtistController.
 */
class AdminArtistController extends Controller
{
    protected $artistRepository;

    protected $showRepository;

    /**
     * AdminArtistController constructor.
     */
    public function __construct(ArtistRepository $artistRepository, ShowRepository $showRepository)
    {
        $this->artistRepository = $artistRepository;
        $this->showRepository = $showRepository;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $show_id
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function create($show_id)
    {
        $show = $this->showRepository->getByID($show_id);

        return view('admin.artists.create', compact('show'));
    }

    /**
     * Enregistrement d'un nouvel artist, et ajout de son image (l'accès en bas de données est parallélisé).
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param $show_id
     * @internal param ShowCreateRequest|Request $request
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function store(ArtistCreateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);
        $show = $this->showRepository->getByID($inputs['show_id']);

        $idLog = initJob($inputs['user_id'], 'Ajout Manuel', 'Artist', mt_rand());

        foreach ($inputs['artists'] as $key => $actor) {
            // Récupération du nom de l'acteur
            $actorName = $actor['name'];
            // Récupération du rôle
            $actorRole = $actor['role'];
            // On supprime les espaces
            $actor_url = Str::slug(trim($actorName));

            $this->dispatch(new ArtistStore($actorName, $actorRole, $show, $idLog));

            $photo = 'artists.'.$key.'.image';
            // Ajout de l'image
            if (Request::hasFile($photo) && Request::file($photo)->isValid()) {
                $destinationPath = public_path().config('directories.actors');
                $extension = 'jpg';
                $fileName = $actor_url.'.'.$extension;
                Request::file($photo)->move($destinationPath, $fileName);

                $logMessage = '> Ajout de l\'image '.$fileName;
                saveLogMessage($idLog, $logMessage);
            }
        }

        endJob($idLog);

        return response()->json();
    }

    /**
     * Affiche la liste des acteurs d'une série en fonction de son ID.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $show = $this->showRepository->getShowActorsByID($id);

        return view('admin.artists.show', compact('show'));
    }

    /**
     * Editer l'artiste.
     *
     * @param $show_id
     * @param $artist_id
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param $actor_id
     * @internal param int $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($show_id, $artist_id)
    {
        $show = $this->showRepository->getByID($show_id);
        $artist = $this->artistRepository->getActorByShowID($show, $artist_id);

        return view('admin.artists.edit', compact('show', 'artist'));
    }

    /**
     * Modification d'un acteur.
     *
     * @return mixed
     *
     * @internal param $show_id
     * @internal param $artist_id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(ArtistUpdateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $show = $this->showRepository->getByID($inputs['show_id']);
        $artist = $this->artistRepository->getActorByShowID($show, $inputs['artist_id']);

        $idLog = initJob($inputs['user_id'], 'Edition', 'Artist', $artist->id);

        // Ajout de l'image
        if (Request::hasfile('image') && Request::file('image')->isValid()) {
            $destinationPath = public_path().config('directories.actors');
            $extension = 'jpg';
            $fileName = $artist->artist_url.'.'.$extension;
            Request::file('image')->move($destinationPath, $fileName);

            $logMessage = '> Ajout de l\'image '.$fileName;
            saveLogMessage($idLog, $logMessage);
        }

        // Modification du rôle
        $this->dispatch(new ArtistUpdate($artist, $show->id, $inputs['role'], $idLog));

        return redirect()->route('admin.artists.show', $show->id)
            ->with('status_header', 'Modification d\'un acteur')
            ->with('status', 'La demande de modification a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $show_id
     * @param $artist_id
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param $actor_id
     * @internal param int $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unlinkShow($show_id, $artist_id)
    {
        $userID = Auth::user()->id;
        $show = $this->showRepository->getByID($show_id);

        dispatch(new ArtistUnlink($show, $artist_id, $userID));

        return redirect()->back()
            ->with('status_header', 'Suppression d\'un acteur')
            ->with('status', 'La demande de suppression a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Redirection.
     *
     * @param $show_id
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect($show_id)
    {
        return redirect()->route('admin.artists.show', $show_id)
            ->with('status_header', 'Acteurs en cours d\'ajout')
            ->with('status', 'La demande de création d\'acteurs a été effectuée. Le serveur la traitera dès que possible.');
    }
}
