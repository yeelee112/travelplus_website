<?php

namespace App\Services;

use App\Models\UserModel;
use Throwable;

class AuthSessionControlService
{
    private const VALIDATION_CACHE_KEY = 'auth_user_validation';
    private const VALIDATION_TTL_SECONDS = 300;

    private static ?bool $sessionVersionSupported = null;

    public function isSupported(): bool
    {
        if (self::$sessionVersionSupported !== null) {
            return self::$sessionVersionSupported;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            self::$sessionVersionSupported = false;

            return false;
        }

        try {
            self::$sessionVersionSupported = db_connect()->fieldExists('auth_session_version', 'users');
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Auth session support check failed');
            self::$sessionVersionSupported = false;
        }

        return self::$sessionVersionSupported;
    }

    public function buildSessionVersion(array $user): int
    {
        if (! $this->isSupported()) {
            return 0;
        }

        return (int) ($user['auth_session_version'] ?? 0);
    }

    public function invalidateAllSessions(int $userId): void
    {
        if ($userId <= 0 || ! $this->isSupported()) {
            return;
        }

        $db = db_connect();
        $db->table('users')
            ->set('auth_session_version', 'COALESCE(auth_session_version, 0) + 1', false)
            ->where('id', $userId)
            ->update();
    }

    public function loadActiveUser(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            return null;
        }

        try {
            $user = (new UserModel())
                ->where('id', $userId)
                ->where('status', 'active')
                ->first();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Auth session user load failed');

            return null;
        }

        return is_array($user) ? $user : null;
    }

    public function isSessionUserValid(array $authUser): bool
    {
        $userId = (int) ($authUser['id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        if ($this->hasFreshValidation($authUser)) {
            return true;
        }

        $user = $this->loadActiveUser($userId);
        if (! is_array($user)) {
            if (DatabaseAvailabilityService::isUnavailable()) {
                return true;
            }

            $this->clearValidation();
            return false;
        }

        if (! $this->isSupported()) {
            $this->rememberValidation($authUser);
            return true;
        }

        $isValid = (int) ($authUser['auth_session_version'] ?? 0) === (int) ($user['auth_session_version'] ?? 0);

        if ($isValid) {
            $this->rememberValidation($authUser);
        } else {
            $this->clearValidation();
        }

        return $isValid;
    }

    private function hasFreshValidation(array $authUser): bool
    {
        $cached = session()->get(self::VALIDATION_CACHE_KEY);
        if (! is_array($cached)) {
            return false;
        }

        $checkedAt = (int) ($cached['checked_at'] ?? 0);
        if ($checkedAt <= 0 || (time() - $checkedAt) > self::VALIDATION_TTL_SECONDS) {
            return false;
        }

        return (int) ($cached['id'] ?? 0) === (int) ($authUser['id'] ?? 0)
            && (int) ($cached['auth_session_version'] ?? 0) === (int) ($authUser['auth_session_version'] ?? 0);
    }

    private function rememberValidation(array $authUser): void
    {
        session()->set(self::VALIDATION_CACHE_KEY, [
            'id' => (int) ($authUser['id'] ?? 0),
            'auth_session_version' => (int) ($authUser['auth_session_version'] ?? 0),
            'checked_at' => time(),
        ]);
    }

    private function clearValidation(): void
    {
        session()->remove(self::VALIDATION_CACHE_KEY);
    }
}
