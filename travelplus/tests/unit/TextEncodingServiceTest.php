<?php

use App\Services\TextEncodingService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TextEncodingServiceTest extends CIUnitTestCase
{
    public function testRepairMojibakeVietnameseText(): void
    {
        $original = 'Năm 1985, Petra được UNESCO công nhận là Di sản Thế giới.';
        $mojibake = mb_convert_encoding($original, 'UTF-8', 'Windows-1252');

        $this->assertSame($original, TextEncodingService::repair($mojibake));
    }

    public function testRepairHtmlMojibakeWithNumericEntity(): void
    {
        $html = '<p>Petra Ä&#x91;Æ°á»£c UNESCO cÃ´ng nháº­n lÃ  <strong>Di sản Thế giới</strong>.</p>';

        $this->assertSame(
            '<p>Petra được UNESCO công nhận là <strong>Di sản Thế giới</strong>.</p>',
            TextEncodingService::repairHtml($html)
        );
    }
}
