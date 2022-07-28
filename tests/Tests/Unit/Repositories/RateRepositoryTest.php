<?php

namespace Tests\Unit\Repositories;

use App\Interfaces\EpisodeRepositoryInterface;
use App\Interfaces\SeasonRepositoryInterface;
use App\Interfaces\ShowRepositoryInterface;
use App\Models\Episode;
use App\Models\Episode_user;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;
use App\Repositories\RateRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * RateRepositoryTest.
 */
class RateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RateRepository $repository;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        /** @var ShowRepositoryInterface&MockObject&MockInterface $showRepositoryMock */
        $showRepositoryMock = $this->mock(ShowRepositoryInterface::class);
        /** @var SeasonRepositoryInterface&MockObject&MockInterface $seasonRepositoryMock */
        $seasonRepositoryMock = $this->mock(SeasonRepositoryInterface::class);
        /** @var EpisodeRepositoryInterface&MockObject&MockInterface $episodeRepositoryMock */
        $episodeRepositoryMock = $this->mock(EpisodeRepositoryInterface::class);

        $this->repository = new RateRepository(
            $showRepositoryMock,
            $seasonRepositoryMock,
            $episodeRepositoryMock
        );
    }

    public function testGetAvgRateAndRatesCountByUserID()
    {
        $season = Season::factory()->for(Show::factory())->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(5),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(5),
                ['rate' => 20]
            )
            ->count(2)
            ->create();

        $want = ['avgRate' => 17.5, 'ratesCount' => 10];

        $got = $this->repository->getAvgRateAndRatesCountByUserID($users[1]->id);
        $this->assertEquals($want, $got);
    }

    public function testGetAvgRateAndRatesCountByUserID_NotFoundShouldReturnZero()
    {
        $want = ['avgRate' => 0, 'ratesCount' => 0];

        $got = $this->repository->getAvgRateAndRatesCountByUserID(0);
        $this->assertEquals($want, $got);
    }

    public function testGetChartRatesByUserID()
    {
        $season = Season::factory()->for(Show::factory())->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(5),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(5),
                ['rate' => 20]
            )
            ->count(2)
            ->create();

        $want = new Collection([
            new Episode_user(['rate' => 15]),
            new Episode_user(['rate' => 20]),
        ]);

        // Total not in fillable field, we have to manually add it here.
        foreach ($want as $w) {
            $w->total = 5;
        }

        $got = $this->repository->getChartRatesByUserID($users[1]->id);
        $this->assertEquals($want->toArray(), $got->toArray());
    }

    public function testGetChartRatesByUserID_NotFoundShouldReturnEmptyArray()
    {
        $want = [];

        $got = $this->repository->getChartRatesByUserID(0);
        $this->assertEquals($want, $got->toArray());
    }

    public function testGetWatchTimeByUserID()
    {
        $show = Show::factory()->create();
        $season = Season::factory()->for($show)->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(5),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(5),
                ['rate' => 20]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getWatchTimeByUserID($users[1]->id);
        $this->assertEquals(10 * $show->format, $got);
    }

    public function testGetWatchTimeByUserID_NotFoundReturnsZero()
    {
        $got = $this->repository->getWatchTimeByUserID(0);
        $this->assertEquals('0', $got);
    }

    public function testGetLastRatesByUserID()
    {
        $season = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(10),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(10),
                ['rate' => 20]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getLastRatesByUserID($users[1]->id);
        $this->assertLessThanOrEqual($this->repository::LIMIT_LAST_RATE_PROFILE, count($got));
    }

    public function testGetLastRatesByUserID_NotEnoughResults()
    {
        $season = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(2),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season)
                    ->count(3),
                ['rate' => 20]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getLastRatesByUserID($users[1]->id);
        $this->assertLessThanOrEqual($this->repository::LIMIT_LAST_RATE_PROFILE, count($got));
    }

    public function testGetRatesAggregateShowByUserID()
    {
        $season1 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $season2 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 20]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season2)
                    ->count(10),
                ['rate' => 11]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getRatesAggregateShowByUserID($users[1]->id, 'sh.name');
        $this->assertCount(2, $got);
    }

    public function testGetRatesAggregateShowByUserID_OrderByCountRate()
    {
        $season1 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $season2 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 20]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season2)
                    ->count(10),
                ['rate' => 11]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getRatesAggregateShowByUserID($users[1]->id, 'count_rate DESC');

        $this->assertLessThanOrEqual($got[0]->count_rate, $got[1]->count_rate);
        $this->assertCount(2, $got);
    }

    public function testGetRatesAggregateShowByUserID_OrderByAvgRate()
    {
        $season1 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $season2 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 20]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season2)
                    ->count(10),
                ['rate' => 11]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getRatesAggregateShowByUserID($users[1]->id, 'avg_rate DESC');

        $this->assertLessThanOrEqual($got[0]->avg_rate, $got[1]->avg_rate);
        $this->assertCount(2, $got);
    }

    public function testGetRatesAggregateShowByUserID_OrderByDuration()
    {
        $season1 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $season2 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 20]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season2)
                    ->count(10),
                ['rate' => 11]
            )
            ->count(2)
            ->create();

        $got = $this->repository->getRatesAggregateShowByUserID($users[1]->id, 'duration DESC');

        $this->assertLessThanOrEqual($got[0]->duration, $got[1]->duration);
        $this->assertCount(2, $got);
    }

    public function testGetRatesAggregateShowByUserID_OrderByBadColumnNameThrowsException()
    {
        $season1 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $season2 = Season::factory()
            ->for(
                Show::factory()->create()
            )
            ->create();

        $users = User::factory()
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 15]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season1)
                    ->count(10),
                ['rate' => 20]
            )
            ->hasAttached(
                Episode::factory()
                    ->for($season2)
                    ->count(10),
                ['rate' => 11]
            )
            ->count(2)
            ->create();

        $this->expectException(QueryException::class);
        $this->repository->getRatesAggregateShowByUserID($users[1]->id, 'pouet DESC');
    }

    public function testGetRatesAggregateShowByUserID_NotFoundReturnsEmptyArray()
    {
        $got = $this->repository->getRatesAggregateShowByUserID(1, 'sh.name');
        $this->assertEquals([], $got);
    }

    public function testGetRankingShowsByUser()
    {
        $listEpisodes = [];
        for ($i = 0; $i < 10; $i++) {
            $episodes = Episode::factory()
            ->count(10)
            ->for(
                Season::factory()
                ->for(
                    Show::factory()
                )
            )->create();

            array_push($listEpisodes, $episodes);
        }

        $user = User::factory()->hasAttached($listEpisodes, ['rate' =>rand(0, 20)])->create();

        $pouet = User::all()->load('episodes');
        echo $pouet;
    }
}
