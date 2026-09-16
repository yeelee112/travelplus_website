<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Models\LocationModel;
use App\Services\DomesticRegionService;
use App\Services\SeoService;
use App\Services\TourCatalogService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Inbound extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        if ($locale !== 'en') {
            return redirect()->to(switch_locale_url('en'))->setStatusCode(302);
        }

        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $tourService = new TourCatalogService();
        $seo = new SeoService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'inbound');
        $featuredInboundTours = [];
        foreach (array_merge(
            $tourService->getFeaturedTours($locale, 3, 'inbound'),
            $tourService->getHomeTours($locale, 9, 'inbound')
        ) as $tour) {
            if (($tour['tour_type'] ?? '') !== 'inbound') {
                continue;
            }
            $tourKey = (string) ($tour['id'] ?? $tour['link'] ?? '');
            $featuredInboundTours[$tourKey] ??= $tour;
            if (count($featuredInboundTours) === 3) {
                break;
            }
        }
        $formToken = bin2hex(random_bytes(16));
        session()->set('contact_form_token', $formToken);

        $breadcrumbs = [
            ['label' => $t('common.home'), 'url' => localized_url('/')],
            ['label' => $t('common.inboundTours')],
        ];
        $canonicalUrl = LocalizedPathCatalog::url('inbound', $locale);

        return view('inbound/index', [
            'breadcrumbs' => $breadcrumbs,
            'tours' => $result['tours'],
            'featuredInboundTours' => array_values($featuredInboundTours),
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'lastPage' => $result['lastPage'],
            ],
            'listingSearch' => ['tour_type' => 'inbound'],
            'page_heading' => 'Private Journeys Across Vietnam & Indochina',
            'contact_form_token' => $formToken,
            'meta_title' => $t('inbound.metaTitle'),
            'meta_desc' => $t('inbound.metaDesc'),
            'canonical_url' => $canonicalUrl,
            'alternate_links' => [
                ['hreflang' => 'en', 'href' => base_url('en/inbound-tours')],
                ['hreflang' => 'x-default', 'href' => base_url('en/inbound-tours')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, $canonicalUrl),
                $seo->webpageSchema($t('inbound.metaTitle'), $t('inbound.metaDesc'), $canonicalUrl, 'CollectionPage'),
                $seo->itemListSchema($t('inbound.metaTitle'), $canonicalUrl, $result['tours'], 'TouristTrip'),
            ],
        ]);
    }

    public function region(string $locale, string $regionSlug)
    {
        if ($locale !== 'en') {
            return redirect()->to(switch_locale_url('en'))->setStatusCode(302);
        }

        $region = (new DomesticRegionService())->getRegionBySlug($locale, $regionSlug);
        if ($region !== null) {
            return $this->renderLocationList($locale, [$region], [
                'type' => 'region',
                'ids' => array_map(static fn(array $province): int => (int) $province['id'], $region['provinces']),
            ]);
        }

        $locationModel = new LocationModel();
        $location = $locationModel->findTranslatedLocationBySlug($locale, $regionSlug, 'country')
            ?? $locationModel->findTranslatedLocationBySlug($locale, $regionSlug, 'continent');
        if ($location === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderLocationList($locale, [$location], [
            'type' => (string) $location['type'],
            'id' => (int) $location['id'],
        ]);
    }

    public function province(string $locale, string $regionSlug, string $provinceSlug)
    {
        if ($locale !== 'en') {
            return redirect()->to(switch_locale_url('en'))->setStatusCode(302);
        }

        $regionService = new DomesticRegionService();
        $region = $regionService->getRegionBySlug($locale, $regionSlug);
        $province = $regionService->getProvinceBySlug($locale, $regionSlug, $provinceSlug);
        if ($region === null || $province === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderLocationList($locale, [$region, $province], [
            'type' => 'province',
            'id' => (int) $province['id'],
        ]);
    }

    private function renderLocationList(string $locale, array $locations, array $filter): string
    {
        $locale = $locale === 'en' ? 'en' : 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $tourService = new TourCatalogService();
        $seo = new SeoService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'inbound', $filter);
        $activeLocation = $locations[array_key_last($locations)] ?? ['name' => ''];
        $locationName = (string) ($activeLocation['name'] ?? '');
        $breadcrumbs = $this->buildBreadcrumbs($locale, $locations);
        $metaTitle = $locale === 'en'
            ? ($locationName . ' Inbound Tours | Travel Plus')
            : ('Tour inbound ' . $locationName . ' | Travel Plus');
        $metaDesc = $locale === 'en'
            ? ('Explore inbound tours in ' . $locationName . ' designed for international travelers with Travel Plus.')
            : ('Khám phá tour inbound ' . $locationName . ' dành cho khách quốc tế cùng Travel Plus.');
        $canonicalUrl = current_url();

        return view('tour-trong-nuoc/index', [
            'breadcrumbs' => $breadcrumbs,
            'tours' => $result['tours'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'lastPage' => $result['lastPage'],
            ],
            'listingSearch' => ['tour_type' => 'inbound'],
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'canonical_url' => $canonicalUrl,
            'alternate_links' => [
                ['hreflang' => 'en', 'href' => switch_locale_url('en')],
                ['hreflang' => 'x-default', 'href' => switch_locale_url('en')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, $canonicalUrl),
                $seo->webpageSchema($metaTitle, $metaDesc, $canonicalUrl, 'CollectionPage'),
                $seo->itemListSchema($metaTitle, $canonicalUrl, $result['tours'], 'TouristTrip'),
            ],
        ]);
    }

    private function buildBreadcrumbs(string $locale, array $locations): array
    {
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $breadcrumbs = [
            ['label' => $t('common.home'), 'url' => localized_url('/')],
            ['label' => $t('common.inboundTours'), 'url' => LocalizedPathCatalog::url('inbound', $locale)],
        ];
        $path = LocalizedPathCatalog::path('inbound', $locale);

        foreach ($locations as $index => $location) {
            $path .= '/' . $location['slug'];
            $crumb = ['label' => (string) $location['name']];
            if ($index !== array_key_last($locations)) {
                $crumb['url'] = localized_url_for($path, $locale);
            }
            $breadcrumbs[] = $crumb;
        }

        return $breadcrumbs;
    }
}
