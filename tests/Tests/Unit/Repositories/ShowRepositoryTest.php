<?php

namespace Tests\Unit\Repositories;

use App\Models\Show;
use App\Models\User;
use App\Repositories\ShowRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ShowRepositoryTest.
 */
class ShowRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ShowRepository $repository;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        $this->repository = new ShowRepository();
    }

    public function testGetShowFollowedByUser()
    {
        $shows = Show::factory()->count(2);
        $users = User::factory()
            ->hasAttached($shows, ['state'=> config('shows.inProgress')])
            ->count(5)
            ->create();

        $got = $this->repository->getShowFollowedByUser($users[2]->id);

        $this->assertCount(2, $got->toArray());
    }

    public function testGetShowFollowedByUser_NoFollowedShows()
    {
        $users = User::factory()
            ->count(5)
            ->create();

        $got = $this->repository->getShowFollowedByUser($users[2]->id);

        $this->assertCount(0, $got->toArray());
    }
}
