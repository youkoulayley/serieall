<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowCreateManuallyRequest;
use App\Http\Requests\ShowCreateRequest;
use App\Http\Requests\ShowUpdateManuallyRequest;
use App\Jobs\AddTMDBID;
use App\Jobs\ClearDoublons;
use App\Jobs\OneShowUpdateFromTMDB;
use App\Jobs\ShowUpdateFromTMDB;
use App\Repositories\ArtistRepository;
use App\Repositories\ChannelRepository;
use App\Repositories\GenreRepository;
use App\Repositories\LogRepository;
use App\Repositories\NationalityRepository;
use App\Repositories\SeasonRepository;
use App\Repositories\ShowRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Class AdminShowController.
 */
class AdminShowController extends Controller
{
    protected $showRepository;

    protected $artistRepository;

    protected $channelRepository;

    protected $genreRepository;

    protected $nationalityRepository;

    protected $seasonRepository;

    protected $logRepository;

    /**
     * AdminShowController constructor.
     */
    public function __construct(
        ShowRepository $showRepository,
        ArtistRepository $artistRepository,
        ChannelRepository $channelRepository,
        GenreRepository $genreRepository,
        NationalityRepository $nationalityRepository,
        SeasonRepository $seasonRepository,
        LogRepository $logRepository
    ) {
        $this->showRepository = $showRepository;
        $this->artistRepository = $artistRepository;
        $this->channelRepository = $channelRepository;
        $this->genreRepository = $genreRepository;
        $this->nationalityRepository = $nationalityRepository;
        $this->seasonRepository = $seasonRepository;
        $this->logRepository = $logRepository;
    }

    /**
     * Return the nidex of the show administration.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        // On récupère la liste des séries
        $shows = $this->showRepository->getAllShowsWithCountSeasonsAndEpisodes();

        // On retourne la vue
        return view('admin/shows/index', compact('shows'));
    }

    /**
     * Return the list of shows in JSON.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShow($id)
    {
        $show = $this->showRepository->getShowByID($id);

        if (empty($show)) {
            return Response::json();
        }

        return Response::json(View::make('admin.shows.list_show', ['show' => $show])->render());
    }

    /**
     * Affiche le formulaire de création via theTVDB.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin/shows/create/tmdb');
    }

    /**
     * Affiche le formulaire de création manuelle.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function createManually()
    {
        // On retourne la vue
        return view('admin/shows/create/manually');
    }

    /**
     * Met à jour une série manuellement.
     */
    public function updateManually(ShowUpdateManuallyRequest $request): RedirectResponse
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $show = $this->showRepository->getByID($inputs['id']);
        // Ajout de l'image
        if (RequestFacade::hasFile('poster') && RequestFacade::file('poster')->isValid()) {
            $destinationPath = public_path().config('directories.original');
            $extension = 'jpg';
            $fileName = $show->show_url.'-poster'.'.'.$extension;
            RequestFacade::file('poster')->move($destinationPath, $fileName);
            resizeImage($show->show_url, 'poster');
        }
        unset($inputs['poster']);

        if (RequestFacade::hasFile('banner') && RequestFacade::file('banner')->isValid()) {
            $destinationPath = public_path().config('directories.original');
            $extension = 'jpg';
            $fileName = $show->show_url.'-banner'.'.'.$extension;
            RequestFacade::file('banner')->move($destinationPath, $fileName);
            resizeImage($show->show_url, 'banner');
        }
        unset($inputs['banner']);

        $dispatchOK = $this->showRepository->updateManuallyShowJob($inputs);

        if ($dispatchOK) {
            $state_header = 'status_header';
            $state = 'status';

            $message_header = 'Série en cours de modification';
            $message = 'La demande de modification de la série a été effectuée. Le serveur la traitera dès que possible.';
        } else {
            $state_header = 'wanring_header';
            $state = 'warning';

            $message_header = 'Erreur';
            $message = 'Problème lors de la mise à jour de la série !';
        }

