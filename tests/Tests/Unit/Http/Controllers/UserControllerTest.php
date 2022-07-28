<?php

namespace Tests\Unit\Http\Controllers;

use App\Charts\RateSummary;
use App\Http\Controllers\UserController;
use App\Http\Requests\ChangeInfoRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Interfaces\UserServiceInterface;
use App\Models\Comment;
use App\Models\Show;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * UserControllerTest.
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private UserServiceInterface $userServiceMock;

    private UserController $controller;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        /** @var UserServiceInterface&MockObject&MockInterface $userServiceMock */
        $userServiceMock = $this->mock(UserServiceInterface::class);

        $this->userServiceMock = $userServiceMock;
        $this->controller = new UserController($this->userServiceMock);
    }

    public function testIndex()
    {
        $user = User::factory()->make();

        $wantMock = [$user];

        $this->userServiceMock
            ->shouldReceive('list')
            ->with()
            ->andReturn($wantMock);

        $want = ['users' => $wantMock];

        $response = $this->controller->index();
        $this->assertEquals($response->getData(), $want);
    }

    public function testGetProfile()
    {
        $user = User::factory()->make();

        $wantMock = ['user' => $user];

        $this->userServiceMock
            ->shouldReceive('getProfile')
            ->with($user->user_url)
            ->andReturn($wantMock);

        $want = ['data' => $wantMock];

        $response = $this->controller->getProfile($user->user_url);
        $this->assertEquals($response->getData(), $want);
    }

    public function testGetRates()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        Request::spy();
        Request::shouldReceive('ajax')->andReturn(false);

        $wantMock = [
            'allRates' => [
                (object) [
                    'thetvdb_id' => 0,
                    'name' => 'Lorine Balistreri',
                    'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                    'format' => 40,
                    'username' => 'toto',
                    'duration' => 800,
                    'count_rate' => 20,
                    'avg_rate' => 17.5,
                ],
                (object) [
                    'thetvdb_id' => 1,
                    'name' => 'Breaking Bad',
                    'show_url' => 'breaking-bad',
                    'format' => 50,
                    'username' => 'toto',
                    'duration' => 800,
                    'count_rate' => 20,
                    'avg_rate' => 13.5,
                ],
            ],
            'chart' => new RateSummary(),
        ];

        $this->userServiceMock
            ->shouldReceive('getRates')
            ->with($user->user_url)
            ->andReturn($wantMock);

        $want = ['data' => $wantMock];

        $response = $this->controller->getRates($user->user_url);
        $this->assertEquals($response->getData(), $want);
    }

    public function testGetRatesAjax()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        Request::spy();
        Request::shouldReceive('ajax')->andReturn(true);

        $want = [
            'allRates' => [
                (object) [
                    'thetvdb_id' => 0,
                    'name' => 'Lorine Balistreri',
                    'show_url' => 'illo-vitae-illo-delectus-fuga-facere-ut',
                    'format' => 40,
                    'username' => 'toto',
                    'duration' => 800,
                    'count_rate' => 20,
                    'avg_rate' => 17.5,
                ],
                (object) [
                'thetvdb_id' => 1,
                'name' => 'Breaking Bad',
                'show_url' => 'breaking-bad',
                'format' => 50,
                'username' => 'toto',
                'duration' => 800,
                'count_rate' => 20,
                'avg_rate' => 13.5,
                ],
            ],
        ];

        $this->userServiceMock
            ->shouldReceive('getRatesAjax')
            ->with($user->user_url, 'count')
            ->andReturn($want);

        $response = $this->controller->getRates($user->user_url, 'count');
        $this->assertStringContainsString('illo-vitae-illo-delectus-fuga-facere-ut', $response->content());
        $this->assertStringContainsString('breaking-bad', $response->content());
    }

    public function testGetComments()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        Request::spy();
        Request::shouldReceive('ajax')->andReturn(false);

        $wantMock = [
            'comments' => [
                'show' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4),
                'season' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4),
                'episode' => new LengthAwarePaginator([new Comment(['thumb' => 1])], 1, 4),
            ],
            'chart' => new RateSummary(),
        ];

        $this->userServiceMock
            ->shouldReceive('getComments')
            ->with($user->user_url, '', '')
            ->andReturn($wantMock);

        $want = ['data' => $wantMock];

        $response = $this->controller->getComments($user->user_url);
        $this->assertEquals($response->getData(), $want);
    }

    public function testGetCommentsAjax()
    {
        $user = User::factory();
        $show = Show::factory();
        $comment = Comment::factory()->for($show, 'commentable')->for($user)->make();
        $comment = $comment->load(['user', 'commentable']);

        $user = $user->make();

        /* @var User $user */
        Auth::login($user);

        Request::spy();
        Request::shouldReceive('ajax')->andReturn(true);

        $wantMock = [
            'comments' =>  new LengthAwarePaginator([$comment], 1, 4),
        ];

        $this->userServiceMock
            ->shouldReceive('getCommentsAjax')
            ->with($user->user_url, 'show', '', '')
            ->andReturn($wantMock);

        $response = $this->controller->getComments($user->user_url, 'show');
        $this->assertStringContainsString($comment->message, $response->content());
        $this->assertStringContainsString($comment->commentable->show_url, $response->content());
    }

    public function testGetNotifications()
    {
        $user = User::factory()->make();

        $wantMock = ['user' => $user];

        $this->userServiceMock
            ->shouldReceive('getNotifications')
            ->with($user->user_url)
            ->andReturn($wantMock);

        $want = ['data' => $wantMock];

        $response = $this->controller->getNotifications($user->user_url);
        $this->assertEquals($response->getData(), $want);
    }

    public function testGetShows()
    {
        $user = User::factory()->make();

        $wantMock = ['user' => $user];

        $this->userServiceMock
            ->shouldReceive('getShows')
            ->with($user->user_url)
            ->andReturn($wantMock);

        $want = ['data' => $wantMock];

        $response = $this->controller->getShows($user->user_url);
        $this->assertEquals($response->getData(), $want);
    }

    public function testGetParameters()
    {
        $user = User::factory()->make();

        $wantMock = $user;

        $this->userServiceMock
            ->shouldReceive('getUserByURL')
            ->with($user->user_url)
            ->andReturn($wantMock);

        $want = ['user' => $wantMock];

        $response = $this->controller->getParameters($user->user_url);
        $this->assertEquals($response->getData(), $want);
    }

    public function testChangePassword()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        $request = ChangePasswordRequest::create('/profil/password', 'POST', [
            'password' => 'password',
            'new_password' => 'AlloAllo123',
            'new_password_confirmation' => 'AlloAllo123',
        ]);

        $this->userServiceMock
            ->shouldReceive('changePassword')
            ->with($user, 'password', 'AlloAllo123')
            ->andReturn(true);

        $response = $this->controller->changePassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('Votre mot de passe a bien été modifié !', $response->getSession()->get('success'));
        $this->assertEquals('http://localhost', $response->getTargetUrl());
    }

    public function testChangePassword_ActualPasswordDoesntMatch()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        $request = ChangePasswordRequest::create('/profil/password', 'POST', [
            'password' => 'password2',
            'new_password' => 'AlloAllo123',
            'new_password_confirmation' => 'AlloAllo123',
        ]);

        $this->userServiceMock
            ->shouldReceive('changePassword')
            ->with($user, 'password2', 'AlloAllo123')
            ->andReturn(false);

        $response = $this->controller->changePassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('Votre mot de passe actuel ne correspond pas à celui saisi.', $response->getSession()->get('warning'));
        $this->assertEquals('http://localhost', $response->getTargetUrl());
    }

    public function testChangePassword_UserServiceThrowsException()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        $request = ChangePasswordRequest::create('/profil/password', 'POST', [
            'password' => 'password2',
            'new_password' => 'AlloAllo123',
            'new_password_confirmation' => 'AlloAllo123',
        ]);

        $this->userServiceMock
            ->shouldReceive('changePassword')
            ->with($user, 'password2', 'AlloAllo123')
            ->andThrow(Exception::class);

        $response = $this->controller->changePassword($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('Impossible de modifier votre mot de passe. Veuillez réessayer.', $response->getSession()->get('error'));
        $this->assertEquals('http://localhost', $response->getTargetUrl());
    }

    public function testChangeInfo()
    {
        $user = User::factory()->make();

        /* @var User $user */
        Auth::login($user);

        $request = ChangeInfoRequest::create('/profil/info', 'POST', [
            'email' => 'foo@bar.com',
        ]);

        $this->userServiceMock
            ->shouldReceive('changeInfo')
            ->with($user, $request);

        $response = $this->controller->changeInfo($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('Vos informations personnelles ont été modifiées !', $response->getSession()->get('success'));
        $this->assertEquals('http://localhost', $response->getTargetUrl());
    }
}
