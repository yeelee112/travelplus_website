<?php

use App\Services\DomesticRegionService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class DomesticMenuLinksTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        (new ReflectionProperty(DomesticRegionService::class, 'menuCache'))->setValue(null, []);
        parent::tearDown();
    }

    public function testCachedPreviewUrlsAreRebuiltForCurrentSite(): void
    {
        helper(['url', 'url_helper_custom']);
        $cached = ['central' => [
            'name' => 'Miền Trung', 'slug' => 'mien-trung',
            'link' => 'http://127.0.0.1:8097/tour-trong-nuoc/mien-trung',
            'provinces' => [[
                'name' => 'Đà Nẵng', 'slug' => 'da-nang',
                'link' => 'http://127.0.0.1:8097/tour-trong-nuoc/mien-trung/da-nang',
            ]],
        ]];
        (new ReflectionProperty(DomesticRegionService::class, 'menuCache'))->setValue(null, ['vi' => $cached, 'en' => $cached]);
        $service = new DomesticRegionService();
        foreach (['vi', 'en'] as $locale) {
            $menu = $service->getMenu($locale);
            $this->assertSame(localized_url_for('tour-trong-nuoc/mien-trung', $locale), $menu['central']['link']);
            $this->assertSame(localized_url_for('tour-trong-nuoc/mien-trung/da-nang', $locale), $menu['central']['provinces'][0]['link']);
            $this->assertStringNotContainsString(':8097', $menu['central']['provinces'][0]['link']);
        }
    }
}
