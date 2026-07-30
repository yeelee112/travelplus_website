<?php

use App\Services\ZaloOtpService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ZaloOtpServiceTest extends CIUnitTestCase
{
    public function testConvertsVietnamesePhoneToZaloInternationalFormat(): void
    {
        $this->assertSame('84795681568', ZaloOtpService::toInternationalPhone('079 568 1568'));
        $this->assertSame('84795681568', ZaloOtpService::toInternationalPhone('+84 79 568 1568'));
    }

    public function testRejectsInvalidPhoneBeforeCallingProvider(): void
    {
        $this->assertSame('', ZaloOtpService::toInternationalPhone('12345'));
        $this->assertSame('', ZaloOtpService::toInternationalPhone(''));
    }
}
