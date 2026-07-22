<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class EntityViewService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function incrementOncePerSession(string $table, int $entityId, string $sessionKeyPrefix = ''): void
    {
        if ($entityId <= 0) {
            return;
        }

        $sessionKey = 'viewed_' . ($sessionKeyPrefix !== '' ? $sessionKeyPrefix : $table) . '_' . $entityId;

        if (session()->get($sessionKey)) {
            return;
        }

        $schema = new DatabaseSchemaCacheService($this->db);
        if (! $schema->tableExists($table) || ! $schema->fieldExists('view_count', $table)) {
            return;
        }

        $this->db->table($table)
            ->set('view_count', 'COALESCE(view_count, 0) + 1', false)
            ->where('id', $entityId)
            ->update();

        session()->set($sessionKey, 1);
    }
}
