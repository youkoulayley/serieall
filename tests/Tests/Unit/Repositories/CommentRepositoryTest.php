<?php

namespace Tests\Unit\Repositories;

use App\Models\Comment;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Show;
use App\Models\User;
use App\Repositories\CommentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CommentRepositoryTest.
 */
class CommentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CommentRepository $repository;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        $this->repository = new CommentRepository();
    }

    public function testGetCountCommentsByUserIDAndThumb()
    {
        $episode = Episode::factory()->for(Season::factory()->for(Show::factory()))->create();
        $user = User::factory()->create();
        Comment::factory()
            ->count(9)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($user)->create();

        $want = [
            ['thumb' => 1, 'total' => 3],
            ['thumb' => 2, 'total' => 3],
            ['thumb' => 3, 'total' => 3],
        ];

        $got = $this->repository->getCountCommentsByUserIDAndThumb($user->id);

        $this->assertEquals($want, $got->toArray());
    }

    public function testGetCountCommentsByUserIDAndThumb_NotFoundReturnsZero()
    {
        $got = $this->repository->getCountCommentsByUserIDAndThumb(123);

        $this->assertEquals([], $got->toArray());
    }

    public function testGetCommentsOnShowByUserID()
    {
        $show = Show::factory();
        $episode = Episode::factory()->for(Season::factory()->for($show))->create();
        $users = User::factory()->count(2)->create();

        Comment::factory()
            ->count(9)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(12)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($show, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(15)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($show, 'commentable')
            ->for($users[1])->create();

        $got = $this->repository->getCommentsOnShowByUserID($users[0]->id, 'show', [1, 2, 3], 'shows.name');

        $this->assertEquals(12, $got->total());
        $this->assertEquals(3, $got->lastPage());
        $this->assertCount(4, $got->items());
    }

    public function testGetCommentsOnShowByUserID_NoCommentsReturnEmpty()
    {
        $show = Show::factory();
        $episode = Episode::factory()->for(Season::factory()->for($show))->create();
        $users = User::factory()->count(2)->create();

        Comment::factory()
            ->count(9)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(12)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($show, 'commentable')
            ->for($users[0])->create();

        $got = $this->repository->getCommentsOnShowByUserID($users[1]->id, 'show', [1, 2, 3], 'shows.name');

        $this->assertEquals(0, $got->total());
        $this->assertEquals(1, $got->lastPage());
        $this->assertCount(0, $got->items());
    }

    public function testGetCommentsOnSeasonByUserID()
    {
        $season = Season::factory()->for(Show::factory());
        $episode = Episode::factory()->for($season)->create();
        $users = User::factory()->count(2)->create();

        Comment::factory()
            ->count(9)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(12)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($season, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(15)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($season, 'commentable')
            ->for($users[1])->create();

        $got = $this->repository->getCommentsOnSeasonByUserID($users[0]->id, 'season', [1, 2, 3], 'shows.name');

        $this->assertEquals(12, $got->total());
        $this->assertEquals(3, $got->lastPage());
        $this->assertCount(4, $got->items());
    }

    public function testGetCommentsOnSeasonByUserID_NoCommentsReturnEmpty()
    {
        $season = Season::factory()->for(Show::factory());
        $episode = Episode::factory()->for($season)->create();
        $users = User::factory()->count(2)->create();

        Comment::factory()
            ->count(9)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(12)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($season, 'commentable')
            ->for($users[0])->create();

        $got = $this->repository->getCommentsOnShowByUserID($users[1]->id, 'season', [1, 2, 3], 'shows.name');

        $this->assertEquals(0, $got->total());
        $this->assertEquals(1, $got->lastPage());
        $this->assertCount(0, $got->items());
    }

    public function testGetCommentsOnEpisodeByUserID()
    {
        $episode = Episode::factory()->for(Season::factory()->for(Show::factory()))->create();
        $users = User::factory()->count(2)->create();

        Comment::factory()
            ->count(12)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[0])->create();
        Comment::factory()
            ->count(15)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[1])->create();

        $got = $this->repository->getCommentsOnEpisodeByUserID($users[0]->id, 'episode', [1, 2, 3], 'shows.name');

        $this->assertEquals(12, $got->total());
        $this->assertEquals(3, $got->lastPage());
        $this->assertCount(4, $got->items());
    }

    public function testGetCommentsOnEpisodeByUserID_NoCommentsReturnEmpty()
    {
        $episode = Episode::factory()->for(Season::factory()->for(Show::factory()))->create();
        $users = User::factory()->count(2)->create();

        Comment::factory()
            ->count(9)
            ->sequence(
                ['thumb' => 3],
                ['thumb' => 1],
                ['thumb' => 2],
            )
            ->for($episode, 'commentable')
            ->for($users[0])->create();

        $got = $this->repository->getCommentsOnShowByUserID($users[1]->id, 'season', [1, 2, 3], 'shows.name');

        $this->assertEquals(0, $got->total());
        $this->assertEquals(1, $got->lastPage());
        $this->assertCount(0, $got->items());
    }
}
