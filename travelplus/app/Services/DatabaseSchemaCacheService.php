<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Database\BaseConnection;
use Throwable;

final class DatabaseSchemaCacheService
{
    private const READY_TTL = 3600;
    private const MISSING_TTL = 300;

    private BaseConnection $db;
    private CacheInterface $cache;

    /** @var array<string, bool> */
    private static array $tableCache = [];

    /** @var array<string, bool> */
    private static array $fieldCache = [];

    public function __construct(?BaseConnection $db = null, ?CacheInterface $cache = null)
    {
        $this->db = $db ?? db_connect();
        $this->cache = $cache ?? cache();
    }

    public function tableExists(string $table): bool
    {
        $memoryKey = $this->databaseKey() . ':table:' . $table;
        if (array_key_exists($memoryKey, self::$tableCache)) {
            return self::$tableCache[$memoryKey];
        }

        return self::$tableCache[$memoryKey] = $this->remember(
            $memoryKey,
            fn (): bool => $this->db->tableExists($table)
        );
    }

    public function fieldExists(string $field, string $table): bool
    {
        $memoryKey = $this->databaseKey() . ':field:' . $table . ':' . $field;
        if (array_key_exists($memoryKey, self::$fieldCache)) {
            return self::$fieldCache[$memoryKey];
        }

        return self::$fieldCache[$memoryKey] = $this->remember(
            $memoryKey,
            fn (): bool => $this->db->fieldExists($field, $table)
        );
    }

    private function remember(string $key, callable $resolver): bool
    {
        $cacheKey = 'db_schema_' . sha1($key);

        try {
            $cached = $this->cache->get($cacheKey);
            if ($cached === 1 || $cached === '1') {
                return true;
            }
            if ($cached === 0 || $cached === '0') {
                return false;
            }
        } catch (Throwable) {
        }

        try {
            $exists = (bool) $resolver();
            try {
                $this->cache->save(
                    $cacheKey,
                    $exists ? 1 : 0,
                    $exists ? self::READY_TTL : self::MISSING_TTL
                );
            } catch (Throwable) {
            }

            return $exists;
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Database schema check failed');

            return false;
        }
    }

    private function databaseKey(): string
    {
        return (string) $this->db->getDatabase();
    }
}
