<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\UserController;
use App\Interfaces\CommentRepositoryInterface;
use App\Interfaces\RateRepositoryInterface;
use App\Interfaces\ShowRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * UserControllerTest.
 */
class UserControllerTest extends TestCase
{
    private UserController $controller;
    private MockInterface $userRepositoryMock;
    private MockInterface $rateRepositoryMock;
    private MockInterface $commentRepositoryMock;
    private MockInterface $showRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = $this->mock(UserRepositoryInterface::class);
        $this->rateRepositoryMock = $this->mock(RateRepositoryInterface::class);
        $this->commentRepositoryMock = $this->mock(CommentRepositoryInterface::class);
        $this->showRepositoryMock = $this->mock(ShowRepositoryInterface::class);

        $this->controller = new UserController(
            $this->userRepositoryMock,
            $this->rateRepositoryMock,
            $this->commentRepositoryMock,
            $this->showRepositoryMock
        );
    }

    public function testIndex()
    {
        $users = [
            new User(['username' => 'toto']),
            new User(['username' => 'test']),
        ];

        $this->userRepositoryMock->shouldReceive('list')->andReturn($users);
        $this->rateRepositoryMock->shouldNotHaveBeenCalled();
        $this->commentRepositoryMock->shouldNotHaveBeenCalled();
        $this->showRepositoryMock->shouldNotHaveBeenCalled();

        $got = $this->controller->index();

        $this->assertEquals($got->getData()['users'], $users);
    }

    public function testGetParameters()
    {
        $user = new User(['username' => 'toto', 'user_url' => 'toto']);

        $this->userRepositoryMock->shouldReceive('getByURL')->andReturn($user);
        $this->rateRepositoryMock->shouldNotHaveBeenCalled();
        $this->commentRepositoryMock->shouldNotHaveBeenCalled();
        $this->showRepositoryMock->shouldNotHaveBeenCalled();

        $got = $this->controller->getParameters('toto');

        $this->assertEquals($got->getData()['user'], $user);
    }
}
