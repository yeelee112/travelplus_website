<?php

use App\Services\WebsiteSettingsService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class WebsiteSettingsServiceTest extends CIUnitTestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = WRITEPATH . 'tests-website-settings-' . bin2hex(random_bytes(4)) . '.json';
        WebsiteSettingsService::resetMemoryCache();
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        WebsiteSettingsService::resetMemoryCache();
        parent::tearDown();
    }

    public function testUsesDefaultsWhenSettingsFileDoesNotExist(): void
    {
        $service = new WebsiteSettingsService($this->path);

        $this->assertSame('+84795681568', $service->get('hotline_e164'));
        $this->assertSame('079 568 1 568', $service->phoneDisplay('vi'));
        $this->assertSame('(+84) 79 568 1 568', $service->phoneDisplay('en'));
    }

    public function testSavesAndReloadsAllowedSettingsOnly(): void
    {
        $service = new WebsiteSettingsService($this->path);
        $saved = $service->save([
            'email' => 'support@example.com',
            'facebook_url' => 'https://facebook.com/example',
            'unknown_secret' => 'must-not-be-saved',
        ]);

        WebsiteSettingsService::resetMemoryCache();
        $reloaded = new WebsiteSettingsService($this->path);
        $fileContent = (string) file_get_contents($this->path);

        $this->assertTrue($saved);
        $this->assertSame('support@example.com', $reloaded->get('email'));
        $this->assertSame('https://facebook.com/example', $reloaded->get('facebook_url'));
        $this->assertStringNotContainsString('unknown_secret', $fileContent);
        $this->assertStringNotContainsString('must-not-be-saved', $fileContent);
    }
}
