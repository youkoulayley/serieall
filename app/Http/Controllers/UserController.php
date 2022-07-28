<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ChangeInfoRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\FollowShowRequest;
use App\Http\Requests\NotificationRequest;
use App\Interfaces\UserServiceInterface;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View as V;
use Illuminate\View\View;
use MaddHatter\LaravelFullcalendar\Facades\Calendar;

/**
 * Class UserController.
 */
class UserController extends Controller
{
    private $userRepositoryInterface;

    private $rateRepositoryInterface;

    private $commentRepositoryInterface;

    private $showRepositoryInterface;

    private UserServiceInterface $userService;

    /**
     * UserController constructor.
     */
    public function __construct(
        UserServiceInterface $userService,
        $userRepositoryInterface = null,
        $rateRepositoryInterface = null,
        $commentRepositoryInterface = null,
        $showRepositoryInterface = null
    ) {
        $this->userService = $userService;
        $this->userRepositoryInterface = $userRepositoryInterface;
        $this->rateRepositoryInterface = $rateRepositoryInterface;
        $this->commentRepositoryInterface = $commentRepositoryInterface;
        $this->showRepositoryInterface = $showRepositoryInterface;
    }

    /**
     * Get user index.
     *
     * @return View
     */
    public function index(): View
    {
        $users = $this->userService->list();

        return view('users.index', compact('users'));
    }

    /**
     * Get users/profile page.
     *
     * @param string $userURL
     *
     * @return View
     */
    public function getProfile(string $userURL): View
    {
        $data = $this->userService->getProfile($userURL);

        return view('users.profile', compact('data'));
    }

    /**
     * Get users/rates page.
     *
     * @param string $userURL
     * @param string $sort
     *
     * @return View|JsonResponse
     */
    public function getRates(string $userURL, string $sort = '')
    {
        if (Request::ajax()) {
            $data = $this->userService->getRatesAjax($userURL, $sort);

            return response()->json(view('users.rates_cards', compact('data'))->render());
        }

        $data = $this->userService->getRates($userURL);

        return view('users.rates', compact('data'));
    }

    /**
     * @param $userURL
     * @param string $action
     * @param string $filter
     * @param string $tri
     *
     * @return Factory|JsonResponse|View
     */
    public function getComments($userURL, string $action = '', string $filter = '', string $tri = '')
    {
        if (Request::ajax()) {
            $data = $this->userService->getCommentsAjax($userURL, $action, $filter, $tri);

            return response()->json(view('users.comments_cards', compact('data'))->render());
        }

        $data = $this->userService->getComments($userURL, $filter, $tri);

        return view('users.comments', compact('data'));
    }

    /**
     * Get users/shows page.
     *
     * @param string $userURL
     * @return View
     */
    public function getShows(string $userURL): View
    {
        $data = $this->userService->getShows($userURL);

        return view('users.shows', compact('data'));
    }

    /**
     * getRanking returns all the ranking for the given user.
     *
     * @param string $userURL
     * @return Factory|View
     */
    public function getRanking(string $userURL)
    {
        $data = $this->userService->getRanking($userURL);

        return view('users.ranking', compact('data'));
    }

    /**
     * getNotifications returns the notifications for the user.
     *
     * @param $user_url
     *
     * @return Application|Factory|\Illuminate\Contracts\View\View
     */
    public function getNotifications($user_url)
    {
        $data = $this->userService->getNotifications($user_url);

        return view('users.notifications', compact('data'));
    }

    /**
     * getParameters returns the parameters page.
     *
     * @param $userURL
     *
     * @return Factory|View
     *
     * @throws ModelNotFoundException
     */
    public function getParameters($userURL)
    {
        $user = $this->userService->getUserByURL($userURL);

        return view('users.parameters', compact('user'));
    }

