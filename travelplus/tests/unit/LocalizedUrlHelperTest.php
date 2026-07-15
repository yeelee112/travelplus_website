<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LocalizedUrlHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('url_helper_custom');
    }

    public function testBuildsVietnameseUrlWithoutLocalePrefix(): void
    {
        $path = (string) parse_url(localized_url_for('cam-hung-du-lich/bai-viet', 'vi'), PHP_URL_PATH);

        $this->assertStringEndsWith('/cam-hung-du-lich/bai-viet', $path);
        $this->assertStringNotContainsString('/en/', $path);
    }

    public function testBuildsEnglishUrlWithLocalePrefix(): void
    {
        $path = (string) parse_url(localized_url_for('travel-inspiration/article', 'en'), PHP_URL_PATH);

        $this->assertStringEndsWith('/en/travel-inspiration/article', $path);
    }

    public function testBuildsEnglishHomeWithoutTrailingDoubleSlash(): void
    {
        $path = (string) parse_url(localized_url_for('', 'en'), PHP_URL_PATH);

        $this->assertStringEndsWith('/en', rtrim($path, '/'));
    }
}
