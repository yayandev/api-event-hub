<?php

namespace App\Helpers;

class SignatureHelper
{
    public static function generate(array $data, string $secretKey): string
    {
        ksort($data);
        $raw = implode('', $data); // concatenate semua nilai
        return hash_hmac('sha256', $raw, $secretKey);
    }

    public static function verify(array $data, string $receivedSignature, string $secretKey): bool
    {
        $expectedSignature = self::generate($data, $secretKey);
        return hash_equals($expectedSignature, $receivedSignature); // untuk keamanan timing attack
    }
}
