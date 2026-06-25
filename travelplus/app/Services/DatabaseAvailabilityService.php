<?php

namespace App\Services;

use Throwable;

class DatabaseAvailabilityService
{
    private static bool $unavailable = false;
    private static array $loggedContexts = [];

    public static function isUnavailable(): bool
    {
        return self::$unavailable;
    }

    public static function markUnavailable(Throwable $exception, string $context): void
    {
        self::$unavailable = true;

        if (isset(self::$loggedContexts[$context])) {
            return;
        }

        self::$loggedContexts[$context] = true;
        log_message('error', $context . ': ' . $exception->getMessage());
    }
}
