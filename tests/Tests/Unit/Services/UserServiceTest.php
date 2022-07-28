<?php

namespace Tests\Unit\Services;

use App\Charts\RateSummary;
use App\Http\Requests\ChangeInfoRequest;
use App\Interfaces\CommentRepositoryInterface;
use App\Interfaces\RateRepositoryInterface;
use App\Interfaces\ShowRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Comment;
use App\Models\Episode_user;
use App\Models\Show;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * UserServiceTest.
 */
class UserServiceTest extends TestCase
{
    // FIXME(youkoulayley): remove duplicate tests on errors from getProfileData.

    private UserRepositoryInterface $userRepositoryMock;

    private RateRepositoryInterface $rateRepositoryMock;

    private CommentRepositoryInterface $commentRepositoryMock;

    private ShowRepositoryInterface $showRepositoryMock;

    private UserService $service;

    public function setUp(): void
    {
        parent::setUp();
        /** @var UserRepositoryInterface&MockObject&MockInterface $userRepositoryMock */
        $userRepositoryMock = $this->mock(UserRepositoryInterface::class);
        /** @var RateRepositoryInterface&MockObject&MockInterface $rateRepositoryMock */
        $rateRepositoryMock = $this->mock(RateRepositoryInterface::class);
        /** @var CommentRepositoryInterface&MockObject&MockInterface $commentRepositoryMock */
        $commentRepositoryMock = $this->mock(CommentRepositoryInterface::class);
        /** @var ShowRepositoryInterface&MockObject&MockInterface $showRepositoryMock */
        $showRepositoryMock = $this->mock(ShowRepositoryInterface::class);

        $this->userRepositoryMock = $userRepositoryMock;
        $this->rateRepositoryMock = $rateRepositoryMock;
        $this->commentRepositoryMock = $commentRepositoryMock;
        $this->showRepositoryMock = $showRepositoryMock;

        $this->service = new UserService(
            $this->userRepositoryMock,
            $this->rateRepositoryMock,
            $this->commentRepositoryMock,
            $this->showRepositoryMock,
        );
    }

    public function testListUsers()
    {
        $users = User::factory()->count(5)->make();

        $this->userRepositoryMock
            ->shouldReceive('list')
            ->andReturn($users);

        $got = $this->service->list();
        $this->assertEquals($users, $got);
    }

    public function testGetUserByURL()
    {
        $users = User::factory()->count(2)->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->andReturn($users[0]);

        $got = $this->service->getUserByURL($users[0]->user_url);
        $this->assertEquals($users[0], $got);
    }

    public function testGetProfile()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $lastRates = [
            new Episode_user(['rate' => 15]),
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getLastRatesByUserID')
            ->with($user->id)
            ->andReturn($lastRates);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'lastRates' => $lastRates,
        ];

