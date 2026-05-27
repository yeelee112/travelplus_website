<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\DomesticRegionService;
use App\Services\SeoService;
use App\Services\TourCatalogService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Domestic extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $tourService = new TourCatalogService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'inbound');

        $data['breadcrumbs'] = [
            ['label' => $t('common.home'), 'url' => localized_url('/')],
            ['label' => $t('common.domesticTours')],
        ];
        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];
        $data['meta_title'] = $t('domestic.metaTitle');
        $data['meta_desc'] = $t('domestic.metaDesc');
        $data['canonical_url'] = LocalizedPathCatalog::url('domestic', $locale);
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => base_url('tour-trong-nuoc')],
            ['hreflang' => 'en', 'href' => base_url('en/tour-trong-nuoc')],
            ['hreflang' => 'x-default', 'href' => base_url('tour-trong-nuoc')],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url'], 'CollectionPage'),
            $seo->itemListSchema((string) $data['meta_title'], (string) $data['canonical_url'], $data['tours'], 'Product'),
        ];

        return view('tour-trong-nuoc/index', $data);
    }

    public function region($locale, $regionSlug)
    {
        $regionService = new DomesticRegionService();
        $region = $regionService->getRegionBySlug($locale, $regionSlug);

        if ($region === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderDomesticLocationList($locale, [$region], [
            'type' => 'region',
            'ids' => array_map(static fn(array $province): int => (int) $province['id'], $region['provinces']),
        ]);
    }

    public function province($locale, $regionSlug, $provinceSlug)
    {
        $regionService = new DomesticRegionService();
        $region = $regionService->getRegionBySlug($locale, $regionSlug);
        $province = $regionService->getProvinceBySlug($locale, $regionSlug, $provinceSlug);

        if ($region === null || $province === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderDomesticLocationList($locale, [$region, $province], [
            'type' => 'province',
            'id' => (int) $province['id'],
        ]);
    }

    private function renderDomesticLocationList(string $locale, array $locations, array $filter): string
    {
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $tourService = new TourCatalogService();
        $seo = new SeoService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'inbound', $filter);

        $data['breadcrumbs'] = $this->buildBreadcrumbs($locale, $locations);
        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];
        $activeLocation = $locations[array_key_last($locations)] ?? ['name' => ''];
        $data['meta_title'] = $locale === 'en'
            ? ((string) ($activeLocation['name'] ?? '') . ' Tours | Travel Plus')
            : ('Tour ' . (string) ($activeLocation['name'] ?? '') . ' | Travel Plus');
        $data['meta_desc'] = $t('domestic.locationMetaDesc', [(string) ($activeLocation['name'] ?? '')]);
        $data['canonical_url'] = current_url();
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => switch_locale_url('vi')],
            ['hreflang' => 'en', 'href' => switch_locale_url('en')],
            ['hreflang' => 'x-default', 'href' => switch_locale_url('vi')],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url'], 'CollectionPage'),
            $seo->itemListSchema((string) $data['meta_title'], (string) $data['canonical_url'], $data['tours'], 'Product'),
        ];

        return view('tour-trong-nuoc/index', $data);
    }

    private function buildBreadcrumbs(string $locale, array $locations): array
    {
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $breadcrumbs = [
            ['label' => $t('common.home'), 'url' => localized_url('/')],
            ['label' => $t('common.domesticTours'), 'url' => LocalizedPathCatalog::url('domestic', $locale)],
        ];

        $path = 'tour-trong-nuoc';

        foreach ($locations as $index => $location) {
            $path .= '/' . $location['slug'];
            $isLast = $index === array_key_last($locations);
            $crumb = ['label' => (string) $location['name']];

            if (! $isLast) {
                $crumb['url'] = localized_url($path);
            }

            $breadcrumbs[] = $crumb;
        }

        return $breadcrumbs;
    }
}
