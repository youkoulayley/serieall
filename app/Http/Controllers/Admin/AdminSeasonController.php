<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeasonCreateRequest;
use App\Http\Requests\SeasonUpdateRequest;
use App\Jobs\SeasonDelete;
use App\Jobs\SeasonStore;
use App\Jobs\SeasonUpdate;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdminSeasonController.
 */
class AdminSeasonController extends Controller
{
    protected $seasonRepository;

    protected $showRepository;

    /**
     * AdminArtistController constructor.
     *
     * @internal param ArtistRepository $artistRepository
     */
    public function __construct(SeasonRepository $seasonRepository, ShowRepository $showRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->showRepository = $showRepository;
    }

    /**
     * Affiche la liste des saisons et des épisodes d'une série en fonction de son ID.
     *
     * @param $show_id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @internal param $id
     */
    public function show($show_id)
    {
        $show = $this->showRepository->getShowSeasonsEpisodesByShowID($show_id);

        return view('admin.seasons.show', compact('show'));
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

        return view('admin.seasons.create', compact('show'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SeasonCreateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $this->dispatch(new SeasonStore($inputs));

        return response()->json();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $id
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param $show_id
     */
    public function edit($id)
    {
        $season = $this->seasonRepository->getSeasonShowEpisodesBySeasonID($id);

        return view('admin.seasons.edit', compact('season'));
    }

    /**
     * Mise à jour d'une saison.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SeasonUpdateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        dispatch(new SeasonUpdate($inputs));

        return redirect()->route('admin.seasons.show', $inputs['show_id'])
        ->with('status_header', 'Modification')
        ->with('status', 'La demande de modification a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Suppression d'une saison.
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @internal param $show_id
     * @internal param $season_id
     * @internal param $id
     */
    public function destroy($id)
    {
        $userID = Auth::user()->id;

        dispatch(new SeasonDelete($id, $userID));

        return redirect()->back()
            ->with('status_header', 'Suppression de la saison')
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
        return redirect()->route('admin.seasons.show', $show_id)
            ->with('status_header', 'Saisons en cours d\'ajout')
            ->with('status', 'La demande de création a été effectuée. Le serveur la traitera dès que possible.');
    }
}
