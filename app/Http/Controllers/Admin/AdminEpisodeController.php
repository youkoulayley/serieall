<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EpisodeCreateRequest;
use App\Http\Requests\EpisodeUpdateRequest;
use App\Jobs\EpisodeDelete;
use App\Jobs\EpisodeStore;
use App\Jobs\EpisodeUpdate;
use App\Repositories\EpisodeRepository;
use App\Repositories\SeasonRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdminEpisodeController.
 */
class AdminEpisodeController extends Controller
{
    protected $episodeRepository;

    protected $seasonRepository;

    /**
     * AdminArtistController constructor.
     *
     * @internal param SeasonRepository $seasonRepostiory
     * @internal param SeasonRepository $seasonRepository
     * @internal param ShowRepository $showRepository
     * @internal param ArtistRepository $artistRepository
     */
    public function __construct(
        EpisodeRepository $episodeRepository,
        SeasonRepository $seasonRepository
    ) {
        $this->episodeRepository = $episodeRepository;
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * Affiche le formulaire de création de nouveaux épisodes.
     *
     * @param $season_id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($season_id)
    {
        $season = $this->seasonRepository->getSeasonWithShowByID($season_id);

        return view('admin.episodes.create', compact('season'));
    }

    /**
     * Stocke la nouvelle série.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(EpisodeCreateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $this->dispatch(new EpisodeStore($inputs));

        return response()->json();
    }

    /**
     * Montre le formulaire d'édition d'un épisode.
     *
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $episode = $this->episodeRepository->getEpisodeWithSeasonShowByID($id);
        $directors = formatRequestInVariableNoSpace($episode->directors);
        $writers = formatRequestInVariableNoSpace($episode->writers);
        $guests = formatRequestInVariableNoSpace($episode->guests);

        return view('admin.episodes.edit', compact('episode', 'directors', 'writers', 'guests'));
    }

    /**
     * Met à jour une série.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EpisodeUpdateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        dispatch(new EpisodeUpdate($inputs));

        return redirect()->route('admin.seasons.edit', $inputs['season_id'])
            ->with('status_header', 'Modification de l\'épisode')
            ->with('status', 'La demande de modification a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Suppression d'un épisode.
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

        dispatch(new EpisodeDelete($id, $userID));

        return redirect()->back()
            ->with('status_header', 'Suppression de l\'épisode')
            ->with('status', 'La demande de suppression a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Redirection.
     *
     * @param $season_id
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param $show_id
     */
    public function redirect($season_id)
    {
        return redirect()->route('admin.seasons.edit', $season_id)
            ->with('status_header', 'Episodes en cours d\'ajout')
            ->with('status', 'La demande de création a été effectuée. Le serveur la traitera dès que possible.');
    }
}
