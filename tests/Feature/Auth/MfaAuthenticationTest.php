<?php

namespace Tests\Feature\Auth;

use App\Domain\Security\Mfa\TotpService;
use App\Models\User;
use App\Models\UserMfaMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Auth\EmailOtpChallengeNotification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MfaAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enroll_and_confirm_mfa_from_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/mfa', [
                'password' => 'password',
            ])
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

        $response->assertRedirect(route('profile.edit', absolute: false));
        $response->assertSessionHas('auth.mfa_verified', true);
        $this->assertDatabaseHas('auth_login_audits', [
            'user_id' => $user->id,
            'mfa_method_id' => $method->id,
            'event_type' => 'MFA_SUCCESS',
            'is_success' => true,
        ]);

        $this->get('/dashboard')->assertRedirect(route('profile.edit', absolute: false));

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
        ])->assertRedirect(route('profile.edit', absolute: false));

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

    public function test_user_can_enable_email_otp_and_receive_a_login_code(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/mfa/email', [
                'password' => 'password',
            ])
            ->assertRedirect(route('profile.edit', absolute: false))
            ->assertSessionHas('mfa_recovery_codes');

        $method = UserMfaMethod::query()
            ->where('user_id', $user->id)
            ->where('method_type', 'EMAIL_OTP')
            ->firstOrFail();

        $this->assertNotNull($method->confirmed_at);

        $this->post('/logout')->assertRedirect('/');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('mfa.challenge.show', absolute: false));

        Notification::assertSentTo($user, EmailOtpChallengeNotification::class);
        $this->get('/mfa/challenge')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/MfaChallenge')
                ->where('challenge.type', 'EMAIL_OTP'));
    }

    public function test_current_password_is_required_to_enable_mfa_methods(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profile')
            ->post('/profile/mfa', [
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/profile')
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('user_mfa_methods', 0);
    }

    public function test_user_can_complete_mfa_challenge_with_email_otp_code(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-04-07 10:00:00');

        $user = User::factory()->create();
        $method = UserMfaMethod::query()->create([
            'user_id' => $user->id,
            'method_type' => 'EMAIL_OTP',
            'label' => 'OTP por correo',
            'destination_masked' => 'te****@example.com',
            'is_primary' => true,
            'confirmed_at' => now(),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('mfa.challenge.show', absolute: false));

        $this->withSession([
            'auth.mfa_email_otp.code_hash' => Hash::make('123456'),
            'auth.mfa_email_otp.expires_at' => now()->addMinutes(5)->toIso8601String(),
            'auth.mfa_email_otp.sent_at' => now()->toIso8601String(),
            'auth.mfa_email_otp.method_id' => $method->id,
        ])->post('/mfa/challenge', [
            'code' => '123456',
        ])->assertRedirect(route('profile.edit', absolute: false));

        $this->assertDatabaseHas('auth_login_audits', [
            'user_id' => $user->id,
            'mfa_method_id' => $method->id,
            'event_type' => 'MFA_SUCCESS',
            'is_success' => true,
        ]);

        Carbon::setTestNow();
    }

    public function test_expired_mfa_challenge_forces_a_fresh_login(): void
    {
        Carbon::setTestNow('2026-04-07 10:00:00');

        $user = User::factory()->create();
        $method = $this->createConfirmedMfaMethod($user, 'JBSWY3DPEHPK3PXP');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('mfa.challenge.show', absolute: false));

        $this->withSession([
            'auth.mfa_required' => true,
            'auth.mfa_verified' => false,
            'auth.mfa_method_id' => $method->id,
            'auth.mfa_intended_url' => route('dashboard', absolute: false),
            'auth.mfa_challenge_expires_at' => now()->subSecond()->toIso8601String(),
        ])->get('/mfa/challenge')
            ->assertRedirect(route('login', absolute: false));

        Carbon::setTestNow();
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
