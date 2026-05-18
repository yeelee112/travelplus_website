<?php

namespace App\Services;

use App\Models\UserModel;

class AuthSessionControlService
{
    public function isSupported(): bool
    {
        return db_connect()->fieldExists('auth_session_version', 'users');
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
