<?php

namespace App\Domain\Security\Mfa;

use App\Models\User;
use App\Models\UserMfaMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MfaSessionService
{
    public const REQUIRED_KEY = 'auth.mfa_required';
    public const VERIFIED_KEY = 'auth.mfa_verified';
    public const VERIFIED_AT_KEY = 'auth.mfa_verified_at';
    public const INTENDED_URL_KEY = 'auth.mfa_intended_url';
    public const METHOD_ID_KEY = 'auth.mfa_method_id';
    public const CHALLENGE_STARTED_AT_KEY = 'auth.mfa_challenge_started_at';
    public const CHALLENGE_EXPIRES_AT_KEY = 'auth.mfa_challenge_expires_at';

    public function beginChallenge(Request $request, UserMfaMethod $method, string $fallbackUrl): void
    {
        $request->session()->put([
            self::REQUIRED_KEY => true,
            self::VERIFIED_KEY => false,
            self::VERIFIED_AT_KEY => null,
            self::METHOD_ID_KEY => $method->id,
            self::INTENDED_URL_KEY => $request->session()->pull('url.intended', $fallbackUrl),
            self::CHALLENGE_STARTED_AT_KEY => now()->toIso8601String(),
            self::CHALLENGE_EXPIRES_AT_KEY => now()->addSeconds($this->challengeTimeoutSeconds())->toIso8601String(),
        ]);
    }

    public function markVerified(Request $request, User $user, ?UserMfaMethod $method = null): void
    {
        $this->primeVerifiedSession($request, $method);

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }

    public function isVerified(Request $request): bool
    {
        if ($this->verifiedSessionExpired($request)) {
            $this->clear($request);

            return false;
        }

        return (bool) $request->session()->get(self::VERIFIED_KEY, false);
    }

    public function verificationRequired(Request $request): bool
    {
        return (bool) $request->session()->get(self::REQUIRED_KEY, false);
    }

    public function pendingMethodId(Request $request): ?int
    {
        $methodId = $request->session()->get(self::METHOD_ID_KEY);

        return is_numeric($methodId) ? (int) $methodId : null;
    }

    public function intendedUrl(Request $request, string $fallbackUrl): string
    {
        return (string) $request->session()->pull(self::INTENDED_URL_KEY, $fallbackUrl);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget([
            self::REQUIRED_KEY,
            self::VERIFIED_KEY,
            self::VERIFIED_AT_KEY,
            self::INTENDED_URL_KEY,
            self::METHOD_ID_KEY,
            self::CHALLENGE_STARTED_AT_KEY,
            self::CHALLENGE_EXPIRES_AT_KEY,
        ]);
    }

    public function primeVerifiedSession(Request $request, ?UserMfaMethod $method = null): void
    {
        $request->session()->put([
            self::REQUIRED_KEY => false,
            self::VERIFIED_KEY => true,
            self::VERIFIED_AT_KEY => now()->toIso8601String(),
            self::METHOD_ID_KEY => $method?->id,
            self::CHALLENGE_STARTED_AT_KEY => null,
            self::CHALLENGE_EXPIRES_AT_KEY => null,
        ]);
    }

    public function challengeExpired(Request $request): bool
    {
        $expiresAt = $request->session()->get(self::CHALLENGE_EXPIRES_AT_KEY);

        if (! is_string($expiresAt) || $expiresAt === '') {
            return false;
        }

        return now()->greaterThanOrEqualTo($expiresAt);
    }

    public function verifiedSessionExpired(Request $request): bool
    {
        $verifiedAt = $request->session()->get(self::VERIFIED_AT_KEY);

        if (! is_string($verifiedAt) || $verifiedAt === '') {
            return false;
        }

        return now()->greaterThanOrEqualTo(
            Carbon::parse($verifiedAt)->addSeconds($this->verifiedTimeoutSeconds()),
        );
    }

    protected function challengeTimeoutSeconds(): int
    {
        return max(60, (int) config('auth.mfa.challenge_timeout_seconds', 900));
    }

    protected function verifiedTimeoutSeconds(): int
    {
        return max(300, (int) config('auth.mfa.verified_timeout_seconds', 14_400));
    }
}
