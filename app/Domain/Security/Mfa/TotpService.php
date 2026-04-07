<?php

namespace App\Domain\Security\Mfa;

use App\Models\User;
use Illuminate\Support\Str;

class TotpService
{
    protected const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $secret = $this->base32Encode(random_bytes(max(20, (int) ceil($length * 5 / 8))));

        return substr($secret, 0, $length);
    }

    public function provisioningUri(User $user, string $secret, ?string $issuer = null): string
    {
        $issuer ??= config('app.name', 'UTH Planeaciones');
        $label = rawurlencode($issuer.':'.$user->email);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);

        return "otpauth://totp/{$label}?{$query}";
    }

    public function formatSecret(string $secret): string
    {
        return trim(chunk_split(Str::upper($secret), 4, ' '));
    }

    public function verify(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        $normalizedCode = preg_replace('/\s+/', '', $code ?? '');

        if (! is_string($normalizedCode) || ! preg_match('/^\d{6}$/', $normalizedCode)) {
            return false;
        }

        $timestamp ??= now()->getTimestamp();

        for ($offset = -$window; $offset <= $window; $offset++) {
            $candidate = $this->at($secret, $timestamp + ($offset * 30));

            if (hash_equals($candidate, $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    public function at(string $secret, int $timestamp): string
    {
        $counter = (int) floor($timestamp / 30);
        $binaryCounter = pack('N*', 0).pack('N*', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $this->base32Decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        );
        $otp = $binary % 1_000_000;

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $bytes): string
    {
        $binaryString = '';

        foreach (str_split($bytes) as $character) {
            $binaryString .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT);
        }

        $chunks = str_split($binaryString, 5);
        $encoded = '';

        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }

            $encoded .= self::ALPHABET[bindec($chunk)];
        }

        return $encoded;
    }

    protected function base32Decode(string $secret): string
    {
        $cleanSecret = preg_replace('/[^A-Z2-7]/', '', strtoupper($secret));
        $binaryString = '';

        foreach (str_split($cleanSecret ?: '') as $character) {
            $position = strpos(self::ALPHABET, $character);

            if ($position === false) {
                continue;
            }

            $binaryString .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split($binaryString, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
