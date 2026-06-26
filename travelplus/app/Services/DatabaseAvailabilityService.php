<?php

namespace App\Services;

use Throwable;

class DatabaseAvailabilityService
{
    private const CACHE_KEY = 'database_unavailable_until';
    private const COOLDOWN_SECONDS = 30;

    private static bool $unavailable = false;
    private static array $loggedContexts = [];

    public static function isUnavailable(): bool
    {
        if (self::$unavailable) {
            return true;
        }

        try {
            $unavailableUntil = cache()->get(self::CACHE_KEY);

            if (is_numeric($unavailableUntil) && (int) $unavailableUntil > time()) {
                self::$unavailable = true;

                return true;
            }
        } catch (Throwable) {
            return self::$unavailable;
        }

        return false;
    }

    public static function markUnavailable(Throwable $exception, string $context): void
    {
        self::$unavailable = true;

        if (self::shouldCooldown($exception)) {
            try {
                cache()->save(self::CACHE_KEY, time() + self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS);
            } catch (Throwable) {
                // If cache is unavailable, the in-request guard still prevents repeated DB attempts.
            }
        }

        if (isset(self::$loggedContexts[$context])) {
            return;
        }

        self::$loggedContexts[$context] = true;
        log_message('error', $context . ': ' . $exception->getMessage());
    }

    private static function shouldCooldown(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());
        $code = (string) $exception->getCode();

        return in_array($code, ['1040', '2002', '2006', '2013'], true)
            || str_contains($message, 'too many connections')
            || str_contains($message, 'unable to connect')
            || str_contains($message, 'mysqli')
            || str_contains($message, 'connection')
            || ($exception->getPrevious() !== null && self::shouldCooldown($exception->getPrevious()));
    }
}
