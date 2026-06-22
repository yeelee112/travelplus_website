<?php

namespace App\Services;

use App\Models\UserModel;
use Throwable;

class AuthSessionControlService
{
    private static ?bool $sessionVersionSupported = null;

    public function isSupported(): bool
    {
        if (self::$sessionVersionSupported !== null) {
            return self::$sessionVersionSupported;
        }

        try {
            self::$sessionVersionSupported = db_connect()->fieldExists('auth_session_version', 'users');
        } catch (Throwable $exception) {
            log_message('error', 'Auth session support check failed: ' . $exception->getMessage());
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

        $user = (new UserModel())
            ->where('id', $userId)
            ->where('status', 'active')
            ->first();

        return is_array($user) ? $user : null;
    }

    public function isSessionUserValid(array $authUser): bool
    {
        $userId = (int) ($authUser['id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $user = $this->loadActiveUser($userId);
        if (! is_array($user)) {
            return false;
        }

        if (! $this->isSupported()) {
            return true;
        }

        return (int) ($authUser['auth_session_version'] ?? 0) === (int) ($user['auth_session_version'] ?? 0);
    }
}
