<?php

namespace App\Controllers;

use App\Models\LocationModel;
use App\Services\TourCatalogService;
use CodeIgniter\Exceptions\PageNotFoundException;

class LocationController extends BaseController
{
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

    /**
     * @param array<int, array<string, mixed>> $locations
     * @param array<string, mixed> $activeLocation
     */
    private function renderLocationTourList(string $locale, array $locations, array $activeLocation): string
    {
        $tourService = new TourCatalogService();
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

        $data['breadcrumbs'] = $this->buildBreadcrumbs($locations);
        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];

        return view('tour-nuoc-ngoai/index', $data);
    }

    /**
     * @param array<int, array<string, mixed>> $locations
     * @return array<int, array<string, string>>
     */
    private function buildBreadcrumbs(array $locations): array
    {
        $breadcrumbs = [
            [
                'label' => 'Trang chu',
                'url'   => localized_url('/'),
            ],
            [
                'label' => 'Tour nuoc ngoai',
                'url'   => localized_url('tour-nuoc-ngoai'),
            ],
        ];

        $path = '';

        foreach ($locations as $index => $location) {
            $path .= ($path === '' ? '' : '/') . $location['slug'];
            $isLast = $index === array_key_last($locations);

            $breadcrumbs[] = [
                'label' => (string) $location['name'],
                'url'   => $isLast ? null : localized_url($path),
            ];

            if ($isLast) {
                unset($breadcrumbs[array_key_last($breadcrumbs)]['url']);
            }
        }

        return $breadcrumbs;
    }
}
