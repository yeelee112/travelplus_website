<?php

use App\Services\AccountVerificationService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AccountVerificationServiceTest extends CIUnitTestCase
{
    public function testTokenHashIsStableWithoutKeepingPlainToken(): void
    {
        $hash = AccountVerificationService::hashToken('123456');

        $this->assertSame(64, strlen($hash));
        $this->assertSame(hash('sha256', '123456'), $hash);
        $this->assertNotSame('123456', $hash);
    }

    public function testMasksVerificationRecipients(): void
    {
        $this->assertSame('an***@travelplusvn.com', AccountVerificationService::maskEmail('an@travelplusvn.com'));
        $this->assertSame('079 *** 568', AccountVerificationService::maskPhone('0795681568'));
    }
}
