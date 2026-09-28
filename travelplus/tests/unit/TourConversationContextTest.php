<?php

use App\Services\WebsiteKnowledgeService;
use App\Services\GeminiWebsiteChatService;
use CodeIgniter\Test\CIUnitTestCase;

final class TourConversationContextTest extends CIUnitTestCase
{
    public function testUnaccentedDestinationAndShortHolidayReply(): void
    {
        $knowledge = new WebsiteKnowledgeService();
        $first = $knowledge->resolveTourRequest('tu van tour Nhat', [], []);
        $this->assertSame('Nhật Bản', $first['context']['destination']);
        $history = [
            ['role' => 'user', 'text' => 'tu van tour Nhat'],
            ['role' => 'assistant', 'text' => 'Tour Nhật khởi hành tháng 10. Mình đi khi nào?'],
            ['role' => 'user', 'text' => 'tour Nhat'],
        ];
        $reply = (new GeminiWebsiteChatService())->answer('vi', 'tết', $history);
        $this->assertSame('holiday_clarification', $reply['debug_meta']['branch']);
        $this->assertStringContainsString('Nhật Bản', $reply['message']);
        $this->assertStringContainsString('Tết Dương lịch hay Tết Nguyên đán', $reply['message']);
        $this->assertStringNotContainsString('Điều khoản', $reply['message']);
        $this->assertSame([], $reply['sources']);
        $next = (new GeminiWebsiteChatService())->answer('vi', '4 người', [], $reply['chat_state']);
        $this->assertSame('4', $next['chat_state']['tour_request']['guests']);
        $this->assertStringNotContainsString('bao nhiêu người', $next['message']);
    }

    public function testTopicChangesAndAssistantTextDoNotSupplyCustomerPreferences(): void
    {
        $knowledge = new WebsiteKnowledgeService();
        $history = [['role' => 'user', 'text' => 'tour Nhật Tết cho 4 người'],
            ['role' => 'assistant', 'text' => 'Tour Nhật tháng 10 cho 20 người']];
        $this->assertSame('4', $knowledge->resolveTourRequest('tết', $history, [])['context']['guests']);
        $changed = $knowledge->resolveTourRequest('tour Đà Nẵng', $history, []);
        $this->assertSame('Đà Nẵng', $changed['context']['destination']);
        $this->assertArrayNotHasKey('holiday', $changed['context']);
        $this->assertSame([], $knowledge->resolveTourRequest('visa Mỹ', $history, [])['context']);
        $this->assertSame([], $knowledge->resolveTourRequest('tour tốt nhất', [], [])['context']);
    }

    public function testCustomerFallbackHidesInternalScoresAndSalesNotes(): void
    {
        $service = new GeminiWebsiteChatService();
        $method = new ReflectionMethod($service, 'buildFactsFallbackMessage');
        $reply = $method->invoke($service, 'vi', ['type' => 'tour_list',
            'tours' => [['title' => 'Nhật Bản', 'fit' => ['score' => 65, 'label' => 'Cần hỏi thêm']]],
            'selected_advisory' => ['summary' => 'Tour này đáng tư vấn.',
                'destination_notes' => ['Nhật Bản dễ tư vấn theo mùa.'], 'strengths' => ['Tham quan Tokyo và Osaka.']]]);
        $this->assertStringNotContainsString('65%', $reply);
        $this->assertStringNotContainsString('đáng tư vấn', $reply);
        $this->assertStringNotContainsString('dễ tư vấn', $reply);
        $this->assertStringContainsString('Tokyo', $reply);
        $direct = new ReflectionMethod($service, 'buildDirectConsultationResponse');
        $this->assertNull($direct->invoke($service, 'vi', 'tour Nhật Bản tu van tour Nhat', []));
    }
}