    /**
     * changePassword changes the user password.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        try {
            $ok = $this->userService->changePassword($user, $request->password, $request->new_password);
        } catch (Exception $e) {
            return back()->with('error', 'Impossible de modifier votre mot de passe. Veuillez réessayer.');
        }

        if ($ok) {
            return back()->with('success', 'Votre mot de passe a bien été modifié !');
        }

        return back()->with('warning', 'Votre mot de passe actuel ne correspond pas à celui saisi.');
    }

    /**
     * changeInfo changes the user information.
     *
     * @param ChangeInfoRequest $request
     * @return RedirectResponse
     */
    public function changeInfo(ChangeInfoRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if (is_null($user)) {
            return redirect()->back()->with(
                'error',
                'Vous devez vous connecter pour pouvoir modifier vos informations personnelles.'
            );
        }

        $this->userService->changeInfo($user, $request);

        return redirect()->back()->with(
            'success',
            'Vos informations personnelles ont été modifiées !'
        );
    }

    //----------------
    // TODO
    //----------------

    /**
     * Follow Show.
     *
     * @return JsonResponse|int
     */
    public function followShow(FollowShowRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        if (Request::ajax()) {
            $user = $this->userRepositoryInterface->getByID($inputs['user_id']);

            if (! empty($inputs['shows'])) {
                $show = explode(',', $inputs['shows']);
                if (! isset($inputs['message'])) {
                    $message = '';
                } else {
                    $message = $inputs['message'];
                }

                foreach ($show as $s) {
                    if ($user->shows->contains($s)) {
                        $user->shows()->detach($s);
                    }
                }

                $user->shows()->attach($show, ['state' => $inputs['state'], 'message' => $message]);
            }

            if (1 == $inputs['state']) {
                $followed_shows = $this->showRepositoryInterface->getShowFollowedByUser($user->id);
                $in_progress_shows = $followed_shows->where('state', '=', 1);

                return Response::json(V::make('users.shows_cards', ['shows' => $in_progress_shows, 'user' => $user])->render());
            } elseif (2 == $inputs['state']) {
                $followed_shows = $this->showRepositoryInterface->getShowFollowedByUser($user->id);
                $on_break_shows = $followed_shows->where('state', '=', 2);

                return Response::json(V::make('users.shows_cards', ['shows' => $on_break_shows, 'user' => $user])->render());
            } elseif (3 == $inputs['state']) {
                $followed_shows = $this->showRepositoryInterface->getShowFollowedByUser($user->id);
                $completed_shows = $followed_shows->where('state', '=', 3);

                return Response::json(V::make('users.shows_cards', ['shows' => $completed_shows, 'user' => $user])->render());
            } elseif (4 == $inputs['state']) {
                $followed_shows = $this->showRepositoryInterface->getShowFollowedByUser($user->id);
                $abandoned_shows = $followed_shows->where('state', '=', 4);

                return Response::json(V::make('users.shows_abandoned_cards', ['shows' => $abandoned_shows, 'user' => $user])->render());
            } elseif (5 == $inputs['state']) {
                $followed_shows = $this->showRepositoryInterface->getShowFollowedByUser($user->id);
                $to_see_shows = $followed_shows->where('state', '=', 5);

                return Response::json(V::make('users.shows_cards', ['shows' => $to_see_shows, 'user' => $user])->render());
            }
        }

        return 404;
    }

    /**
     * Follow Show.
     *
     * @return JsonResponse|int
     */
    public function followShowFiche(FollowShowRequest $request)
    {
        $inputs = array_merge($request->all(), ['user_id' => $request->user()->id]);

        if (Request::ajax()) {
            $user = $this->userRepositoryInterface->getByID($inputs['user_id']);

            if (! empty($inputs['shows'])) {
                $show = explode(',', $inputs['shows']);
                if (! isset($inputs['message'])) {
                    $message = '';
                } else {
                    $message = $inputs['message'];
                }

                foreach ($show as $s) {
                    if ($user->shows->contains($s)) {
                        $user->shows()->detach($s);
                    }
                }

                $user->shows()->attach($show, ['state' => $inputs['state'], 'message' => $message]);
                $show = $this->showRepositoryInterface->getShowByID($inputs['shows']);

                return Response::json(V::make('shows.actions_show', ['state_show' => $inputs['state'], 'show_id' => $inputs['shows'], 'completed_show' => $show->encours])->render());
            }
        }

        return 404;
    }

