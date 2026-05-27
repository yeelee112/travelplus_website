<?php

namespace App\Controllers;

use App\Data\AboutPageContent;
use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;

class AboutUs extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();

        $data['breadcrumbs'] = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $t('common.aboutUs'),
            ],
        ];
        $data['meta_title'] = $t('about.metaTitle');
        $data['meta_desc'] = $t('about.metaDesc');
        $data['canonical_url'] = LocalizedPathCatalog::url('about', $locale);
        $data['pageContent'] = AboutPageContent::get($locale);
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => base_url('ve-chung-toi')],
            ['hreflang' => 'en', 'href' => base_url('en/ve-chung-toi')],
            ['hreflang' => 'x-default', 'href' => base_url('ve-chung-toi')],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url'], 'AboutPage'),
        ];

        return view('ve-chung-toi/index', $data);
    }
}
