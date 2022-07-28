<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\UserStore;
use App\Jobs\UserUpdate;
use App\Repositories\CommentRepository;
use App\Repositories\ShowRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Class AdminUserController.
 */
class AdminUserController extends Controller
{
    protected UserRepository $userRepository;

    protected CommentRepository $commentRepository;

    protected ShowRepository $showRepository;

    /**
     * AdminUserController constructor.
     *
     * @internal param SeasonRepository $seasonRepository
     * @internal param ShowRepository $showRepository
     * @internal param ArtistRepository $artistRepository
     */
    public function __construct(UserRepository $userRepository, CommentRepository $commentRepository, ShowRepository $showRepository)
    {
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository;
        $this->showRepository = $showRepository;
    }

    /**
     * Affiche l'index de l'administation des utilisateurs.
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // On récupère la liste des utilisateurs
        $users = $this->userRepository->list();

        // On retourne la vue
        return view('admin.users.index', compact('users'));
    }

    /**
     * Return the list of users in JSON.
     *
     * @param $id
     */
    public function getUser($id): JsonResponse
    {
        $user = $this->userRepository->getByID($id);

        if (empty($user)) {
            return Response::json();
        }

        return Response::json(View::make('admin.users.list_user', ['user' => $user])->render());
    }

    /**
     * Affiche le formulaire de création d'utilisateur.
     *
     * @return Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Ajoute un nouvel utilisateur.
     *
     * @return string
     */
    public function store(UserStoreRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $this->dispatch(new UserStore($inputs));

        return redirect()->route('admin.users.index')
            ->with('status_header', 'Ajout d\'un utilisateur')
            ->with('status', 'La demande d\'ajout a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Affiche le formulaire d'édition de l'utilisateur.
     *
     * @param $id
     *
     * @return Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit($id)
    {
        $user = $this->userRepository->getByID($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Met à jour un utilisateur.
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @internal param $id
     */
    public function update(UserUpdateRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        dispatch(new UserUpdate($inputs));

        return redirect()->route('admin.users.index')
            ->with('status_header', 'Modification d\'un utilisateur')
            ->with('status', 'La demande de modification a été envoyée au serveur. Il la traitera dès que possible.');
    }

    /**
     * Bannir ou débannir un utilisateur.
     *
     * @param $id
     *
     * @return Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function ban($id)
    {
        $user = $this->userRepository->getByID($id);
        $userID = Auth::user()->id;

        $logID = initJob($userID, 'Ban', 'User', $user->id);

        if (1 == $user->suspended) {
            $logMessage = 'Déban de l\'utilisateur '.$user->username;
            saveLogMessage($logID, $logMessage);
            $user->suspended = 0;
        } else {
            $logMessage = 'Ban de l\'utilisateur '.$user->username;
            saveLogMessage($logID, $logMessage);
            $user->suspended = 1;
        }

        $user->save();
        endJob($logID);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Deletes a user.
     *
     * @param $id
     *
     * @throws \Exception
     */
    public function destroy($id): RedirectResponse
    {
        $user = $this->userRepository->getByID($id);

        $user->rates()->delete();
        $user->shows()->detach();
        $user->comments()->delete();

        $user->delete();

        return redirect()->back()
            ->with('status_header', 'Suppression')
            ->with('status', 'L\'utilisateur a été supprimé.');
    }
}
