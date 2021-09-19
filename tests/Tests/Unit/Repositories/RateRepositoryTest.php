<?php

namespace Tests\Unit\Repositories;

use App\Interfaces\EpisodeRepositoryInterface;
use App\Interfaces\SeasonRepositoryInterface;
use App\Interfaces\ShowRepositoryInterface;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;
use App\Repositories\RateRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * RateRepositoryTest.
 */
class RateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MockInterface $showRepositoryMock;
    private MockInterface $seasonRepositoryMock;
    private MockInterface $episodeRepositoryMock;
    private RateRepository $repository;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();
        $this->showRepositoryMock = $this->mock(ShowRepositoryInterface::class);
        $this->seasonRepositoryMock = $this->mock(SeasonRepositoryInterface::class);
        $this->episodeRepositoryMock = $this->mock(EpisodeRepositoryInterface::class);

        $this->repository = new RateRepository(
            $this->showRepositoryMock,
            $this->seasonRepositoryMock,
            $this->episodeRepositoryMock
        );
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

        $want = [
            ["rate" => 15, "total" => 5],
            ["rate"=> 20, "total" => 5]
        ];

        $got = $this->repository->getChartRatesByUserID($users[1]->id);
        $this->assertEquals($want, $got);
    }

    public function testGetChartRatesByUserIDUserNotFoundShouldReturnEmptyArray()
    {
        $want = [];

        $got = $this->repository->getChartRatesByUserID("pouet");
        $this->assertEquals($want, $got);
    }
}
