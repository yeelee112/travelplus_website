<?php

use App\Services\TourDocumentImporter;
use PHPUnit\Framework\TestCase;

final class TourDocumentImporterTest extends TestCase
{
    public function testSectionsAndUncertainPrices(): void
    {
        $data = (new TourDocumentImporter())->fromText("CANADA (14N13D)\nNgày 1: Toronto\n<script>alert(1)</script>\nNgày 2: Ottawa\nTham quan\nGIÁ TOUR: VNĐ\n10 khách: 193.500.000\n15 khách: 165.500.000\nGiá tour bao gồm:\nVé máy bay\nGiá tour không bao gồm:\nTiền tip\nPhụ thu trẻ em:\n85%\nLưu ý:\nGiá có thể thay đổi");
        self::assertCount(2, $data['itinerary_days']);
        self::assertSame(14, $data['duration_days']);
        self::assertSame(13, $data['duration_nights']);
        self::assertStringContainsString('&lt;script&gt;', $data['itinerary_days'][0]['description_vi']);
        self::assertSame('Vé máy bay', $data['included_items'][0]['label_vi']);
        self::assertCount(1, $data['excluded_items']);
        self::assertStringContainsString('15 khách: 165.500.000', $data['description_vi']);
        self::assertArrayNotHasKey('base_price', $data);
        self::assertSame('draft', $data['status']);
    }

    public function testWordTextboxFallbackIsNotDuplicated(): void
    {
        $xml = '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"><w:body><w:p><w:r><w:t>Tour</w:t></w:r></w:p><w:p><mc:AlternateContent><mc:Choice><w:txbxContent><w:p><w:r><w:t>Ngày 1: Toronto</w:t></w:r></w:p><w:p><w:r><w:t>Tham quan</w:t></w:r></w:p></w:txbxContent></mc:Choice><mc:Fallback><w:txbxContent><w:p><w:r><w:t>Ngày 1: Toronto</w:t></w:r></w:p><w:p><w:r><w:t>Tham quan</w:t></w:r></w:p></w:txbxContent></mc:Fallback></mc:AlternateContent></w:p></w:body></w:document>';
        $data = (new TourDocumentImporter())->fromXml($xml);
        self::assertCount(1, $data['itinerary_days']);
        self::assertSame('<p>Tham quan</p>', $data['itinerary_days'][0]['description_vi']);
    }

    public function testRejectsEntities(): void
    {
        $this->expectException(RuntimeException::class);
        (new TourDocumentImporter())->fromXml('<!DOCTYPE x [<!ENTITY test SYSTEM "file:///secret">]><x/>');
    }

    public function testWordRunFormattingAndCombinedEditorContent(): void
    {
        $xml = '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
            <w:p><w:r><w:t>Canada</w:t></w:r></w:p>
            <w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Ngày 1: Toronto</w:t></w:r></w:p>
            <w:p><w:r><w:t>Tham quan </w:t></w:r><w:r><w:rPr><w:b/><w:i/></w:rPr><w:t>CN Tower</w:t></w:r><w:r><w:rPr><w:b w:val="0"/><w:i w:val="false"/></w:rPr><w:t> &amp; hồ</w:t><w:br/><w:t>Dòng mới</w:t></w:r></w:p>
            <w:p><w:r><w:rPr><w:u w:val="single"/></w:rPr><w:t>Nghỉ đêm</w:t></w:r></w:p>
            <w:p><w:r><w:t>Ngày 2: Ottawa</w:t></w:r></w:p>
            <w:p><w:r><w:rPr><w:i/></w:rPr><w:t>Ăn sáng</w:t></w:r></w:p>
            <w:p><w:r><w:t>GIÁ TOUR: 100</w:t></w:r></w:p>
        </w:body></w:document>';
        $data = (new TourDocumentImporter())->fromXml($xml);
        self::assertCount(2, $data['itinerary_days']);
        self::assertSame('<p>Tham quan <strong><em>CN Tower</em></strong> &amp; hồ</p><p>Dòng mới</p><p><u>Nghỉ đêm</u></p>', $data['itinerary_days'][0]['description_vi']);
        self::assertStringContainsString('<p><strong>Ngày 1: Toronto</strong></p>', $data['itinerary_import_html']);
        self::assertStringContainsString('<p><em>Ăn sáng</em></p>', $data['itinerary_import_html']);
        self::assertStringNotContainsString('GIÁ TOUR', $data['itinerary_import_html']);
    }

    public function testEmptyInputHasHelpfulError(): void
    {
        $this->expectException(RuntimeException::class);
        (new TourDocumentImporter())->fromText(' ');
    }
}
