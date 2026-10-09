<?php

namespace App\Services;

/**
 * RFC 6238 TOTP (Time-based One-Time Password) implementation for the
 * optional MFA layer required by the BRD technology stack
 * (Argon2id + TOTP MFA). Pure PHP — no external dependencies.
 */
class TotpService
{
    public const DIGITS     = 6;
    public const PERIOD     = 30;
    public const ALGORITHM  = 'sha1';
    public const SECRET_LEN = 20; // 160 bits (RFC 4226 recommendation)

    /**
     * Generate a new base32-encoded shared secret (160-bit).
     */
    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(self::SECRET_LEN));
    }

    /**
     * Verify a submitted 6-digit code against the secret.
     * Allows ±1 time-step drift for clock skew.
     */
    public static function verify(string $code, string $secret, ?int $atTime = null): bool
    {
        $code = trim($code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $time = $atTime ?? time();
        $window = 1;

        for ($offset = -$window; $offset <= $window; $offset++) {
            $expected = self::generateCode($secret, $time + ($offset * self::PERIOD));
            // Constant-time comparison
            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the current 6-digit code for a secret at a given time.
     */
    public static function generateCode(string $secret, ?int $atTime = null): string
    {
        $time = $atTime ?? time();
        $counter = intdiv($time, self::PERIOD);

        $key  = self::base32Decode($secret);
        $bin  = pack('N*', 0) . pack('N*', $counter); // 8-byte big-endian counter

        $hash = hash_hmac(self::ALGORITHM, $bin, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $value  = ((ord($hash[$offset]) & 0x7F) << 24)
                | ((ord($hash[$offset + 1]) & 0xFF) << 16)
                | ((ord($hash[$offset + 2]) & 0xFF) << 8)
                | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * otpauth:// provisioning URI for authenticator apps.
     */
    public static function provisioningUri(string $secret, string $accountName, string $issuer = 'PIA-AFMS'): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            $label,
            $secret,
            rawurlencode($issuer),
            strtoupper(self::ALGORITHM),
            self::DIGITS,
            self::PERIOD
        );
    }

    public static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bytes    = array_values(unpack('C*', $data));
        $bits     = 0;
        $value    = 0;
        $output   = '';

        foreach ($bytes as $byte) {
            $value = ($value << 8) | $byte;
            $bits  += 8;
            while ($bits >= 5) {
                $output   .= $alphabet[($value >> ($bits - 5)) & 31];
                $bits     -= 5;
            }
        }

        if ($bits > 0) {
            $output .= $alphabet[($value << (5 - $bits)) & 31];
        }

        return $output;
    }

    public static function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input    = strtoupper(rtrim(trim($input), '='));
        $bits     = 0;
        $value    = 0;
        $output   = '';

        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $pos = strpos($alphabet, $input[$i]);
            if ($pos === false) {
                continue;
            }
            $value = ($value << 5) | $pos;
            $bits  += 5;
            if ($bits >= 8) {
                $output .= chr(($value >> ($bits - 8)) & 0xFF);
                $bits   -= 8;
            }
        }

        return $output;
    }
}
