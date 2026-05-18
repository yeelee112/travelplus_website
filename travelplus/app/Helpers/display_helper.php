<?php

declare(strict_types=1);

use CodeIgniter\I18n\Time;

if (! function_exists('app_datetime')) {
    function app_datetime(null|string $value, string $format = 'd/m/Y H:i', string $fallback = '-'): string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return $fallback;
        }

        try {
            return Time::parse($value, app_timezone())->format($format);
        } catch (Throwable) {
            return $value;
        }
    }
}
