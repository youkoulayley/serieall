<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils;

/**
 * ChangeInfoTest.
 */
class ChangeInfoTest extends TestCase
{
    use RefreshDatabase;

    private Utils $utils;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        $this->utils = new Utils();
    }

    public function testChangeInfo()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response = $this->post('/profil/info', [
            'email'=> 'foo@email.com',
            'antispoiler' => false,
            'twitter' => 'twitter',
            'facebook' => 'facebook',
            'website' => 'website',
            'edito' => 'edito',
        ]);

        $wantUser = $user;
        $wantUser->email = 'foo@email.com';
        $wantUser->antispoiler = false;
        $wantUser->twitter = 'twitter';
        $wantUser->facebook = 'facebook';
        $wantUser->website = 'website';
        $wantUser->edito = 'edito';

        $response->assertSessionHas('success', 'Vos informations personnelles ont été modifiées !');
        $response->assertRedirect('/');
        $response->assertStatus(302);
        $this->utils->assertUser($user->id, $wantUser, 'password');
    }

    public function testChangeInfo_NeedsLogin()
    {
        $response = $this->post('/profil/info', [
            'edito' => 'Edito',
        ]);

        $response->assertRedirect('/login');
        $response->assertStatus(302);
        $this->utils->assertNoUserInDatabase();
    }

    public function testChangeInfo_ValidEmail()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response = $this->post('/profil/info', [
            'email'=> 'foo',
            'antispoiler' => false,
            'twitter' => 'twitter',
            'facebook' => 'facebook',
            'website' => 'website',
            'edito' => 'edito',
        ]);

        $response->assertRedirect('/');
        $response->assertStatus(302);
        $this->utils->assertUser($user->id, $user, 'password');
    }

    public function testChangeInfo_EmailAlreadyUsed()
    {
        $users = User::factory()->count(2)->create();

        $this->post('/login', [
            'username' => $users[0]->username,
            'password' => 'password',
        ]);

        $response = $this->post('/profil/info', [
            'email'=> $users[1]->email,
            'antispoiler' => false,
            'twitter' => 'twitter',
            'facebook' => 'facebook',
            'website' => 'website',
            'edito' => 'edito',
        ]);

        $response->assertRedirect('/');
        $response->assertStatus(302);
        $this->utils->assertUser($users[0]->id, $users[0], 'password');
        $this->utils->assertUser($users[1]->id, $users[1], 'password');
    }
}
