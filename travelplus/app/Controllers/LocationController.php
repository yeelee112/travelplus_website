<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Models\LocationModel;
use App\Services\SeoService;
use App\Services\TourCatalogService;
use CodeIgniter\Exceptions\PageNotFoundException;

class LocationController extends BaseController
{
    private const LOCATION_TITLE_TEMPLATES = [
        'vi' => 'Tour %s | Travel Plus',
        'en' => '%s Tours | Travel Plus',
    ];

    public function continent($locale, $continentSlug)
    {
        $locationModel = new LocationModel();
        $continent = $locationModel->findTranslatedLocationBySlug($locale, $continentSlug, 'continent');

        if ($continent === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderLocationTourList($locale, [$continent], $continent);
    }

    public function country($locale, $continentSlug, $countrySlug)
    {
        $locationModel = new LocationModel();
        $continent = $locationModel->findTranslatedLocationBySlug($locale, $continentSlug, 'continent');

        if ($continent === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $country = $locationModel->findTranslatedLocationBySlug($locale, $countrySlug, 'country', (int) $continent['id']);

        if ($country === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderLocationTourList($locale, [$continent, $country], $country);
    }

    public function province($locale, $continentSlug, $countrySlug, $provinceSlug)
    {
        $locationModel = new LocationModel();
        $continent = $locationModel->findTranslatedLocationBySlug($locale, $continentSlug, 'continent');

        if ($continent === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $country = $locationModel->findTranslatedLocationBySlug($locale, $countrySlug, 'country', (int) $continent['id']);

        if ($country === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $province = $locationModel->findTranslatedLocationBySlug($locale, $provinceSlug, 'province', (int) $country['id']);

        if ($province === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderLocationTourList($locale, [$continent, $country, $province], $province);
    }

    private function renderLocationTourList(string $locale, array $locations, array $activeLocation): string
    {
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $tourService = new TourCatalogService();
        $seo = new SeoService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours(
            $locale,
            9,
            $page,
            'outbound',
            [
                'id' => (int) $activeLocation['id'],
                'type' => (string) $activeLocation['type'],
            ]
        );

        $data['breadcrumbs'] = $this->buildBreadcrumbs($locale, $locations);
        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];
        $titleTemplate = self::LOCATION_TITLE_TEMPLATES[$locale] ?? self::LOCATION_TITLE_TEMPLATES['vi'];
        $data['meta_title'] = sprintf($titleTemplate, (string) $activeLocation['name']);
        $data['meta_desc'] = $t('location.metaDesc', [(string) $activeLocation['name']]);
        $data['canonical_url'] = current_url();
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => switch_locale_url('vi')],
            ['hreflang' => 'en', 'href' => switch_locale_url('en')],
            ['hreflang' => 'x-default', 'href' => switch_locale_url('vi')],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
        ];

        return view('tour-nuoc-ngoai/index', $data);
    }

    private function buildBreadcrumbs(string $locale, array $locations): array
    {
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $breadcrumbs = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $t('common.outboundTours'),
                'url' => LocalizedPathCatalog::url('outbound', $locale),
            ],
        ];

        $path = '';

        foreach ($locations as $index => $location) {
            $path .= ($path === '' ? '' : '/') . $location['slug'];
            $isLast = $index === array_key_last($locations);

            $breadcrumbs[] = [
                'label' => (string) $location['name'],
                'url' => $isLast ? null : localized_url($path),
            ];

            if ($isLast) {
                unset($breadcrumbs[array_key_last($breadcrumbs)]['url']);
            }
        }

        return $breadcrumbs;
    }
}
