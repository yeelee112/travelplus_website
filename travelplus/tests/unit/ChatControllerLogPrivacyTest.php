<?php

namespace Tests\Unit;

use App\Controllers\Api\ChatController;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;

final class ChatControllerLogPrivacyTest extends CIUnitTestCase
{
    public function testChatLogMessageRedactsEmailAndVietnamPhone(): void
    {
        $message = 'Lien he test@example.com hoac +84 79 568 1568 de tu van.';
        $redacted = $this->invokePrivate('redactLogMessage', [$message]);

        $this->assertSame('Lien he [email] hoac [phone] de tu van.', $redacted);
    }

    public function testChatLogPageDropsHostAndQueryString(): void
    {
        $path = $this->invokePrivate(
            'normalizeLogPagePath',
            ['https://travelplus.vn/tour-nuoc-ngoai/my-tour?email=test@example.com']
        );

        $this->assertSame('/tour-nuoc-ngoai/my-tour', $path);
    }

    private function invokePrivate(string $methodName, array $arguments): mixed
    {
        $method = new ReflectionMethod(ChatController::class, $methodName);

        return $method->invoke(new ChatController(), ...$arguments);
    }
}
