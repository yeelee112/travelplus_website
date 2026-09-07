<?php

use App\Services\ChatHistoryService;
use CodeIgniter\Test\CIUnitTestCase;

final class ChatHistoryServiceTest extends CIUnitTestCase
{
    public function testGroupsRotatedLogsAndSearchKeepsBothSides(): void
    {
        $directory = sys_get_temp_dir() . '/chat-history-' . bin2hex(random_bytes(6));
        mkdir($directory);
        try {
            $user = ['timestamp' => '2026-09-07T10:00:00+07:00', 'role' => 'user',
                'session_id' => 'legacy-secret', 'message' => 'Nha Trang test@example.com 0901234567'];
            $reply = array_merge($user, ['timestamp' => '2026-09-07T10:00:03+07:00',
                'role' => 'assistant', 'message' => '<script>alert(1)</script> Tour details']);
            file_put_contents($directory . '/2026-09-07.jsonl', json_encode($user) . "\ninvalid\n");
            file_put_contents($directory . '/2026-09-07-2.jsonl', json_encode($reply) . "\n");
            $service = new ChatHistoryService($directory);
            $report = $service->search('../../secret', 'Nha Trang');
            $this->assertSame('2026-09-07', $report['date']);
            $this->assertSame(1, $report['total']);
            $this->assertCount(2, $report['conversation']['messages']);
            $this->assertStringContainsString('[email] [phone]', $report['conversation']['messages'][0]['message']);
            $this->assertStringNotContainsString('legacy-secret', json_encode($report));
            $this->assertSame(0, $service->search('', 'missing')['total']);
            $this->assertNull($service->search('', '', 'unknown')['conversation']);
        } finally {
            foreach (glob($directory . '/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }

    public function testEmptyDirectory(): void
    {
        $report = (new ChatHistoryService(__DIR__ . '/missing-chat-directory'))->search('');
        $this->assertSame(0, $report['total']);
        $this->assertNull($report['conversation']);
    }
}