    /**
     * Unfollow a show.
     *
     * @param $show
     *
     * @return JsonResponse|int
     */
    public function unfollowShow($show)
    {
        if (Request::ajax()) {
            $user = Auth::user();

            if ($user->shows->contains($show)) {
                $user->shows()->detach($show);
            }

            return Response::json(200);
        }

        return 404;
    }

    /**
     * Get planning for a particular user.
     *
     * @param $user_url
     *
     * @return Factory|View
     */
    public function getPlanning($user_url)
    {
        $user = $this->userRepositoryInterface->getByURLWithPublishedArticles($user_url);

        $all_rates = $this->rateRepositoryInterface->getAllRateByUserID($user->id);
        $avg_user_rates = $all_rates->select(DB::raw('trim(round(avg(rate),2))+0 avg, count(*) nb_rates'))->first();
        $time_passed_shows = getTimePassedOnShow($this->rateRepositoryInterface, $user->id);

        $comments = $this->commentRepositoryInterface->getCommentByUserIDThumbNotNull($user->id);
        $nb_comments = $this->commentRepositoryInterface->countCommentByUserIDThumbNotNull($user->id);
        $comment_fav = $comments->where('thumb', '=', 1)->first();
        $comment_neu = $comments->where('thumb', '=', 2)->first();
        $comment_def = $comments->where('thumb', '=', 3)->first();

        $episodes_in_progress = $this->userRepositoryInterface->getEpisodePlanning($user->id, 1);
        $episodes_on_break = $this->userRepositoryInterface->getEpisodePlanning($user->id, 2);

        $events = [];

        foreach ($episodes_in_progress as $event) {
            $events[] = Calendar::event(
                $event->show_name.' - '.'Episode '.$event->season_name.'.'.sprintf('%02s', $event->numero),
                true,
                $event->diffusion_us,
                $event->diffusion_us,
                $event->id,
                [
                    'url' => route('episode.fiche', [$event->show_url, $event->season_name, $event->numero, $event->id]),
                    'backgroundColor' => '#1074b2',
                    'borderColor' => '#1074b2',
                ]
            );
        }

        foreach ($episodes_on_break as $event) {
            $events[] = Calendar::event(
                $event->show_name.' - '.'Episode '.$event->season_name.'.'.sprintf('%02s', $event->numero),
                true,
                $event->diffusion_us,
                $event->diffusion_us,
                $event->id,
                [
                    'url' => route('episode.fiche', [$event->show_url, $event->season_name, $event->numero, $event->id]),
                    'backgroundColor' => '#213d64',
                    'borderColor' => '#213d64',
                    'hover' => $event->show_name.' - '.'Episode '.$event->season_name.'.'.sprintf('%02s', $event->numero),
                ]
            );
        }

        $calendar = Calendar::addEvents($events)
            ->setOptions([
                'firstDay' => 1,
                'lang' => 'fr',
                'locale' => 'fr',
                'aspectRatio' => 2.5,
                'showNonCurrentDates' => false,
                'fixedWeekCount' => false,
            ])->setCallbacks(['eventRender' => 'function(eventObj, $el) {$el.popup({title: eventObj.title,content: eventObj.description,trigger: "hover",placement: "top",container: "body"});}']);

        return view('users.planning', compact('user', 'avg_user_rates', 'time_passed_shows', 'nb_comments', 'comment_fav', 'comment_neu', 'comment_def', 'calendar'));
    }

    /**
     * @return JsonResponse|int
     */
    public function markNotification(NotificationRequest $notificationRequest)
    {
        if (Request::ajax()) {
            $user = Auth::user();
            $notification = $user->Notifications->where('id', '=', $notificationRequest->notif_id)->first();

            if (is_null($notification->read_at)) {
                $notification->markAsRead();
            } else {
                if ('true' == $notificationRequest->markUnread) {
                    $notification->markAsUnRead();
                }
            }

            return Response::json('test');
        }

        return Response::json('Not Ajax');
    }

    /**
     * @return JsonResponse|int
     */
    public function markNotifications()
    {
        if (Request::ajax()) {
            Log::Info('pouet');
            $user = Auth::user();

            $user->unreadNotifications->markAsRead();

            return Response::json('OK');
        }

        return 404;
    }
}
