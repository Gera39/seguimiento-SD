<?php

namespace Tests\Feature\Auth;

use App\Domain\Security\Mfa\TotpService;
use App\Models\User;
use App\Models\UserMfaMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MfaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enroll_and_confirm_mfa_from_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/mfa')
            ->assertRedirect(route('profile.edit', absolute: false));

        $method = UserMfaMethod::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertNull($method->confirmed_at);

        $code = app(TotpService::class)->at(
            Crypt::decryptString($method->secret_encrypted),
            Carbon::now()->getTimestamp(),
        );

        $this->actingAs($user)
            ->post('/profile/mfa/confirm', [
                'code' => $code,
            ])
            ->assertRedirect(route('profile.edit', absolute: false))
            ->assertSessionHas('mfa_recovery_codes');

        $method->refresh();

        $this->assertNotNull($method->confirmed_at);
        $this->assertDatabaseCount('user_mfa_recovery_codes', 8);
    }

    public function test_login_requires_totp_verification_when_mfa_is_enabled(): void
    {
        Carbon::setTestNow('2026-04-07 10:00:00');

        $user = User::factory()->create();
        $method = $this->createConfirmedMfaMethod($user, 'JBSWY3DPEHPK3PXP');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('mfa.challenge.show', absolute: false));

        $this->get('/dashboard')
            ->assertRedirect(route('mfa.challenge.show', absolute: false));

        $totpCode = app(TotpService::class)->at(
            Crypt::decryptString($method->secret_encrypted),
            Carbon::now()->getTimestamp(),
        );

        $response = $this->post('/mfa/challenge', [
            'code' => $totpCode,
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHas('auth.mfa_verified', true);
        $this->assertDatabaseHas('auth_login_audits', [
            'user_id' => $user->id,
            'mfa_method_id' => $method->id,
            'event_type' => 'MFA_SUCCESS',
            'is_success' => true,
        ]);

        $this->get('/dashboard')->assertOk();

        Carbon::setTestNow();
    }

    public function test_user_can_complete_mfa_challenge_with_recovery_code(): void
    {
        $user = User::factory()->create();
        $method = $this->createConfirmedMfaMethod($user, 'JBSWY3DPEHPK3PXP');
        $method->recoveryCodes()->create([
            'code_hash' => Hash::make('ABCD-EFGH'),
            'created_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('mfa.challenge.show', absolute: false));

        $this->post('/mfa/challenge/recovery-code', [
            'recovery_code' => 'ABCD-EFGH',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('user_mfa_recovery_codes', [
            'mfa_method_id' => $method->id,
        ]);
        $this->assertNotNull($method->recoveryCodes()->firstOrFail()->used_at);
    }

    public function test_mfa_challenge_screen_can_be_rendered_after_password_login(): void
    {
        $user = User::factory()->create();
        $this->createConfirmedMfaMethod($user, 'JBSWY3DPEHPK3PXP');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->get('/mfa/challenge')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/MfaChallenge')
                ->where('challenge.label', 'Authenticator principal'));
    }

    protected function createConfirmedMfaMethod(User $user, string $secret): UserMfaMethod
    {
        return UserMfaMethod::query()->create([
            'user_id' => $user->id,
            'method_type' => 'TOTP',
            'label' => 'Authenticator principal',
            'secret_encrypted' => Crypt::encryptString($secret),
            'destination_masked' => 'Aplicacion autenticadora',
            'is_primary' => true,
            'confirmed_at' => now(),
            'is_active' => true,
        ]);
    }
}
