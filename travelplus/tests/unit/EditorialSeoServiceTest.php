<?php
use App\Services\EditorialSeoService;
use CodeIgniter\Test\CIUnitTestCase;

final class EditorialSeoServiceTest extends CIUnitTestCase
{
    public function testEditorialFieldsNormalizeAndStayLocaleSpecific(): void
    {
        $result = EditorialSeoService::normalize([
            'focus_keyword_vi' => "  tour   mùa thu \n",
            'secondary_keywords_vi' => "Nhật Bản, nhật bản; lá đỏ\nTokyo",
            'social_hashtags_vi' => '#MùaThu #mùathu, TravelPlus; #Tokyo!',
            'focus_keyword_en' => 'Japan tour',
        ], 'vi');
        $this->assertSame('tour mùa thu', $result['focus_keyword']);
        $this->assertSame('Nhật Bản, lá đỏ, Tokyo', $result['secondary_keywords']);
        $this->assertSame('#MùaThu #TravelPlus #Tokyo', $result['social_hashtags']);
    }

    public function testMissingOrMalformedValuesCanBeCleared(): void
    {
        $this->assertSame(array_fill_keys(EditorialSeoService::FIELDS, ''), EditorialSeoService::normalize([
            'focus_keyword_vi' => ['unexpected'], 'social_hashtags_vi' => '### , ;',
        ], 'vi'));
    }
}
