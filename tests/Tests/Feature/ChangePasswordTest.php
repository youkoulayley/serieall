<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Utils;

/**
 * ChangePasswordTest.
 */
class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private Utils $utils;

    public function setUp(): void
    {
        parent::createApplication();
        parent::setUp();

        $this->utils = new Utils();
    }

    public function testChangePassword()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $wantUser = $user;
        $wantUser->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $response = $this->post('/profil/password', [
            'password'=> 'password',
            'new_password' => 'TestPassword123',
            'new_password_confirmation' => 'TestPassword123',
        ]);

        $response->assertSessionHas('success', 'Votre mot de passe a bien été modifié !');
        $response->assertRedirect('/');
        $response->assertStatus(302);
        $this->utils->assertUser($user->id, $wantUser, 'TestPassword123');
    }

    public function testChangePassword_NeedsLogin()
    {
        $response = $this->post('/profil/password', [
            'password'=> 'password',
            'new_password' => 'TestPassword123',
            'new_password_confirmation' => 'TestPassword123',
        ]);

        $response->assertRedirect('/login');
        $response->assertStatus(302);
        $this->utils->assertNoUserInDatabase();
    }

    public function testChangePassword_BadPasswordConfirmation()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response = $this->post('/profil/password', [
            'password'=> 'password',
            'new_password' => 'TestPassword123',
            'new_password_confirmation' => 'TestPassword12',
        ]);

        $response->assertRedirect('/');
        $this->assertEquals(422, $response->baseResponse->exception->status);
        $response->assertStatus(302);
        $this->utils->assertUser($user->id, $user, 'password');
    }

    public function testChangePassword_SamePassword()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response = $this->post('/profil/password', [
            'password'=> 'password',
            'new_password' => 'password',
            'new_password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertEquals(422, $response->baseResponse->exception->status);
        $response->assertStatus(302);
        $this->utils->assertUser($user->id, $user, 'password');
    }

    public function testChangePassword_DontMatchPasswordPolicy()
    {
        $user = User::factory()->create();
        $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response = $this->post('/profil/password', [
            'password'=> 'password',
            'new_password' => 'toto',
            'new_password_confirmation' => 'toto',
        ]);

        $response->assertRedirect('/');
        $this->assertEquals(422, $response->baseResponse->exception->status);
        $response->assertStatus(302);
        $this->utils->assertUser($user->id, $user, 'password');
    }
}