        $got = $this->service->getProfile($user->user_url);
        $this->assertEquals($want, $got);
    }

    public function testGetProfile_UserRepositoryThrowsException()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andThrow(ModelNotFoundException::class);
        $this->rateRepositoryMock->shouldNotHaveBeenCalled();
        $this->commentRepositoryMock->shouldNotHaveBeenCalled();

        $this->expectException(ModelNotFoundException::class);

        $this->service->getProfile($user->user_url);
    }

    public function testGetProfile_GetWatchTimeByUserIDReturnsZero()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 123]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn('0');

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $lastRates = [
            new Episode_user(['rate' => 15]),
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getLastRatesByUserID')
            ->with($user->id)
            ->andReturn($lastRates);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 123],
            'watchTime' => '1 seconde',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'lastRates' => $lastRates,
        ];

        $got = $this->service->getProfile($user->user_url);
        $this->assertEquals($want, $got);
    }

    public function testGetProfile_BuildReadableWatchTimeThrowsException()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn('pouetpouet');

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $lastRates = [
            new Episode_user(['rate' => 15]),
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getLastRatesByUserID')
            ->with($user->id)
            ->andReturn($lastRates);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '1 seconde',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'lastRates' => $lastRates,
        ];

        $got = $this->service->getProfile($user->user_url);
        $this->assertEquals($want, $got);
    }

    public function testGetProfile_GetCountCommentsByUserIDAndThumbReturnsNoNeutralComments()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 123]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn('0');

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $lastRates = [
            new Episode_user(['rate' => 15]),
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getLastRatesByUserID')
            ->with($user->id)
            ->andReturn($lastRates);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 123],
            'watchTime' => '1 seconde',
            'commentsSummary' => [
                'count' => 49,
                'positiveCount' => 12,
                'neutralCount' => 0,
                'negativeCount' => 37,
            ],
            'lastRates' => $lastRates,
        ];

        $got = $this->service->getProfile($user->user_url);
        $this->assertEquals($want, $got);
    }

    public function testGetNotifications()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $want = [
            'user' => $user,
            'notifications' => $user->notifications()->paginate(30),
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
        ];

        $got = $this->service->getNotifications($user->user_url);
        $this->assertEquals($want, $got);
    }

    public function testGetRates()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $allRates = [
            [
                'thetvdb_id' => 0,
                'name' => 'Lorine Balistreri',
                'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                'format' => 40,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 17.5,
            ],
                [
                    'thetvdb_id' => 1,
                    'name' => 'Breaking Bad',
                    'show_url' => 'breaking-bad',
                    'format' => 50,
                    'username' => 'toto',
                    'minutes' => 800,
                    'nb_rate' => 20,
                    'avg_rate' => 13.5,
                ],
            ];

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'sh.name')
            ->andReturn($allRates);

        $chartRate = new Collection([
            new Episode_user(['rate' => 15]),
            new Episode_user(['rate' => 20]),
        ]);

        // Total not in fillable field, we have to manually add it here.
        foreach ($chartRate as $c) {
            $c->total = 5;
        }

        $this->rateRepositoryMock
            ->shouldReceive('getChartRatesByUserID')
            ->with($user->id)
            ->andReturn($chartRate);

        $wantChart = new RateSummary();
        $wantChart->height(300)
            ->title('Récapitulatif des notes')
            ->labels($chartRate->pluck('rate'))
            ->dataset('Nombre de notes', 'line', $chartRate->pluck('total'));
        $wantChart->options([
            'yAxis' => [
                'min' => 0,
            ],
        ]);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'allRates' => $allRates,
            'chart' => $wantChart,
        ];

        $got = $this->service->getRates($user->user_url);

        // Handle ID not equals for the RateSummary chart.
        $this->assertNotEmpty($got['chart']->id);
        $want['chart']->id = $got['chart']->id;

        $this->assertEquals($want, $got);
    }

    public function testGetRates_GetRatesAggregateShowByUserIDReturnsEmptyArray()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 123]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn('0');

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'sh.name')
            ->andReturn([]);

        $chartRate = new Collection([
            new Episode_user(['rate' => 15]),
            new Episode_user(['rate' => 20]),
        ]);

        // Total not in fillable field, we have to manually add it here.
        foreach ($chartRate as $c) {
            $c->total = 5;
        }

        $this->rateRepositoryMock
            ->shouldReceive('getChartRatesByUserID')
            ->with($user->id)
            ->andReturn($chartRate);

        $wantChart = new RateSummary();
        $wantChart->height(300)
            ->title('Récapitulatif des notes')
            ->labels($chartRate->pluck('rate'))
            ->dataset('Nombre de notes', 'line', $chartRate->pluck('total'));
        $wantChart->options([
            'yAxis' => [
                'min' => 0,
            ],
        ]);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 123],
            'watchTime' => '1 seconde',
            'commentsSummary' => [
                'count' => 49,
                'positiveCount' => 12,
                'neutralCount' => 0,
                'negativeCount' => 37,
            ],
            'allRates' => [],
            'chart' => $wantChart,
        ];

        $got = $this->service->getRates($user->user_url);

        // Handle ID not equals for the RateSummary chart.
        $this->assertNotEmpty($got['chart']->id);
        $want['chart']->id = $got['chart']->id;

        $this->assertEquals($want, $got);
    }

    public function testGetRates_GetChartRatesByUserIDReturnsEmptyChart()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $allRates = [
            [
                'thetvdb_id' => 0,
                'name' => 'Lorine Balistreri',
                'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                'format' => 40,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 17.5,
            ],
            [
                'thetvdb_id' => 1,
                'name' => 'Breaking Bad',
                'show_url' => 'breaking-bad',
                'format' => 50,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 13.5,
            ],
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'sh.name')
            ->andReturn($allRates);

        $chartRate = new Collection();

        $this->rateRepositoryMock
            ->shouldReceive('getChartRatesByUserID')
            ->with($user->id)
            ->andReturn($chartRate);

        $wantChart = new RateSummary();
        $wantChart->height(300)
            ->title('Récapitulatif des notes')
            ->labels($chartRate->pluck('rate'))
            ->dataset('Nombre de notes', 'line', $chartRate->pluck('total'));
        $wantChart->options([
            'yAxis' => [
                'min' => 0,
            ],
        ]);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'allRates' => $allRates,
            'chart' => $wantChart,
        ];

        $got = $this->service->getRates($user->user_url);

        // Handle ID not equals for the RateSummary chart.
        $this->assertNotEmpty($got['chart']->id);
        $want['chart']->id = $got['chart']->id;

        $this->assertEquals($want, $got);
    }

    public function testGetRatesAjax()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $allRates = [
            (object) [
                'thetvdb_id' => 0,
                'name' => 'Lorine Balistreri',
                'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                'format' => 40,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 17.5,
            ],
            (object) [
                'thetvdb_id' => 1,
                'name' => 'Breaking Bad',
                'show_url' => 'breaking-bad',
                'format' => 50,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 13.5,
            ],
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'sh.name')
            ->andReturn($allRates);

        $want = ['allRates' => $allRates];

        $got = $this->service->getRatesAjax($user->user_url, 'toto');
        $this->assertEquals($want, $got);
    }

    public function testGetRatesAjax_sortByAverageRates()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $allRates = [
            [
                'thetvdb_id' => 0,
                'name' => 'Lorine Balistreri',
                'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                'format' => 40,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 17.5,
            ],
            [
                'thetvdb_id' => 1,
                'name' => 'Breaking Bad',
                'show_url' => 'breaking-bad',
                'format' => 50,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 13.5,
            ],
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'avg_rate DESC')
            ->andReturn($allRates);

        $want = ['allRates' => $allRates];

        $got = $this->service->getRatesAjax($user->user_url, 'avg');
        $this->assertEquals($want, $got);
    }

    public function testGetRatesAjax_sortByCountRates()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $allRates = [
            [
                'thetvdb_id' => 0,
                'name' => 'Lorine Balistreri',
                'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                'format' => 40,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 17.5,
            ],
            [
                'thetvdb_id' => 1,
                'name' => 'Breaking Bad',
                'show_url' => 'breaking-bad',
                'format' => 50,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 13.5,
            ],
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'count_rate DESC')
            ->andReturn($allRates);

        $want = ['allRates' => $allRates];

        $got = $this->service->getRatesAjax($user->user_url, 'count');
        $this->assertEquals($want, $got);
    }

    public function testGetRatesAjax_sortByWatchTime()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $allRates = [
            [
                'thetvdb_id' => 0,
                'name' => 'Lorine Balistreri',
                'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                'format' => 40,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 17.5,
            ],
            [
                'thetvdb_id' => 1,
                'name' => 'Breaking Bad',
                'show_url' => 'breaking-bad',
                'format' => 50,
                'username' => 'toto',
                'minutes' => 800,
                'nb_rate' => 20,
                'avg_rate' => 13.5,
            ],
        ];

        $this->rateRepositoryMock
            ->shouldReceive('getRatesAggregateShowByUserID')
            ->with($user->id, 'duration DESC')
            ->andReturn($allRates);

        $want = ['allRates' => $allRates];

        $got = $this->service->getRatesAjax($user->user_url, 'time');
        $this->assertEquals($want, $got);
    }

    public function testGetComments()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));
        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnSeasonByUserID')
            ->with($user->id, 'season', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));
        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnEpisodeByUserID')
            ->with($user->id, 'episode', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $wantChart = new RateSummary();
        $wantChart->height(300)
            ->title('Récapitulatif des avis')
            ->labels(['Favorables', 'Neutres', 'Défavorables'])
            ->dataset('Avis', 'pie', [
                12,
                98,
                37,
            ])
            ->color(['#21BA45', '#767676', '#db2828']);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'comments' => [
              'show' =>   new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4),
                'season' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4),
                'episode' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4),
            ],
            'chart' => $wantChart,
        ];

        $got = $this->service->getComments($user->user_url, '', '');

        // Handle ID not equals for the RateSummary chart.
        $this->assertNotEmpty($got['chart']->id);
        $want['chart']->id = $got['chart']->id;

        $this->assertEquals($want, $got);
    }

    public function testGetComments_userWithoutComments()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([], 0, 4));
        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnSeasonByUserID')
            ->with($user->id, 'season', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([], 0, 4));
        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnEpisodeByUserID')
            ->with($user->id, 'episode', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([], 0, 4));

        $wantChart = new RateSummary();
        $wantChart->height(300)
            ->title('Récapitulatif des avis')
            ->labels(['Favorables', 'Neutres', 'Défavorables'])
            ->dataset('Avis', 'pie', [
                12,
                98,
                37,
            ])
            ->color(['#21BA45', '#767676', '#db2828']);

        $want = [
            'user' => $user,
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
            'comments' => [
                'show' =>   new LengthAwarePaginator([], 0, 4),
                'season' => new LengthAwarePaginator([], 0, 4),
                'episode' => new LengthAwarePaginator([], 0, 4),
            ],
            'chart' => $wantChart,
        ];

        $got = $this->service->getComments($user->user_url, '', '');

        // Handle ID not equals for the RateSummary chart.
        $this->assertNotEmpty($got['chart']->id);
        $want['chart']->id = $got['chart']->id;

        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'show', 0, 'shows.name');
        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax_FilterBy1()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [1], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' =>new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'show', 1, 'shows.name');
        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax_FilterBy2()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [2], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'show', 2, 'shows.name');
        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax_FilterBy3()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' =>new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'show', 3, 'shows.name');
        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax_OrderBy2()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnShowByUserID')
            ->with($user->id, 'show', [3], 'comments.id')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' =>new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'show', 3, 2);
        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax_SeasonComments()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnSeasonByUserID')
            ->with($user->id, 'season', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' =>new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'season', 0, 'shows.name');
        $this->assertEquals($want, $got);
    }

    public function testGetCommentsAjax_EpisodeComments()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURL')
            ->with($user->user_url)
            ->andReturn($user);

        $this->commentRepositoryMock
            ->shouldReceive('getCommentsOnEpisodeByUserID')
            ->with($user->id, 'episode', [1, 2, 3], 'shows.name')
            ->andReturn(new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4));

        $want = ['comments' =>new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4)];

        $got = $this->service->getCommentsAjax($user->user_url, 'episode', 0, 'shows.name');
        $this->assertEquals($want, $got);
    }

    public function testGetShows()
    {
        $shows = Show::factory()->count(5);
        $user = User::factory()
            ->hasAttached($shows, ['state' => config('shows.onBreak')])
            ->make();

        $this->userRepositoryMock
            ->shouldReceive('getByURLWithPublishedArticles')
            ->with($user->user_url)
            ->andReturn($user);

        $this->rateRepositoryMock
            ->shouldReceive('getAvgRateAndRatesCountByUserID')
            ->with($user->id)
            ->andReturn(['avgRate' => 12.5, 'ratesCount' => 147]);

        $this->rateRepositoryMock
            ->shouldReceive('getWatchTimeByUserID')
            ->with($user->id)
            ->andReturn(800);

        $this->commentRepositoryMock
            ->shouldReceive('getCountCommentsByUserIDAndThumb')
            ->with($user->id)
            ->andReturn(new Collection([
                new Collection(['thumb' => 1, 'total' => 12]),
                new Collection(['thumb' => 2, 'total' => 98]),
                new Collection(['thumb' => 3, 'total' => 37]),
            ]));

        $show1 = new Show(['show_url' => 'show1']);
        $show1->state = config('shows.inProgress');

        $show2 = new Show(['show_url' => 'show2']);
        $show2->state = config('shows.onBreak');
        $wantFollowedShow = new Collection([$show1, $show2]);

        $this->showRepositoryMock
            ->shouldReceive('getShowFollowedByUser')
            ->with($user->id)
            ->andReturn($wantFollowedShow);

        $want = [
            'user' => $user,
            'followedShows' => [
                'inProgress' => new Collection([$show1]),
                'onBreak' => new Collection([1 => $show2]),
                'completed' => new Collection(),
                'toSee' => new Collection(),
                'abandoned' => new Collection(),
            ],
            'ratesSummary' => ['avgRate' => 12.5, 'ratesCount' => 147],
            'watchTime' => '13 heures 20 minutes',
            'commentsSummary' => [
                'count' => 147,
                'positiveCount' => 12,
                'neutralCount' => 98,
                'negativeCount' => 37,
            ],
        ];

        $got = $this->service->getShows($user->user_url);
        $this->assertEquals($want, $got);
    }

    public function testChangePassword()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock->shouldReceive('save')->with($user);

        $got = $this->service->changePassword($user, 'password', 'AlloAllo123');
        $this->assertTrue($got);
    }

    public function testChangePassword_OldPasswordDontMatch()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock->shouldReceive('save')->with($user);

        $got = $this->service->changePassword($user, 'password2', 'AlloAllo123');
        $this->assertNotTrue($got);
    }

    public function testChangePassword_SaveDoesntWork()
    {
        $user = User::factory()->make();

        $this->userRepositoryMock->shouldReceive('save')->with($user)->andThrow(Exception::class);

        $this->expectException(Exception::class);
        $this->service->changePassword($user, 'password', 'AlloAllo123');
    }

    public function testChangeInfo()
    {
        $user = User::factory()->make();

        $wantUser = $user;
        $wantUser->email = 'foo@bar.com';
        $wantUser->edito = 'toto';

        $this->userRepositoryMock->shouldReceive('save')->with($wantUser);

        $request = new ChangeInfoRequest(['email' => 'foo@bar.com', 'edito'=> 'toto']);

        $this->service->changeInfo($user, $request);
    }
}
