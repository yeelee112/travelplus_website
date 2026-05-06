<?php

namespace App\Controllers;

use App\Services\TourCatalogService;

class SearchController extends BaseController
{
    public function tours()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $query = trim((string) $this->request->getGet('q'));
        $departureDate = trim((string) $this->request->getGet('departure_date'));
        $page = (int) ($this->request->getGet('page') ?? 1);

        $tourService = new TourCatalogService();
        $result = $tourService->searchTours($locale, $query, $departureDate, 9, $page);
        $fallbackTours = [];

        if (((int) ($result['total'] ?? 0)) === 0) {
            $fallback = $tourService->getPagedTours($locale, 999, 1);
            $fallbackTours = $fallback['tours'];
        }

        return view('tour-search/index', [
            'breadcrumbs' => [
                ['label' => $locale === 'en' ? 'Home' : 'Trang chủ', 'url' => localized_url('/')],
                ['label' => $locale === 'en' ? 'Tour Search' : 'Tìm tour'],
            ],
            'pageTitle' => $locale === 'en' ? 'Tour Search Results' : 'Kết quả tìm tour',
            'pageSubtitle' => $query !== ''
                ? ($locale === 'en' ? 'Results for: ' : 'Kết quả cho: ') . $query
                : ($locale === 'en' ? 'All matching tours' : 'Các tour phù hợp'),
            'tours' => $result['tours'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'lastPage' => $result['lastPage'],
            ],
            'fallbackTours' => $fallbackTours,
        ]);
    }
}
