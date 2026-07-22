<?php

use App\Services\DatabaseSchemaCacheService;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DatabaseSchemaCacheServiceTest extends CIUnitTestCase
{
    public function testRepeatedTableCheckUsesRequestMemoryCache(): void
    {
        $database = 'schema_test_' . bin2hex(random_bytes(6));
        $table = 'table_' . bin2hex(random_bytes(6));
        $db = $this->createMock(BaseConnection::class);
        $db->method('getDatabase')->willReturn($database);
        $db->expects($this->once())->method('tableExists')->with($table)->willReturn(true);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->expects($this->once())
            ->method('save')
            ->with($this->isType('string'), 1, 3600)
            ->willReturn(true);

        $service = new DatabaseSchemaCacheService($db, $cache);

        $this->assertTrue($service->tableExists($table));
        $this->assertTrue($service->tableExists($table));
    }
}