        return redirect()->back()
            ->with($state_header, $message_header)
            ->with($state, $message);
    }

    /**
     * Mettre à jour les informations sur la série.
     *
     * @param $id
     *
     * @return RedirectResponse
     */
    public function edit($id)
    {
        $show = $this->showRepository->getInfoShowByID($id);
        $genres = formatRequestInVariable($show->genres);
        $nationalities = formatRequestInVariableNoSpace($show->nationalities);
        $channels = formatRequestInVariableNoSpace($show->channels);
        $creators = formatRequestInVariableNoSpace($show->creators);

        // On retourne la vue
        return view('admin/shows/edit', compact('show', 'genres', 'nationalities', 'channels', 'creators'));
    }

    /**
     * Enregistre une nouvelle série via theTVDB.
     *
     * @param ShowCreateRequest|Request $request
     *
     * @return RedirectResponse
     */
    public function store(ShowCreateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $dispatchOK = $this->showRepository->createShowJob($inputs);

        if ($dispatchOK) {
            $state_header = 'status_header';
            $state = 'status';

            $message_header = 'Série en cours d\'ajout';
            $message = 'La demande de création de série a été effectuée. Le serveur la traitera dès que possible.';
        } else {
            $state_header = 'warning_header';
            $state = 'warning';

            $message_header = 'Série déjà ajoutée';
            $message = 'La série que vous voulez créer existe déjà chez Série-All.';
        }

        return redirect()->back()
            ->with($state_header, $message_header)
            ->with($state, $message);
    }

    /**
     * Enregistre une nouvelle série créée manuellement.
     *
     * @param ShowCreateManuallyRequest|Request $request
     *
     * @return RedirectResponse
     */
    public function storeManually(ShowCreateManuallyRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $this->showRepository->createManuallyShowJob($inputs);

        return response()->json();
    }

    /**
     * Redirection JSON.
     *
     * @return RedirectResponse
     */
    public function redirectJSON()
    {
        return redirect()->route('admin.shows.index')
            ->with('status_header', 'Série en cours d\'ajout')
            ->with('status', 'La demande de cManuallyréation de série a été effectuée. Le serveur la traitera dès que possible.');
    }

    /**
     * Supprime une série ainsi que tous les éléments qui lui sont rattachés.
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $userID = Auth::user()->id;

        $dispatchOK = $this->showRepository->deleteJob($id, $userID);

        if ($dispatchOK) {
            $state_header = 'status_header';
            $state = 'status';

            $message_header = 'Suppression';
            $message = 'La série est en cours de suppression.';
        } else {
            $state_header = 'warning_header';
            $state = 'warning';

            $message_header = 'Erreur';
            $message = 'Problème lors de la suppression de la série !';
        }

        return redirect()->back()
            ->with($state_header, $message_header)
            ->with($state, $message);
    }

    /**
     * Update shows from TMDB.
     */
    public function updateFromTMDB()
    {
        dispatch(new ShowUpdateFromTMDB());
    }

    /**
     * Adds TMDB ID.
     */
    public function addTMDBID()
    {
        dispatch(new AddTMDBID());
    }

    /**
     * clearDoublons.
     */
    public function clearDoublons()
    {
        dispatch(new ClearDoublons());
    }

    /**
     * Update one show from TMDB.
     *
     * @param $show_id
     *
     * @return RedirectResponse
     */
    public function updateOneShowFromTMDB($show_id): RedirectResponse
    {
        $dispatchOK = dispatch(new OneShowUpdateFromTMDB($show_id));

        if ($dispatchOK) {
            $state_header = 'status_header';
            $state = 'status';

            $message_header = 'Mise à jour';
            $message = 'La mise à jour de la série est en cours.';
        } else {
            $state_header = 'warning_header';
            $state = 'warning';

            $message_header = 'Erreur';
            $message = 'Problème lors de la mise à jour de la série !';
        }

        return redirect()->back()
            ->with($state_header, $message_header)
            ->with($state, $message);
    }
}
