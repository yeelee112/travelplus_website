<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;
use App\Services\TourCatalogService;

class SearchController extends BaseController
{
    public function tours()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $query = trim((string) $this->request->getGet('q'));
        $departureFrom = trim((string) $this->request->getGet('departure_from'));
        $departureTo = trim((string) $this->request->getGet('departure_to'));
        $departureDate = trim((string) $this->request->getGet('departure_date'));
        if ($departureDate !== '') {
            if ($departureFrom === '') {
                $departureFrom = $departureDate;
            }
            if ($departureTo === '') {
                $departureTo = $departureDate;
            }
        }
        $tourType = trim((string) $this->request->getGet('tour_type'));
        $tourType = in_array($tourType, ['outbound', 'inbound'], true) ? $tourType : '';
        $promotionOnly = (string) $this->request->getGet('promotion') === '1';
        $page = (int) ($this->request->getGet('page') ?? 1);

        $tourService = new TourCatalogService();
        $result = $tourService->searchTours($locale, $query, $departureFrom, $departureTo, 9, $page, $tourType !== '' ? $tourType : null, $promotionOnly);
        $fallbackTours = [];

        if (((int) ($result['total'] ?? 0)) === 0) {
            $fallback = $tourService->getPagedTours($locale, 999, 1, $tourType !== '' ? $tourType : null, [], $promotionOnly);
            $fallbackTours = $fallback['tours'];
        }

        $alternateParams = array_filter([
            'q' => $query,
            'departure_from' => $departureFrom,
            'departure_to' => $departureTo,
            'tour_type' => $tourType,
            'promotion' => $promotionOnly ? '1' : '',
        ], static fn($value): bool => $value !== '');
        $viSearchUrl = LocalizedPathCatalog::url('search', 'vi') . ($alternateParams !== [] ? '?' . http_build_query($alternateParams) : '');
        $enSearchUrl = LocalizedPathCatalog::url('search', 'en') . ($alternateParams !== [] ? '?' . http_build_query($alternateParams) : '');
        $canonicalUrl = LocalizedPathCatalog::url('search', $locale);
        $metaTitle = $query !== ''
            ? ($t('search.resultsFor', [$query]) . ' | Travel Plus')
            : $t('search.metaTitle');
        $metaDesc = $t('search.metaDesc');
        $breadcrumbs = [
            ['label' => $t('common.home'), 'url' => localized_url('/')],
            ['label' => $t('search.title')],
        ];

        return view('tour-search/index', [
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => $t('search.resultsTitle'),
            'pageSubtitle' => $query !== ''
                ? $t('search.resultsFor', [$query])
                : $t('search.resultsAll'),
            'listingSearch' => [
                'q' => $query,
                'departure_from' => $departureFrom,
                'departure_to' => $departureTo,
                'tour_type' => $tourType,
                'promotion_only' => $promotionOnly,
                'is_search_page' => true,
            ],
            'tours' => $result['tours'],
            'pagination' => [
                'total' => $result['total'],
                'page' => $result['page'],
                'lastPage' => $result['lastPage'],
            ],
            'fallbackTours' => $fallbackTours,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'meta_robots' => 'noindex,follow,max-image-preview:large',
            'canonical_url' => $canonicalUrl,
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => $viSearchUrl],
                ['hreflang' => 'en', 'href' => $enSearchUrl],
                ['hreflang' => 'x-default', 'href' => $viSearchUrl],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, $canonicalUrl),
                $seo->webpageSchema($metaTitle, $metaDesc, $canonicalUrl),
            ],
        ]);
    }
}
