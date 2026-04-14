<?php

namespace App\Domain\Security\Mfa;

use App\Models\User;
use App\Models\UserMfaMethod;
use Illuminate\Support\Facades\Crypt;

class UserMfaManager
{
    public function __construct(
        protected TotpService $totpService,
        protected RecoveryCodeService $recoveryCodeService,
    ) {
    }

    public function primaryMethodFor(User $user): ?UserMfaMethod
    {
        return $user->mfaMethods()
            ->where('is_active', true)
            ->whereNotNull('confirmed_at')
            ->orderByDesc('is_primary')
            ->orderByDesc('confirmed_at')
            ->first();
    }

    public function pendingMethodFor(User $user): ?UserMfaMethod
    {
        return $user->mfaMethods()
            ->where('is_active', true)
            ->whereNull('confirmed_at')
            ->latest('id')
            ->first();
    }

    public function createPendingTotp(User $user, string $label = 'Authenticator principal'): UserMfaMethod
    {
        $user->mfaMethods()
            ->whereNull('confirmed_at')
            ->delete();

        return $user->mfaMethods()->create([
            'method_type' => 'TOTP',
            'label' => $label,
            'secret_encrypted' => Crypt::encryptString($this->totpService->generateSecret()),
            'destination_masked' => 'Aplicacion autenticadora',
            'is_primary' => true,
            'is_active' => true,
        ]);
    }

    public function enableEmailOtp(User $user, string $label = 'OTP por correo'): array
    {
        $user->mfaMethods()
            ->whereNull('confirmed_at')
            ->delete();

        $method = $user->mfaMethods()
            ->firstOrNew([
                'method_type' => 'EMAIL_OTP',
            ]);

        $user->mfaMethods()
            ->where('id', '!=', $method->id)
            ->update([
                'is_primary' => false,
                'is_active' => false,
            ]);

        $method->fill([
            'label' => $label,
            'secret_encrypted' => null,
            'destination_masked' => $this->maskEmail($user->email),
            'is_primary' => true,
            'confirmed_at' => now(),
            'is_active' => true,
        ])->save();

        $recoveryCodes = $this->recoveryCodeService->regenerateFor($method);

        return [$method->fresh('recoveryCodes'), $recoveryCodes];
    }

    public function confirmPendingTotp(User $user, string $code): array
    {
        $pendingMethod = $this->pendingMethodFor($user);

        if ($pendingMethod === null) {
            return [null, []];
        }

        $secret = Crypt::decryptString($pendingMethod->secret_encrypted);

        if (! $this->totpService->verify($secret, $code)) {
            return [null, []];
        }

        $user->mfaMethods()
            ->where('id', '!=', $pendingMethod->id)
            ->update([
                'is_primary' => false,
                'is_active' => false,
            ]);

        $pendingMethod->forceFill([
            'confirmed_at' => now(),
            'is_primary' => true,
            'is_active' => true,
        ])->save();

        $recoveryCodes = $this->recoveryCodeService->regenerateFor($pendingMethod);

        return [$pendingMethod->fresh('recoveryCodes'), $recoveryCodes];
    }

    public function disable(User $user): void
    {
        $user->mfaMethods()->delete();
    }

    public function regenerateRecoveryCodes(User $user): array
    {
        $method = $this->primaryMethodFor($user);

        if ($method === null) {
            return [];
        }

        return $this->recoveryCodeService->regenerateFor($method);
    }

    public function setupPayload(User $user): array
    {
        $primaryMethod = $this->primaryMethodFor($user);
        $pendingMethod = $this->pendingMethodFor($user);

        return [
            'enabled' => $primaryMethod !== null,
            'method' => $primaryMethod ? [
                'id' => $primaryMethod->id,
                'type' => $primaryMethod->method_type,
                'label' => $primaryMethod->label,
                'destination_masked' => $primaryMethod->destination_masked,
                'confirmed_at' => optional($primaryMethod->confirmed_at)->format('d/m/Y H:i'),
                'last_used_at' => optional($primaryMethod->last_used_at)->format('d/m/Y H:i'),
                'recovery_codes_remaining' => $primaryMethod->recoveryCodes()->whereNull('used_at')->count(),
            ] : null,
            'pending' => $pendingMethod ? [
                'id' => $pendingMethod->id,
                'label' => $pendingMethod->label,
                'secret' => $secret = Crypt::decryptString($pendingMethod->secret_encrypted),
                'formatted_secret' => $this->totpService->formatSecret($secret),
                'otpauth_uri' => $this->totpService->provisioningUri($user, $secret),
            ] : null,
        ];
    }

    protected function maskEmail(string $email): string
    {
        [$localPart, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return $email;
        }

        $visible = substr($localPart, 0, min(2, strlen($localPart)));
        $masked = str_repeat('*', max(2, strlen($localPart) - strlen($visible)));

        return "{$visible}{$masked}@{$domain}";
    }
}
