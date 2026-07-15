<?php

use App\Services\AnalyticsTrackingService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AnalyticsTrackingServiceTest extends CIUnitTestCase
{
    public function testAutomatedClientsAreDetectedBeforeTracking(): void
    {
        $method = new ReflectionMethod(AnalyticsTrackingService::class, 'isAutomatedClient');
        $method->setAccessible(true);
        $service = new AnalyticsTrackingService();

        foreach ([
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; GPTBot/1.2; +https://openai.com/gptbot)',
            'facebookexternalhit/1.1',
            'Mozilla/5.0 AppleWebKit/537.36 HeadlessChrome/126.0 Safari/537.36',
        ] as $userAgent) {
            $this->assertTrue($method->invoke($service, $userAgent), $userAgent);
        }
    }

    public function testNormalBrowserIsNotClassifiedAsAutomated(): void
    {
        $method = new ReflectionMethod(AnalyticsTrackingService::class, 'isAutomatedClient');
        $method->setAccessible(true);
        $service = new AnalyticsTrackingService();

        $this->assertFalse($method->invoke(
            $service,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126.0 Safari/537.36'
        ));
        $this->assertFalse($method->invoke($service, ''));
    }
}
