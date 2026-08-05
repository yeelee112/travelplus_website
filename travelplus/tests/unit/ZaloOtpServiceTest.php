<?php

use App\Services\ZaloOtpService;
use App\Services\ZaloOaTokenService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Zalo;

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

    public function testReportsWhetherOtpConfigurationIsReady(): void
    {
        $config = new Zalo();
        $config->otpEnabled = true;
        $config->otpTemplateId = '617290';
        $config->otpField = 'otp';
        $config->accessToken = 'legacy-token-for-readiness-check';
        $tokens = new ZaloOaTokenService($config, $this->createMock(BaseConnection::class));
        $service = new ZaloOtpService($config, $tokens);

        $this->assertSame(['ready' => true, 'reason' => 'ready'], $service->readiness());

        $config->otpEnabled = false;
        $this->assertSame(['ready' => false, 'reason' => 'otp_disabled'], $service->readiness());
    }
}
