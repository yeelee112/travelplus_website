<?php

use App\Services\TourCatalogService;

if (!function_exists('getTourCards')) {
    function getTourCards(?string $locale = null, int $limit = 6): array
    {
        $locale = $locale ?? service('request')->getLocale();
        $service = new TourCatalogService();

        return $service->getHomeTours($locale, $limit);
    }
}

if (!function_exists('getFeaturedTours')) {
    function getFeaturedTours(int $limit = 6, ?string $locale = null): array
    {
        return getTourCards($locale, $limit);
    }
}
