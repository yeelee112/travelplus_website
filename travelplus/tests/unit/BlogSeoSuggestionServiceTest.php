<?php
use App\Services\BlogSeoSuggestionService;
use CodeIgniter\Test\CIUnitTestCase;

final class BlogSeoSuggestionServiceTest extends CIUnitTestCase
{
    public function testNormalizesValidProviderResult(): void
    {
        $result = BlogSeoSuggestionService::validateSuggestion([
            'focus_keyword' => '  du lịch Nhật Bản ', 'secondary_keywords' => ['lá đỏ', 'Lá đỏ', 'Kyoto'],
            'social_hashtags' => ['#TravelPlus', '#travelplus', '#NhatBan'],
            'meta_title' => '<b>Du lịch Nhật Bản</b>', 'meta_description' => 'Khám phá Kyoto mùa lá đỏ.',
        ]);
        $this->assertSame('du lịch Nhật Bản', $result['focus_keyword']);
        $this->assertSame('lá đỏ, Kyoto', $result['secondary_keywords']);
        $this->assertSame('#TravelPlus #NhatBan', $result['social_hashtags']);
        $this->assertSame('Du lịch Nhật Bản', $result['meta_title']);
    }

    public function testRejectsMalformedProviderOutput(): void
    {
        $this->expectException(RuntimeException::class);
        BlogSeoSuggestionService::validateSuggestion(['focus_keyword' => ['unexpected']]);
    }
}
