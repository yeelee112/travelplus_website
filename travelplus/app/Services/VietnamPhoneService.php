<?php

namespace App\Services;

class VietnamPhoneService
{
    public static function normalize(?string $phone): string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return '';
        }

        $phone = preg_replace('/[^\d+]/u', '', $phone) ?? '';

        if (str_starts_with($phone, '+84')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '84')) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    public static function isValid(?string $phone): bool
    {
        $phone = self::normalize($phone);

        if ($phone === '') {
            return false;
        }

        return preg_match('/^(?:0(?:2\d{8,9}|[35789]\d{8}))$/', $phone) === 1;
    }
}
