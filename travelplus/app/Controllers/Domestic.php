<?php

namespace App\Controllers;

use App\Services\DomesticRegionService;
use App\Services\TourCatalogService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Domestic extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale();
        $tourService = new TourCatalogService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'inbound');

        $data['breadcrumbs'] = [
            ['label' => 'Trang chu', 'url' => localized_url('/')],
            ['label' => $locale === 'en' ? 'Domestic Tours' : 'Tour trong nuoc'],
        ];
        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
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
        $tourService = new TourCatalogService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($locale, 9, $page, 'inbound', $filter);

        $data['breadcrumbs'] = $this->buildBreadcrumbs($locale, $locations);
        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];

        return view('tour-trong-nuoc/index', $data);
    }

    private function buildBreadcrumbs(string $locale, array $locations): array
    {
        $breadcrumbs = [
            ['label' => 'Trang chu', 'url' => localized_url('/')],
            ['label' => $locale === 'en' ? 'Domestic Tours' : 'Tour trong nuoc', 'url' => localized_url('tour-trong-nuoc')],
        ];

        $path = 'tour-trong-nuoc';

        foreach ($locations as $index => $location) {
            $path .= '/' . $location['slug'];
            $isLast = $index === array_key_last($locations);
            $crumb = ['label' => (string) $location['name']];

            if (!$isLast) {
                $crumb['url'] = localized_url($path);
            }

            $breadcrumbs[] = $crumb;
        }

        return $breadcrumbs;
    }
}
