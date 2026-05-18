<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;
use App\Services\TourCatalogService;

class Outbound extends BaseController
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
                'label' => $t('common.outboundTours'),
            ],
        ];

        $tourService = new TourCatalogService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'outbound');

        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];
        $data['meta_title'] = $t('outbound.metaTitle');
        $data['meta_desc'] = $t('outbound.metaDesc');
        $data['canonical_url'] = LocalizedPathCatalog::url('outbound', $locale);
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => base_url('tour-nuoc-ngoai')],
            ['hreflang' => 'en', 'href' => base_url('en/tour-nuoc-ngoai')],
            ['hreflang' => 'x-default', 'href' => base_url('tour-nuoc-ngoai')],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
        ];

        return view('tour-nuoc-ngoai/index', $data);
    }
}
