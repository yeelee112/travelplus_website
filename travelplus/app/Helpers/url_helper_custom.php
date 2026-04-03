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
