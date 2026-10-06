<?php

use App\Services\ChatPageContextService;
use App\Services\GeminiWebsiteChatService;
use App\Controllers\Api\ChatController;
use CodeIgniter\Test\CIUnitTestCase;

final class ChatPageContextTest extends CIUnitTestCase
{
    public function testOnlyLocalTourRoutesAreAccepted(): void
    {
        $service = new ChatPageContextService();
        $path = '/tour-nuoc-ngoai/chau-au/kham-pha-tay-au-phap-thuy-si-y-10n9d';
        $this->assertSame('outbound', $service->parseTourRoute('https://travelplusvn.com' . $path . '?x=1#info')['type']);
        $this->assertSame([], $service->parseTourRoute('https://evil.example' . $path));
        $this->assertSame([], $service->parseTourRoute('/admin/tours'));
        $this->assertSame([], $service->resolve('vi', '/tour-nuoc-ngoai/chau-au/nonexistent-tour-chat-test'));
    }

    public function testVisaUsesVerifiedPageOnlyWhenDestinationIsMissing(): void
    {
        $service = new GeminiWebsiteChatService();
        $method = new ReflectionMethod($service, 'buildVisaConsultationResponse');
        $page = ['title' => 'Tây Âu: Pháp – Thụy Sĩ – Ý', 'type' => 'outbound', 'url' => 'https://travelplusvn.com/tour'];
        $reply = $method->invoke($service, 'vi', 'Travel Plus có hỗ trợ visa không?', [], $page);
        $this->assertStringContainsString($page['title'], $reply['message']);
        $this->assertStringNotContainsString('đi không', $reply['message']);
        $this->assertCount(2, $reply['sources']);
        $explicit = $method->invoke($service, 'vi', 'Anh/chị hỗ trợ visa Nhật không?', [], $page);
        $this->assertStringContainsString('Nhật Bản', $explicit['message']);
        $this->assertStringNotContainsString('Tây Âu', $explicit['message']);
        $generic = $method->invoke($service, 'vi', 'Travel Plus có hỗ trợ visa không?', [], []);
        $this->assertStringContainsString('visa nước nào', $generic['message']);
        $history = [['role' => 'user', 'text' => 'tôi cần đi Canada']];
        $this->assertStringContainsString('Canada', $method->invoke($service, 'vi', 'hỗ trợ visa không?', $history, $page)['message']);
    }

    public function testContactInvitationIsNotAppendedTwice(): void
    {
        session()->remove('ai_chat_lead_cta_asked');
        $controller = new ChatController();
        $method = new ReflectionMethod($controller, 'appendLeadCaptureCta');
        $message = 'Anh/chị có thể để lại số điện thoại để được tư vấn.';
        $this->assertSame($message, $method->invoke($controller, 'vi', 'visa', [], $message));
        session()->remove('ai_chat_lead_cta_asked');
    }
}
