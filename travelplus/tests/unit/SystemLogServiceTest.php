<?php

use App\Services\SystemLogService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SystemLogServiceTest extends CIUnitTestCase
{
    public function testParsesMultilineIssueEntriesAndIgnoresDebugMessages(): void
    {
        $content = <<<'LOG'
DEBUG - 2026-07-16 08:00:00 --> Session initialized.
CRITICAL - 2026-07-16 08:01:00 --> RuntimeException: Booking failed
[Method: POST, Route: booking/proceed]
in APPPATH\Controllers\BookingController.php on line 42.
 1 SYSTEMPATH\CodeIgniter.php(900): run()
WARNING - 2026-07-16 08:02:00 --> Slow request
[Method: GET, Route: tour-nuoc-ngoai]
LOG;

        $entries = SystemLogService::parseContent($content);

        $this->assertCount(2, $entries);
        $this->assertSame('critical', $entries[0]['level']);
        $this->assertSame('POST', $entries[0]['method']);
        $this->assertSame('booking/proceed', $entries[0]['route']);
        $this->assertStringContainsString('BookingController.php', $entries[0]['details']);
        $this->assertSame('warning', $entries[1]['level']);
    }

    public function testRedactsCredentialsAndCustomerContactDetails(): void
    {
        $redacted = SystemLogService::redact(
            'token=abc123 password:secret email customer@example.com phone 0795681568'
        );

        $this->assertStringNotContainsString('abc123', $redacted);
        $this->assertStringNotContainsString('password:secret', $redacted);
        $this->assertStringNotContainsString('customer@example.com', $redacted);
        $this->assertStringNotContainsString('0795681568', $redacted);
        $this->assertStringContainsString('[REDACTED]', $redacted);
        $this->assertStringContainsString('c***@example.com', $redacted);
        $this->assertStringContainsString('[PHONE REDACTED]', $redacted);
    }
}
