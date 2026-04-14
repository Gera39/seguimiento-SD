<?php

namespace App\Domain\Security\Mfa;

use App\Models\User;
use App\Models\UserMfaMethod;
use App\Notifications\Auth\EmailOtpChallengeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class EmailOtpService
{
    public const CODE_HASH_KEY = 'auth.mfa_email_otp.code_hash';
    public const EXPIRES_AT_KEY = 'auth.mfa_email_otp.expires_at';
    public const SENT_AT_KEY = 'auth.mfa_email_otp.sent_at';
    public const METHOD_ID_KEY = 'auth.mfa_email_otp.method_id';

    public function issueChallenge(Request $request, User $user, UserMfaMethod $method, bool $force = false): void
    {
        if (! $force && ! $this->canResend($request)) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addSeconds($this->expiresInSeconds());

        $request->session()->put([
            self::CODE_HASH_KEY => Hash::make($code),
            self::EXPIRES_AT_KEY => $expiresAt->toIso8601String(),
            self::SENT_AT_KEY => now()->toIso8601String(),
            self::METHOD_ID_KEY => $method->id,
        ]);

        $user->notify(new EmailOtpChallengeNotification($code, $expiresAt, $method->destination_masked));
    }

    public function verify(Request $request, UserMfaMethod $method, string $code): bool
    {
        $hash = $request->session()->get(self::CODE_HASH_KEY);
        $methodId = $request->session()->get(self::METHOD_ID_KEY);
        $normalizedCode = preg_replace('/\s+/', '', $code);

        if (! is_string($hash) || ! is_numeric($methodId) || (int) $methodId !== $method->id) {
            return false;
        }

        if ($this->codeExpired($request) || ! is_string($normalizedCode) || ! preg_match('/^\d{6}$/', $normalizedCode)) {
            return false;
        }

        return Hash::check($normalizedCode, $hash);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget([
            self::CODE_HASH_KEY,
            self::EXPIRES_AT_KEY,
            self::SENT_AT_KEY,
            self::METHOD_ID_KEY,
        ]);
    }

    public function codeExpired(Request $request): bool
    {
        $expiresAt = $request->session()->get(self::EXPIRES_AT_KEY);

        if (! is_string($expiresAt) || $expiresAt === '') {
            return true;
        }

        return now()->greaterThanOrEqualTo($expiresAt);
    }

    public function canResend(Request $request): bool
    {
        $sentAt = $request->session()->get(self::SENT_AT_KEY);

        if (! is_string($sentAt) || $sentAt === '') {
            return true;
        }

        return now()->greaterThanOrEqualTo(Carbon::parse($sentAt)->addSeconds($this->resendCooldownSeconds()));
    }

    public function resendAvailableAt(Request $request): ?string
    {
        $sentAt = $request->session()->get(self::SENT_AT_KEY);

        if (! is_string($sentAt) || $sentAt === '') {
            return null;
        }

        return Carbon::parse($sentAt)->addSeconds($this->resendCooldownSeconds())->toIso8601String();
    }

    public function expiresAt(Request $request): ?string
    {
        $expiresAt = $request->session()->get(self::EXPIRES_AT_KEY);

        return is_string($expiresAt) && $expiresAt !== '' ? $expiresAt : null;
    }

    protected function expiresInSeconds(): int
    {
        return max(60, (int) config('auth.mfa.email_otp_expire_seconds', 600));
    }

    protected function resendCooldownSeconds(): int
    {
        return max(15, (int) config('auth.mfa.email_otp_resend_cooldown_seconds', 60));
    }
}
