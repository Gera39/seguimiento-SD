<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile.edit', absolute: false));
        $this->assertDatabaseHas('auth_login_audits', [
            'user_id' => $user->id,
            'event_type' => 'LOGIN_SUCCESS',
            'is_success' => true,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseHas('auth_login_audits', [
            'user_id' => $user->id,
            'event_type' => 'LOGIN_FAILED',
            'is_success' => false,
            'failure_reason' => 'invalid_credentials',
        ]);
    }

    public function test_inactive_users_can_not_authenticate(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('auth_login_audits', [
            'user_id' => $user->id,
            'event_type' => 'LOGIN_FAILED',
            'is_success' => false,
            'failure_reason' => 'inactive_user',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
