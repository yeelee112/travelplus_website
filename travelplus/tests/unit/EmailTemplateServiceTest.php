<?php

use App\Services\EmailTemplateService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EmailTemplateServiceTest extends CIUnitTestCase
{
    public function testRendersResponsiveMobileEmailStructure(): void
    {
        $html = (new EmailTemplateService())->render(
            'Booking update',
            'Payment confirmed',
            'Your booking has been updated.',
            [
                'Booking code' => 'BK260716ABC123',
                'Customer' => 'Travel Plus Customer',
                'Departure' => '31/07/2026',
            ],
            [
                ['label' => 'Amount paid', 'value' => '110.690.000 VND'],
                ['label' => 'Tour', 'value' => 'East and West Coast of the United States'],
            ],
            'Keep this booking code for support.',
            'View booking',
            'https://example.com/booking/lookup'
        );

        $this->assertStringContainsString('@media only screen and (max-width: 600px)', $html);
        $this->assertStringContainsString('x-apple-disable-message-reformatting', $html);
        $this->assertStringContainsString('class="email-detail-cell"', $html);
        $this->assertStringContainsString('class="email-detail-spacer"', $html);
        $this->assertStringContainsString('class="email-row-label"', $html);
        $this->assertStringContainsString('class="email-row-value"', $html);
        $this->assertStringContainsString('class="email-promo-image"', $html);
        $this->assertStringContainsString('class="email-cta-table"', $html);
        $this->assertStringContainsString('class="email-cta"', $html);
        $this->assertStringContainsString('.email-promo-image { display: none !important;', $html);
        $this->assertStringContainsString('.email-detail-cell { display: block !important; width: 100% !important;', $html);
    }

    public function testEscapesCustomerContentInResponsiveTemplate(): void
    {
        $html = (new EmailTemplateService())->render(
            'Update',
            '<Payment>',
            'Hello <script>alert(1)</script>',
            ['Customer' => '<b>Name</b>'],
            [['label' => 'Note', 'value' => '<img src=x onerror=alert(1)>']]
        );

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;Payment&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;Name&lt;/b&gt;', $html);
    }
}
