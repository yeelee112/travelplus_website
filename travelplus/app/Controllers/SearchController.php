<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SearchAnalyticsService;
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
        $allowedTourTypes = $locale === 'en' ? ['outbound', 'inbound'] : ['outbound', 'domestic'];
        $tourType = in_array($tourType, $allowedTourTypes, true) ? $tourType : '';
        $excludedTourType = $locale === 'en' ? 'domestic' : 'inbound';
        $promotionOnly = (string) $this->request->getGet('promotion') === '1';
        $destinationId = max(0, (int) $this->request->getGet('destination_id'));
        $collectionSlug = trim((string) $this->request->getGet('collection'));
        if ($collectionSlug === '' && (string) $this->request->getGet('autumn') === '1') $collectionSlug = 'mua-thu';
        $collectionName = '';
        foreach ((new \App\Services\TourCollectionService())->all() as $collection) {
            if ($collection['slug'] === $collectionSlug && !empty($collection['is_active'])) $collectionName = $locale === 'en' ? ($collection['name_en'] ?: $collection['name_vi']) : $collection['name_vi'];
        }
        $page = (int) ($this->request->getGet('page') ?? 1);

        $tourService = new TourCatalogService();
        $result = $tourService->searchTours($locale, $query, $departureFrom, $departureTo, 9, $page, $tourType !== '' ? $tourType : null, $promotionOnly, $excludedTourType, $collectionSlug, $destinationId);
        $fallbackTours = [];

        (new SearchAnalyticsService())->track(
            $this->request,
            $query,
            $departureFrom,
            $departureTo,
            $tourType,
            $promotionOnly,
            (int) ($result['total'] ?? 0),
            session()->get('auth_user')
        );

        if ($destinationId === 0 && $collectionSlug === '' && ((int) ($result['total'] ?? 0)) === 0) {
            $fallback = $tourService->getPagedTours($locale, 999, 1, $tourType !== '' ? $tourType : null, [], $promotionOnly, $excludedTourType);
            $fallbackTours = $fallback['tours'];
        }

        $alternateParams = array_filter([
            'q' => $query,
            'departure_from' => $departureFrom,
            'departure_to' => $departureTo,
            'tour_type' => $tourType,
            'promotion' => $promotionOnly ? '1' : '',
            'destination_id' => $destinationId ?: '',
            'collection' => $collectionSlug,
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
            'pageTitle' => $collectionName !== '' ? $collectionName : $t('search.resultsTitle'),
            'pageSubtitle' => $query !== ''
                ? $t('search.resultsFor', [$query])
                : $t('search.resultsAll'),
            'collectionSlug' => $collectionSlug,
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
