<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;
use Throwable;

final class PublicContentCacheService
{
    private const VERSION_KEY = 'travelplus_public_content_version';
    private const VERSION_TTL = 31_536_000;

    private CacheInterface $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache ?? cache();
    }

    public function get(string $key)
    {
        try {
            return $this->cache->get($this->versionedKey($key));
        } catch (Throwable) {
            return null;
        }
    }

    public function save(string $key, $value, int $ttl): bool
    {
        try {
            return $this->cache->save($this->versionedKey($key), $value, max(30, $ttl));
        } catch (Throwable) {
            return false;
        }
    }

    public function invalidate(): void
    {
        try {
            $this->cache->save(self::VERSION_KEY, $this->version() + 1, self::VERSION_TTL);
        } catch (Throwable) {
        }
    }

    public function version(): int
    {
        try {
            return max(1, (int) ($this->cache->get(self::VERSION_KEY) ?: 1));
        } catch (Throwable) {
            return 1;
        }
    }

    private function versionedKey(string $key): string
    {
        return 'travelplus_public_v' . $this->version() . '_' . sha1($key);
    }
}
