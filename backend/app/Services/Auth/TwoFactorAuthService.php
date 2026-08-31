<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    protected const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure Base32 TOTP secret key
     */
    public function generateSecretKey(int $length = 16): string
    {
        $secret = '';
        $max = strlen(self::BASE32_CHARS) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[random_int(0, $max)];
        }

        return $secret;
    }

    /**
     * Generate standard otpauth:// provisioning URI for Authenticator apps
     */
    public function generateQrCodeUri(string $company, string $holder, string $secret): string
    {
        $issuer = rawurlencode($company);
        $account = rawurlencode($holder);

        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Verify a 6-digit TOTP code against a secret key using RFC 6238 HMAC-SHA1
     */
    public function verifyKey(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $currentTimeSlice = (int) floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->calculateCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate 6-digit TOTP code for a specific 30-second time slice
     */
    public function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);

        // Pack time slice into 8-byte binary big-endian
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        $hmac = hash_hmac('sha1', $time, $secretKey, true);

        // Dynamic truncation (RFC 4226)
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $modulo = $value % 1000000;

        return str_pad((string) $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate 8 single-use cryptographically secure recovery codes
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4));
            $codes[] = $code;
        }
        return $codes;
    }

    /**
     * Verify and consume a single-use recovery code
     */
    public function verifyAndConsumeRecoveryCode(User $user, string $code): bool
    {
        $code = strtoupper(trim($code));
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];

        if (!is_array($recoveryCodes) || empty($recoveryCodes)) {
            return false;
        }

        $index = array_search($code, $recoveryCodes, true);

        if ($index === false) {
            return false;
        }

        // Consume code (remove from active list)
        unset($recoveryCodes[$index]);
        $user->two_factor_recovery_codes = array_values($recoveryCodes);
        $user->save();

        return true;
    }

    /**
     * Decode a Base32 string to binary
     */
    protected function base32Decode(string $b32): string
    {
        $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
        $buffer = 0;
        $bufferSize = 0;
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            $val = strpos(self::BASE32_CHARS, $char);
            if ($val === false) continue;

            $buffer = ($buffer << 5) | $val;
            $bufferSize += 5;

            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $binary .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }

        return $binary;
    }
}
