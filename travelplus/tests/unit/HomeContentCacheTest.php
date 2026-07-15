<?php

use App\Controllers\Home;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HomeContentCacheTest extends CIUnitTestCase
{
    public function testSafeSectionReusesCachedResult(): void
    {
        $controller = new Home();
        $method = new ReflectionMethod($controller, 'safeSection');
        $method->setAccessible(true);
        $calls = 0;
        $callback = static function () use (&$calls): array {
            $calls++;

            return [['id' => 123]];
        };
        $cacheKey = 'test:home-section:' . bin2hex(random_bytes(8));

        $first = $method->invoke($controller, 'test section', $callback, [], false, $cacheKey, 60);
        $second = $method->invoke($controller, 'test section', $callback, [], false, $cacheKey, 60);

        $this->assertSame([['id' => 123]], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
    }
}
