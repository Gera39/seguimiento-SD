<?php

namespace App\Domain\Security\Mfa;

use App\Models\UserMfaMethod;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RecoveryCodeService
{
    public function regenerateFor(UserMfaMethod $method, int $count = 8): array
    {
        $codes = [];

        $method->recoveryCodes()->delete();

        for ($index = 0; $index < $count; $index++) {
            $plainCode = Str::upper(Str::random(4).'-'.Str::random(4));
            $codes[] = $plainCode;

            $method->recoveryCodes()->create([
                'code_hash' => Hash::make($plainCode),
                'created_at' => now(),
            ]);
        }

        return $codes;
    }

    public function redeem(UserMfaMethod $method, string $recoveryCode): bool
    {
        $normalizedCode = Str::upper(trim($recoveryCode));

        $record = $method->recoveryCodes()
            ->whereNull('used_at')
            ->get()
            ->first(fn ($code) => Hash::check($normalizedCode, $code->code_hash));

        if ($record === null) {
            return false;
        }

        $record->forceFill([
            'used_at' => now(),
        ])->save();

        return true;
    }
}
