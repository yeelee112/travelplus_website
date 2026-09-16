<?php

namespace App\Services;

use App\Models\UserModel;
use Throwable;

class RememberLoginService
{
    private const COOKIE_NAME = 'travelplus_remember';
    private const LIFETIME_SECONDS = 2592000; // 30 days
    private static ?bool $tokenTableExists = null;

    public function issue(array $user): void
    {
        if (! $this->hasTokenTable()) {
            return;
        }

        $db = db_connect();

        $this->deleteCurrentSelector();

        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME_SECONDS);

        $db->table('user_remember_tokens')->insert([
            'user_id' => (int) $user['id'],
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
            'user_agent' => substr((string) service('request')->getUserAgent(), 0, 500),
            'expires_at' => $expiresAt,
            'last_used_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->writeClientCookie($selector . ':' . $validator);
    }

    public function restoreUser(): ?array
    {
        $rawCookie = (string) service('request')->getCookie(self::COOKIE_NAME);
        if ($rawCookie === '' || ! str_contains($rawCookie, ':')) {
            return null;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            return null;
        }

        if (! $this->hasTokenTable()) {
            return null;
        }

        $db = db_connect();

        [$selector, $validator] = explode(':', $rawCookie, 2);
        $selector = trim($selector);
        $validator = trim($validator);

        if ($selector === '' || $validator === '') {
            $this->expireClientCookie();
            return null;
        }

        try {
            $row = $db->table('user_remember_tokens')
                ->where('selector', $selector)
                ->get()
                ->getRowArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Remember login token lookup failed');

            return null;
        }

        if (! is_array($row)) {
            $this->expireClientCookie();
            return null;
        }

        if (strtotime((string) ($row['expires_at'] ?? '')) < time()) {
            $this->deleteSelector($selector);
            $this->expireClientCookie();
            return null;
        }

        if (! hash_equals((string) ($row['token_hash'] ?? ''), hash('sha256', $validator))) {
            $this->expireClientCookie();
            return null;
        }

        try {
            $user = (new UserModel())
                ->where('id', (int) $row['user_id'])
                ->where('status', 'active')
                ->first();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Remember login user load failed');

            return null;
        }

        if (! is_array($user)) {
            $this->deleteSelector($selector);
            $this->expireClientCookie();
            return null;
        }

        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME_SECONDS);

        try {
            $db->table('user_remember_tokens')
                ->where('id', (int) $row['id'])
                ->update([
                    'expires_at' => $expiresAt,
                    'last_used_at' => $now,
                    'updated_at' => $now,
                ]);
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Remember login token refresh failed');
        }

        // Keep the validator stable across restoration requests. Rotating it here
        // makes concurrent requests invalidate each other after a session reset.
        $this->writeClientCookie($selector . ':' . $validator);

        return $user;
    }

    public function clear(): void
    {
        $rawCookie = (string) service('request')->getCookie(self::COOKIE_NAME);
        if ($rawCookie !== '' && str_contains($rawCookie, ':')) {
            [$selector] = explode(':', $rawCookie, 2);
            $this->deleteSelector(trim($selector));
        }

        $this->expireClientCookie();
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->clearAllForUser($userId);
    }

    private function clearAllForUser(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        if (! $this->hasTokenTable()) {
            return;
        }

        $db = db_connect();
        $db->table('user_remember_tokens')->where('user_id', $userId)->delete();
    }

    private function deleteSelector(string $selector): void
    {
        if ($selector === '') {
            return;
        }

        if (! $this->hasTokenTable()) {
            return;
        }

        $db = db_connect();
        $db->table('user_remember_tokens')->where('selector', $selector)->delete();
    }

    private function deleteCurrentSelector(): void
    {
        $rawCookie = (string) service('request')->getCookie(self::COOKIE_NAME);
        if ($rawCookie === '' || ! str_contains($rawCookie, ':')) {
            return;
        }

        [$selector] = explode(':', $rawCookie, 2);
        $this->deleteSelector(trim($selector));
    }

    private function writeClientCookie(string $value): void
    {
        service('response')->setCookie(
            self::COOKIE_NAME,
            $value,
            self::LIFETIME_SECONDS,
            '',
            '/',
            '',
            null,
            true,
            'Lax'
        );
    }

    private function expireClientCookie(): void
    {
        service('response')->deleteCookie(self::COOKIE_NAME);
    }

    private function hasTokenTable(): bool
    {
        if (self::$tokenTableExists !== null) {
            return self::$tokenTableExists;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            self::$tokenTableExists = false;

            return false;
        }

        try {
            self::$tokenTableExists = db_connect()->tableExists('user_remember_tokens');
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Remember login table check failed');
            self::$tokenTableExists = false;
        }

        return self::$tokenTableExists;
    }
}
