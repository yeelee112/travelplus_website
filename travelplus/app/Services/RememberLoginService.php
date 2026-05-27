<?php

namespace App\Services;

use App\Models\UserModel;

class RememberLoginService
{
    private const COOKIE_NAME = 'travelplus_remember';
    private const LIFETIME_SECONDS = 2592000; // 30 days

    public function issue(array $user): void
    {
        $db = db_connect();
        if (! $db->tableExists('user_remember_tokens')) {
            return;
        }

        $this->clearAllForUser((int) ($user['id'] ?? 0));

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

        service('response')->setCookie(
            self::COOKIE_NAME,
            $selector . ':' . $validator,
            self::LIFETIME_SECONDS,
            '',
            '/',
            '',
            null,
            true,
            'Lax'
        );
    }

    public function restoreUser(): ?array
    {
        $db = db_connect();
        if (! $db->tableExists('user_remember_tokens')) {
            return null;
        }

        $rawCookie = (string) service('request')->getCookie(self::COOKIE_NAME);
        if ($rawCookie === '' || ! str_contains($rawCookie, ':')) {
            return null;
        }

        [$selector, $validator] = explode(':', $rawCookie, 2);
        $selector = trim($selector);
        $validator = trim($validator);

        if ($selector === '' || $validator === '') {
            $this->clear();
            return null;
        }

        $row = $db->table('user_remember_tokens')
            ->where('selector', $selector)
            ->get()
            ->getRowArray();

        if (! is_array($row)) {
            $this->clear();
            return null;
        }

        if (strtotime((string) ($row['expires_at'] ?? '')) < time()) {
            $this->deleteSelector($selector);
            $this->clear();
            return null;
        }

        if (! hash_equals((string) ($row['token_hash'] ?? ''), hash('sha256', $validator))) {
            $this->deleteSelector($selector);
            $this->clear();
            return null;
        }

        $user = (new UserModel())
            ->where('id', (int) $row['user_id'])
            ->where('status', 'active')
            ->first();

        if (! is_array($user)) {
            $this->deleteSelector($selector);
            $this->clear();
            return null;
        }

        $newValidator = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME_SECONDS);

        $db->table('user_remember_tokens')
            ->where('id', (int) $row['id'])
            ->update([
                'token_hash' => hash('sha256', $newValidator),
                'expires_at' => $expiresAt,
                'last_used_at' => $now,
                'updated_at' => $now,
            ]);

        service('response')->setCookie(
            self::COOKIE_NAME,
            $selector . ':' . $newValidator,
            self::LIFETIME_SECONDS,
            '',
            '/',
            '',
            null,
            true,
            'Lax'
        );

        return $user;
    }

    public function clear(): void
    {
        $rawCookie = (string) service('request')->getCookie(self::COOKIE_NAME);
        if ($rawCookie !== '' && str_contains($rawCookie, ':')) {
            [$selector] = explode(':', $rawCookie, 2);
            $this->deleteSelector(trim($selector));
        }

        service('response')->deleteCookie(self::COOKIE_NAME);
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

        $db = db_connect();
        if (! $db->tableExists('user_remember_tokens')) {
            return;
        }

        $db->table('user_remember_tokens')->where('user_id', $userId)->delete();
    }

    private function deleteSelector(string $selector): void
    {
        if ($selector === '') {
            return;
        }

        $db = db_connect();
        if (! $db->tableExists('user_remember_tokens')) {
            return;
        }

        $db->table('user_remember_tokens')->where('selector', $selector)->delete();
    }
}
