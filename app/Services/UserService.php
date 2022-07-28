<?php

declare(strict_types=1);

namespace App\Services;

use App\Charts\RateSummary;
use App\Http\Requests\ChangeInfoRequest;
use App\Interfaces\CommentRepositoryInterface;
use App\Interfaces\RateRepositoryInterface;
use App\Interfaces\ShowRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\UserServiceInterface;
use App\Models\User;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * UserService class.
 */
class UserService implements UserServiceInterface
{
    // FIXME(youkoulayley): add logs where needed.

    private UserRepositoryInterface $userRepository;

    private RateRepositoryInterface $rateRepository;

    private CommentRepositoryInterface $commentRepository;

    private ShowRepositoryInterface $showRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RateRepositoryInterface $rateRepository,
        CommentRepositoryInterface $commentRepository,
        ShowRepositoryInterface $showRepository
    ) {
        $this->userRepository = $userRepository;
        $this->rateRepository = $rateRepository;
        $this->commentRepository = $commentRepository;
        $this->showRepository = $showRepository;
    }

    /**
     * listUsers return a list of users.
     */
    public function list()
    {
        return $this->userRepository->list();
    }

    /**
     * getUserByURL gets a user by its URL.
     *
     * @param string $userURL
     *
     * @return Builder|Model
     *
     * @throws ModelNotFoundException
     */
    public function getUserByURL(string $userURL)
    {
        return $this->userRepository->getByURLWithPublishedArticles($userURL);
    }

    /**
     * getNotifications gets notifications for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getNotifications(string $userURL): array
    {
        $profileData = $this->getProfileData($userURL);
        $notifications = $profileData['user']->notifications()->paginate(30);
        $notificationData = ['notifications' => $notifications];

        return array_merge($profileData, $notificationData);
    }

    /**
     * getProfile gets profile data for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getProfile(string $userURL): array
    {
        $profileData = $this->getProfileData($userURL);

        $lastRates = $this->rateRepository->getLastRatesByUserID($profileData['user']->id);
        $lastRatesData = ['lastRates' => $lastRates];

        return array_merge($profileData, $lastRatesData);
    }

    /**
     * getRates gets rates data for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getRates(string $userURL): array
    {
        $profileData = $this->getProfileData($userURL);

        $allRates = $this->rateRepository->getRatesAggregateShowByUserID($profileData['user']->id, 'sh.name');

        $chartData = $this->rateRepository->getChartRatesByUserID($profileData['user']->id);
        $chart = new RateSummary();
        $chart->height(300)
            ->title('Récapitulatif des notes')
            ->labels($chartData->pluck('rate'))
            ->dataset('Nombre de notes', 'line', $chartData->pluck('total'));
        $chart->options([
            'yAxis' => [
                'min' => 0,
            ],
        ]);

        $data = ['allRates' => $allRates, 'chart' => $chart];

        return array_merge($profileData, $data);
    }

    /**
     * getRatesAjax gets rates data for the given user.
     *
     * @param string $userURL
     * @param string $sort
     * @return array
     */
    public function getRatesAjax(string $userURL, string $sort): array
    {
        $user = $this->userRepository->getByURL($userURL);

        switch ($sort) {
            case 'avg':
                $rates = $this->rateRepository->getRatesAggregateShowByUserID($user->id, 'avg_rate DESC');
                break;
            case 'count':
                $rates = $this->rateRepository->getRatesAggregateShowByUserID($user->id, 'count_rate DESC');
                break;
            case 'time':
                $rates = $this->rateRepository->getRatesAggregateShowByUserID($user->id, 'duration DESC');
                break;
            default:
                $rates = $this->rateRepository->getRatesAggregateShowByUserID($user->id, 'sh.name');
                break;
        }

        return ['allRates' => $rates];
    }

    /**
     * Get Comments get all comments for the given user.
     *
     * @param string $userURL
     * @param string $filter
     * @param string $order
     * @return array
     */
    public function getComments(string $userURL, string $filter, string $order): array
    {
        $profileData = $this->getProfileData($userURL);

        $computedFilter = $this->getCommentsChooseFilter($filter);
        $computedOrder = $this->getCommentsChooseOrder($order);

        $commentsShows = $this->commentRepository->getCommentsOnShowByUserID($profileData['user']->id, 'show', $computedFilter, $computedOrder);
        $commentsSeasons = $this->commentRepository->getCommentsOnSeasonByUserID($profileData['user']->id, 'season', $computedFilter, $computedOrder);
        $commentsEpisodes = $this->commentRepository->getCommentsOnEpisodeByUserID($profileData['user']->id, 'episode', $computedFilter, $computedOrder);

        $chart = new RateSummary();
        $chart->height(300)
            ->title('Récapitulatif des avis')
            ->labels(['Favorables', 'Neutres', 'Défavorables'])
            ->dataset('Avis', 'pie', [
                $profileData['commentsSummary']['positiveCount'],
                $profileData['commentsSummary']['neutralCount'],
                $profileData['commentsSummary']['negativeCount'],
            ])
            ->color(['#21BA45', '#767676', '#db2828']);

        $data = [
            'comments' => [
                'show' => $commentsShows,
                'season' => $commentsSeasons,
                'episode' => $commentsEpisodes,
            ],
            'chart' => $chart,
        ];

        return array_merge($profileData, $data);
    }

    /**
     * GetCommentsAjax get all comments for the given user.
     *
     * @param string $userURL
     * @param string $action
     * @param string $filter
     * @param string $order
     * @return array
     */
    public function getCommentsAjax(string $userURL, string $action, string $filter, string $order): array
    {
        $user = $this->userRepository->getByURL($userURL);

        $computedFilter = $this->getCommentsChooseFilter($filter);
        $computedOrder = $this->getCommentsChooseOrder($order);

        switch ($action) {
            case 'season':
                $comments = $this->commentRepository->getCommentsOnSeasonByUserID($user->id, 'season', $computedFilter, $computedOrder);
                break;
            case 'episode':
                $comments = $this->commentRepository->getCommentsOnEpisodeByUserID($user->id, 'episode', $computedFilter, $computedOrder);
                break;
            default:
                $comments = $this->commentRepository->getCommentsOnShowByUserID($user->id, 'show', $computedFilter, $computedOrder);
                break;
        }

        return ['comments' => $comments];
    }

    /**
     * Get Shows get all followed shows for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getShows(string $userURL): array
    {
        $profileData = $this->getProfileData($userURL);

        $followedShows = $this->showRepository->getShowFollowedByUser($profileData['user']->id);
        $inProgress = $followedShows->where('state', '=', config('shows.inProgress'));
        $onBreak = $followedShows->where('state', '=', config('shows.onBreak'));
        $completed = $followedShows->where('state', '=', config('shows.completed'));
        $abandoned = $followedShows->where('state', '=', config('shows.abandoned'));
        $toSee = $followedShows->where('state', '=', config('shows.toSee'));

        $data = [
            'followedShows' => [
                'inProgress' => $inProgress,
                'onBreak' => $onBreak,
                'completed' => $completed,
                'abandoned' => $abandoned,
                'toSee' => $toSee,
            ],
        ];

        return array_merge($profileData, $data);
    }

    /**
     * getRanking get all rankings for the given user.
     *
     * @param string $userURL
     * @return array
     */
    public function getRanking(string $userURL): array
    {
        $profileData = $this->getProfileData($userURL);

        $topShows = $this->rateRepository->getRankingShowsByUser($profileData['user']->id, 'DESC');
        $flopShows = $this->rateRepository->getRankingShowsByUser($profileData['user']->id, 'ASC');
        $topSeasons = $this->rateRepository->getRankingSeasonsByUsers($profileData['user']->id, 'DESC');
        $flopSeasons = $this->rateRepository->getRankingSeasonsByUsers($profileData['user']->id, 'ASC');
        $topEpisodes = $this->rateRepository->getRankingEpisodesByUsers($profileData['user']->id, 'DESC');
        $flopEpisodes = $this->rateRepository->getRankingEpisodesByUsers($profileData['user']->id, 'ASC');
        $topPilot = $this->rateRepository->getRankingPilotByUsers($profileData['user']->id, 'DESC');
        $flopPilot = $this->rateRepository->getRankingPilotByUsers($profileData['user']->id, 'ASC');

        $data = [
            'topShows' => $topShows,
            'flopShows' => $flopShows,
            'topSeasons' => $topSeasons,
            'flopSeasons' => $flopSeasons,
            'topEpisodes' => $topEpisodes,
            'flopEpisodes' => $flopEpisodes,
            'topPilot' => $topPilot,
            'flopPilot' => $flopPilot,
        ];

        return array_merge($profileData, $data);
    }

    /**
     * changePassword change the user password.
     *
     * @param Authenticatable|Model|User $user
     * @param string $oldPassword
     * @param string $newPassword
     * @return bool
     * @throws Exception
     */
    public function changePassword(Authenticatable $user, string $oldPassword, string $newPassword): bool
    {
        if (Hash::check($oldPassword, $user->getAuthPassword())) {
            $user->password = Hash::make($newPassword);
            $this->userRepository->save($user);

            return true;
        }

        return false;
    }

    /**
     * changeInfo change the user personal information.
     *
     * @param Authenticatable|Model|User $user
     * @param ChangeInfoRequest $data
     * @return void
     */
    public function changeInfo(Authenticatable $user, ChangeInfoRequest $data): void
    {
        $user->email = $data->email;
        $user->antispoiler = $data->antispoiler;
        $user->twitter = $data->twitter;
        $user->facebook = $data->facebook;
        $user->website = $data->website;
        $user->edito = $data->edito;

        $this->userRepository->save($user);
    }

    /**
     * getProfile data gets all common data for the user profile.
     */
    private function getProfileData($userURL): array
    {
        $user = $this->getUserByURL($userURL);
        $rates = $this->rateRepository->getAvgRateAndRatesCountByUserID($user->id);

        $watchTime = $this->rateRepository->getWatchTimeByUserID($user->id);
        $watchTimeReadable = $this->buildReadableWatchTime($watchTime);

        $comments = $this->getCommentsSummary($user->id);

        return [
            'user' => $user,
            'ratesSummary' => $rates,
            'watchTime' => $watchTimeReadable,
            'commentsSummary' => $comments,
        ];
    }

    /**
     * buildReadableWatchTime returns the time passed on all rated episodes with a human-readable format.
     */
    private function buildReadableWatchTime(string $watchTime): string
    {
        Carbon::setLocale('fr');

        $watchTimeReadable = '0m';

        try {
            $watchTimeReadable = CarbonInterval::fromString($watchTime.'m')->cascade()->forHumans();
        } catch (Exception $e) {
            Log::Error('unable to transform watch time', ['watchTime' => "$watchTime"]);
        }

        return $watchTimeReadable;
    }

    /**
     * getCommentsSummary returns a summary of comments for the given user including:
     * - Number of comments
     * - Number of positive comments
     * - Number of neutral comments.
     * - Number of negative comments.
     */
    private function getCommentsSummary($userID): array
    {
        $commentsCount = $this->commentRepository->getCountCommentsByUserIDAndThumb($userID);
        $positiveComments = $commentsCount->where('thumb', '=', config('comments.positive'))->pluck('total')->first();
        $neutralComments = $commentsCount->where('thumb', '=', config('comments.neutral'))->pluck('total')->first();
        $negativeComments = $commentsCount->where('thumb', '=', config('comments.negative'))->pluck('total')->first();

        return [
            'count' => $positiveComments + $neutralComments + $negativeComments,
            'positiveCount' => is_null($positiveComments) ? 0 : $positiveComments,
            'neutralCount' => is_null($neutralComments) ? 0 : $neutralComments,
            'negativeCount' => is_null($negativeComments) ? 0 : $negativeComments,
        ];
    }

    private function getCommentsChooseOrder(string $order): string
    {
        switch ($order) {
            case 2:
                return 'comments.id';
            default:
                return 'shows.name';
        }
    }

    private function getCommentsChooseFilter(string $filter): array
    {
        switch ($filter) {
            case 1:
                return [1];
            case 2:
                return [2];
            case 3:
                return [3];
            default:
                return [1, 2, 3];
        }
    }
}
