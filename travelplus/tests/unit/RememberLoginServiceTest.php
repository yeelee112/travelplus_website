<?php

use App\Services\RememberLoginService;
use App\Services\DatabaseAvailabilityService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RememberLoginServiceTest extends CIUnitTestCase
{
    private string $userTable;
    private string $tokenTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = db_connect();
        $this->userTable = $this->db->prefixTable('users');
        $this->tokenTable = $this->db->prefixTable('user_remember_tokens');

        $this->db->query('DROP TABLE IF EXISTS ' . $this->tokenTable);
        $this->db->query('DROP TABLE IF EXISTS ' . $this->userTable);
        $this->db->query(
            'CREATE TABLE ' . $this->userTable
            . ' (id INTEGER PRIMARY KEY, full_name TEXT, email TEXT, username TEXT, status TEXT)'
        );
        $this->db->query(
            'CREATE TABLE ' . $this->tokenTable
            . ' (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, selector TEXT NOT NULL UNIQUE,'
            . ' token_hash TEXT NOT NULL, user_agent TEXT, expires_at TEXT NOT NULL, last_used_at TEXT,'
            . ' created_at TEXT NOT NULL, updated_at TEXT NOT NULL)'
        );
        $this->db->resetDataCache();

        $this->db->table('users')->insert([
            'id' => 7,
            'full_name' => 'Remembered User',
            'email' => 'remembered@example.com',
            'username' => 'remembered-user',
            'status' => 'active',
        ]);

        $property = new ReflectionProperty(RememberLoginService::class, 'tokenTableExists');
        $property->setAccessible(true);
        $property->setValue(null, null);

        $availability = new ReflectionProperty(DatabaseAvailabilityService::class, 'unavailable');
        $availability->setAccessible(true);
        $availability->setValue(null, false);
        cache()->delete('database_unavailable_until');
    }

    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE IF EXISTS ' . $this->tokenTable);
        $this->db->query('DROP TABLE IF EXISTS ' . $this->userTable);

        parent::tearDown();
    }

    public function testSameRememberCookieCanRestoreMoreThanOneFreshSession(): void
    {
        $selector = 'selector123456789';
        $validator = str_repeat('a1', 32);
        $tokenHash = hash('sha256', $validator);
        $now = date('Y-m-d H:i:s');

        $this->db->table('user_remember_tokens')->insert([
            'user_id' => 7,
            'selector' => $selector,
            'token_hash' => $tokenHash,
            'user_agent' => 'PHPUnit',
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'last_used_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        service('request')->setGlobal('cookie', [
            'travelplus_remember' => $selector . ':' . $validator,
        ]);

        $service = new RememberLoginService();
        $firstUser = $service->restoreUser();
        $secondUser = $service->restoreUser();
        $storedToken = $this->db->table('user_remember_tokens')
            ->where('selector', $selector)
            ->get()
            ->getRowArray();

        $this->assertSame(7, (int) ($firstUser['id'] ?? 0));
        $this->assertSame(7, (int) ($secondUser['id'] ?? 0));
        $this->assertIsArray($storedToken);
        $this->assertSame($tokenHash, $storedToken['token_hash']);
        $this->assertNotEmpty($storedToken['last_used_at']);
    }

    public function testInvalidValidatorDoesNotDeleteTheValidServerToken(): void
    {
        $selector = 'selector987654321';
        $validValidator = str_repeat('b2', 32);
        $now = date('Y-m-d H:i:s');

        $this->db->table('user_remember_tokens')->insert([
            'user_id' => 7,
            'selector' => $selector,
            'token_hash' => hash('sha256', $validValidator),
            'user_agent' => 'PHPUnit',
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'last_used_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        service('request')->setGlobal('cookie', [
            'travelplus_remember' => $selector . ':' . str_repeat('c3', 32),
        ]);

        $this->assertNull((new RememberLoginService())->restoreUser());
        $this->assertSame(
            1,
            $this->db->table('user_remember_tokens')->where('selector', $selector)->countAllResults()
        );
    }

    public function testSessionRegenerationKeepsThePreviousSessionForConcurrentRequests(): void
    {
        $this->assertFalse(config(\Config\Session::class)->regenerateDestroy);
    }
}
