<?php

use App\Services\DomesticRegionService;
use App\Models\LocationModel;

function localized_url($path = '')
{
    $locale = service('request')->getLocale();

    if ($locale === 'en') {
        return base_url('en/' . ltrim($path, '/'));
    }

    return base_url(ltrim($path, '/'));
}

function switch_locale_url(string $targetLocale): string
{
    $currentUrl = current_url(true);
    $segments = $currentUrl->getSegments();
    $currentLocale = service('request')->getLocale() ?: 'vi';

    if (isset($segments[0]) && $segments[0] === 'en') {
        $currentLocale = 'en';
        array_shift($segments);
    }

    if ($segments === []) {
        return $targetLocale === 'en' ? base_url('en') : base_url('/');
    }

    $translatedSegments = translate_location_segments($segments, $currentLocale, $targetLocale);
    $path = implode('/', $translatedSegments);

    if ($targetLocale === 'en') {
        return base_url('en/' . ltrim($path, '/'));
    }

    return base_url(ltrim($path, '/'));
}

function translate_location_segments(array $segments, string $fromLocale, string $toLocale): array
{
    if ($fromLocale === $toLocale || $segments === []) {
        return $segments;
    }

    if (($segments[0] ?? '') === 'tour-nuoc-ngoai' && isset($segments[1], $segments[2])) {
        $locationModel = new LocationModel();
        $location = $locationModel->findTranslatedLocationBySlug($fromLocale, $segments[1]);
        $targetLocation = $location !== null
            ? $locationModel->findTranslatedLocationById($toLocale, (int) $location['id'])
            : null;

        return [
            'tour-nuoc-ngoai',
            (string) ($targetLocation['slug'] ?? $segments[1]),
            translate_tour_slug($fromLocale, $toLocale, $segments[2], 'outbound'),
        ];
    }

    if (($segments[0] ?? '') === 'tour-trong-nuoc' && isset($segments[1], $segments[2], $segments[3]) && $segments[2] === 'tour') {
        $domesticRegionService = new DomesticRegionService();
        $translatedRegionPath = $domesticRegionService->translatePathSegments(
            ['tour-trong-nuoc', $segments[1]],
            $fromLocale,
            $toLocale
        );

        return [
            'tour-trong-nuoc',
            (string) ($translatedRegionPath[1] ?? $segments[1]),
            'tour',
            translate_tour_slug($fromLocale, $toLocale, $segments[3], 'inbound'),
        ];
    }

    if (($segments[0] ?? '') === 'tour-trong-nuoc') {
        $domesticRegionService = new DomesticRegionService();

        return $domesticRegionService->translatePathSegments($segments, $fromLocale, $toLocale);
    }

    $locationModel = new LocationModel();
    $translated = [];
    $parentId = null;

    foreach ($segments as $segment) {
        $location = $locationModel->findTranslatedLocationBySlug($fromLocale, $segment, null, $parentId);

        if ($location === null) {
            return $segments;
        }

        $targetLocation = $locationModel->findTranslatedLocationById($toLocale, (int) $location['id']);

        if ($targetLocation === null || empty($targetLocation['slug'])) {
            return $segments;
        }

        $translated[] = (string) $targetLocation['slug'];
        $parentId = (int) $location['id'];
    }

    return $translated;
}

function translate_tour_slug(string $fromLocale, string $toLocale, string $slug, ?string $tourType = null): string
{
    if ($fromLocale === $toLocale || $slug === '') {
        return $slug;
    }

    $db = db_connect();

    if (! $db->tableExists('tours') || ! $db->tableExists('tour_translations')) {
        return $slug;
    }

    $builder = $db->table('tour_translations from_tt')
        ->select('to_tt.slug')
        ->join('tour_translations to_tt', 'to_tt.tour_id = from_tt.tour_id AND to_tt.locale = ' . $db->escape($toLocale), 'inner')
        ->join('tours t', 't.id = from_tt.tour_id', 'inner')
        ->where('from_tt.locale', $fromLocale)
        ->where('from_tt.slug', $slug);

    if ($tourType !== null) {
        $builder->where('t.tour_type', $tourType);
    }

    $row = $builder->get()->getRowArray();

    return (string) ($row['slug'] ?? $slug);
}
