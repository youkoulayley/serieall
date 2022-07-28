<?php

namespace Tests\Unit\Repositories;

use App\Models\Article;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * UserRepositoryTest.
 */
class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        $this->repository = new UserRepository();
    }

    public function testGetByID()
    {
        $users = User::factory()
            ->count(2)
            ->create();
        $want = $users[1];

        $got = $this->repository->getByID($want->id);

        $this->assertEquals($want->getAttributes(), $got->getAttributes());
    }

    public function testGetByID_NotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->getByID(1);
    }

    public function testGetByURL()
    {
        $users = User::factory()
            ->count(2)
            ->create();
        $want = $users[1];

        $got = $this->repository->getByURL($want->user_url);

        $this->assertEquals($want->getAttributes(), $got->getAttributes());
    }

    public function testGetByURL_NotFoundThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->getBYURL(1);
    }

    public function testGetByUsername()
    {
        $users = User::factory()->count(2)->create();
        $want = $users[1];

        $got = $this->repository->getByUsername($want->username);

        $this->assertEquals($want->getAttributes(), $got->getAttributes());
    }

    public function testGetByUsername_NotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->getByUsername('abcdef');
    }

    public function testGetByURLWithPublishedArticles()
    {
        $users = User::factory()
            ->count(2)
            ->has(
                Article::factory()
                    ->count(3)
                    ->for(
                        Category::factory()
                    )
            )
            ->create();
        $want = $users[1];

        $got = $this->repository->getByURLWithPublishedArticles($want->user_url);

        $this->assertEquals($want->getAttributes(), $got->getAttributes());
        $this->assertCount(2, $got->articles);
    }

    public function testGetByURLWithPublishedArticles_NotFoundThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->getByURLWithPublishedArticles('toto');
    }

    public function testList()
    {
        $users = User::factory()
            ->count(2)
            ->create();
        $want = $users->toArray();

        $gotUsers = $this->repository->list();
        $got = json_decode(json_encode($gotUsers->toArray()), true);

        $this->assertEquals(sort($want), sort($got));
    }

    public function testList_NotFoundReturnsEmptyList()
    {
        $gotUsers = $this->repository->list();
        $got = json_decode(json_encode($gotUsers->toArray()));
        $want = [];

        $this->assertEquals($want, $got);
    }

    public function testGetEpisodePlanning()
    {
        $user = User::factory()
            ->count(1)
            ->hasAttached(
                Show::factory()
                    ->count(1)
                    ->has(
                        Season::factory()
                            ->count(1)
                            ->has(
                                Episode::factory()
                                    ->count(6)
                                    ->sequence(
                                        [
                                            'diffusion_us' => date_create(),
                                            'diffusion_fr' => date_create(),
                                        ],
                                        [],
                                    )
                            )
                    ),
                ['state' => 1]
            )
            ->create();
        $want = $user[0];

        $got = $this->repository->getEpisodePlanning($want->id, 1);

        $this->assertCount(3, $got);
    }

    public function testSave()
    {
        $user = User::factory()->make();

        $this->repository->save($user);
        $got = $this->repository->getByURL($user->user_url);

        $this->assertEquals($user->getAttributes(), $got->getAttributes());
    }

    public function testSave_ThrowsException()
    {
        config(['database.connections.mysql.password' => 'toto']);
        DB::reconnect('mysql');

        $user = User::factory()->make();

        $this->expectException(Exception::class);
        $this->repository->save($user);
    }
}
