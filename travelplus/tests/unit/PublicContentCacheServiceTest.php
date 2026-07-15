<?php

use App\Services\PublicContentCacheService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicContentCacheServiceTest extends CIUnitTestCase
{
    public function testInvalidationMakesPreviousVersionUnavailable(): void
    {
        $service = new PublicContentCacheService();
        $key = 'test:' . bin2hex(random_bytes(8));
        $value = ['cached' => true];

        $this->assertTrue($service->save($key, $value, 60));
        $this->assertSame($value, $service->get($key));

        $version = $service->version();
        $service->invalidate();

        $this->assertGreaterThan($version, $service->version());
        $this->assertNull($service->get($key));
    }
}
