<?php

use App\Services\RecaptchaService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RecaptchaServiceTest extends CIUnitTestCase
{
    public function testRejectsEmptyTokenWithoutCallingProvider(): void
    {
        $this->assertFalse((new RecaptchaService())->verify('', 'register', '127.0.0.1'));
    }
}
