<?php

namespace App\Services;

use Throwable;

class AdminAccessService
{
    private static ?bool $usersTableHasIsAdmin = null;

    /**
     * @param array<string, mixed>|null $authUser
     */
    public function isAdmin(?array $authUser): bool
    {
        if (! is_array($authUser) || empty($authUser['id'])) {
            return false;
        }

        if (array_key_exists('is_admin', $authUser)) {
            return (bool) $authUser['is_admin'];
        }

        $userId = (int) ($authUser['id'] ?? 0);
        $email = strtolower(trim((string) ($authUser['email'] ?? '')));

        if ($userId > 0 && $this->usersTableHasIsAdmin()) {
            try {
                $row = db_connect()->table('users')
                    ->select('is_admin')
                    ->where('id', $userId)
                    ->limit(1)
                    ->get()
                    ->getRowArray();
            } catch (Throwable $exception) {
                DatabaseAvailabilityService::markUnavailable($exception, 'Admin access user lookup failed');
                $row = null;
            }

            if (is_array($row)) {
                return (bool) ($row['is_admin'] ?? false);
            }
        }

        if ($email === '') {
            return false;
        }

        return in_array($email, $this->allowedEmails(), true);
    }

    /**
     * @return string[]
     */
    private function allowedEmails(): array
    {
        $raw = (string) env('admin.allowedEmails', '');
        $emails = array_filter(array_map(
            static fn(string $email): string => strtolower(trim($email)),
            explode(',', $raw)
        ));

        return array_values(array_unique($emails));
    }

    private function usersTableHasIsAdmin(): bool
    {
        if (self::$usersTableHasIsAdmin !== null) {
            return self::$usersTableHasIsAdmin;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            self::$usersTableHasIsAdmin = false;

            return false;
        }

        try {
            $db = db_connect();

            self::$usersTableHasIsAdmin = $db->tableExists('users') && $db->fieldExists('is_admin', 'users');
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Admin access schema check failed');
            self::$usersTableHasIsAdmin = false;
        }

        return self::$usersTableHasIsAdmin;
    }
}
