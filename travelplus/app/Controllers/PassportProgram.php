<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;

final class PassportProgram extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        $canonicalUrl = LocalizedPathCatalog::url('passport.program', $locale);
        $title = $locale === 'en'
            ? 'TravelPlus Passport – Membership tiers and benefits'
            : 'TravelPlus Passport – Hạng thành viên và quyền lợi';
        $description = $locale === 'en'
            ? 'Explore TravelPlus Passport membership tiers, Journey Miles, tier benefits and voucher redemption.'
            : 'Khám phá các hạng TravelPlus Passport, cách tích Dặm Hành Trình, quyền lợi theo hạng và đổi voucher.';
        $seo = new SeoService();
        $breadcrumbs = [
            ['label' => $locale === 'en' ? 'Home' : 'Trang chủ', 'url' => localized_url('/')],
            ['label' => 'TravelPlus Passport'],
        ];

        return view('passport/index', [
            'locale' => $locale,
            'meta_title' => $title,
            'meta_desc' => $description,
            'canonical_url' => $canonicalUrl,
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url(LocalizedPathCatalog::path('passport.program', 'vi'))],
                ['hreflang' => 'en', 'href' => base_url('en/' . LocalizedPathCatalog::path('passport.program', 'en'))],
                ['hreflang' => 'x-default', 'href' => base_url(LocalizedPathCatalog::path('passport.program', 'vi'))],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, $canonicalUrl),
                $seo->webpageSchema($title, $description, $canonicalUrl),
            ],
        ]);
    }
}
