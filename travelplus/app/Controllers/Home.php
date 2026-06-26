<?php

namespace App\Controllers;

use App\Data\FeaturedDestinationCatalog;
use App\Data\LocalizedPathCatalog;
use App\Data\TourCard;
use App\Services\BlogService;
use App\Services\DatabaseAvailabilityService;
use App\Services\DomesticRegionService;
use App\Services\SeoService;
use App\Services\TourCatalogService;
use Throwable;

class Home extends BaseController
{
    public function index()
    {
        $seo = new SeoService();
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $canonicalUrl = localized_url('/');
        $searchUrl = LocalizedPathCatalog::url('search', $locale) . '?q={search_term_string}';
        $metaDesc = $t('home.metaDesc');
        $tourFallback = array_slice(TourCard::getAll(), 0, 6);
        $featuredTours = $this->safeSection(
            'featured tours',
            static fn(): array => (new TourCatalogService())->getFeaturedTours($locale, 6),
            $tourFallback
        );
        $promotionalTours = $this->safeSection(
            'promotional tours',
            static fn(): array => (new TourCatalogService())->getPromotionalTours($locale, 4)
        );
        $featuredDestinations = $this->safeSection(
            'featured destinations',
            fn(): array => $this->getCuratedFeaturedDestinations($locale),
            [],
            false
        );
        $homeTours = $this->safeSection(
            'home tours',
            static fn(): array => (new TourCatalogService())->getHomeTours($locale, 6),
            $tourFallback
        );
        $homeBlogs = $this->safeSection(
            'home blogs',
            static fn(): array => (new BlogService())->getHomeBlogs($locale, 3)
        );

        return view('home/index', [
            'featuredTours' => $featuredTours,
            'promotionalTours' => $promotionalTours,
            'featuredDestinations' => $featuredDestinations,
            'homeTours' => $homeTours,
            'homeBlogs' => $homeBlogs,
            'meta_title' => $t('home.metaTitle'),
            'meta_desc' => $metaDesc,
            'canonical_url' => $canonicalUrl,
            'meta_image' => base_url('assets/images/TravelPlus_CompanyProfile.png'),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url('/')],
                ['hreflang' => 'en', 'href' => base_url('en')],
                ['hreflang' => 'x-default', 'href' => base_url('/')],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->websiteSchema($searchUrl),
                $seo->webpageSchema(
                    $t('home.webpageTitle'),
                    $metaDesc,
                    $canonicalUrl
                ),
                $seo->itemListSchema($t('home.homeTour.title'), $canonicalUrl, $homeTours, 'Product'),
                $seo->itemListSchema($t('blog.listTitle'), $canonicalUrl, $homeBlogs, 'BlogPosting'),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function safeSection(
        string $name,
        callable $callback,
        array $fallback = [],
        bool $skipWhenDatabaseUnavailable = true
    ): array
    {
        if ($skipWhenDatabaseUnavailable && DatabaseAvailabilityService::isUnavailable()) {
            return $fallback;
        }

        try {
            $result = $callback();

            return is_array($result) ? $result : [];
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Home section failed [' . $name . ']');

            return $fallback;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getCuratedFeaturedDestinations(string $locale): array
    {
        $tabs = [];

        foreach (FeaturedDestinationCatalog::getAll() as $tab) {
            $items = [];

            foreach ((array) ($tab['items'] ?? []) as $index => $item) {
                $image = trim((string) ($item['image'] ?? ''));

                $items[] = [
                    'title' => $this->translateFeaturedDestinationValue($item['title'] ?? '', $locale),
                    'subtitle' => $this->translateFeaturedDestinationValue($item['subtitle'] ?? '', $locale),
                    'image' => $this->resolveFeaturedDestinationImage($image),
                    'link' => $this->buildFeaturedDestinationLink((array) $item, $locale),
                    'col' => $this->featuredDestinationColClass($index),
                ];
            }

            if ($items === []) {
                continue;
            }

            $tabs[] = [
                'key' => (string) ($tab['key'] ?? ('featured-tab-' . count($tabs))),
                'label' => $this->translateFeaturedDestinationValue($tab['label'] ?? '', $locale),
                'items' => $items,
            ];
        }

        return $tabs;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function buildFeaturedDestinationLink(array $item, string $locale): string
    {
        $kind = (string) ($item['kind'] ?? '');
        $destinationSlug = $this->resolveFeaturedDestinationSlug($item['destination_slug'] ?? '', $locale);

        if ($kind === 'outbound_country') {
            $continentSlug = $this->resolveFeaturedDestinationSlug($item['continent_slug'] ?? '', $locale);

            if ($locale !== 'vi') {
                $continentSlugVi = $this->resolveFeaturedDestinationSlug($item['continent_slug'] ?? '', 'vi');
                $destinationSlugVi = $this->resolveFeaturedDestinationSlug($item['destination_slug'] ?? '', 'vi');
                $localizedSlugs = $this->resolveOutboundDestinationSlugs(
                    $continentSlugVi,
                    $destinationSlugVi,
                    $locale
                );

                if ($localizedSlugs !== null) {
                    $continentSlug = $localizedSlugs['continent_slug'];
                    $destinationSlug = $localizedSlugs['destination_slug'];
                }
            }

            if ($continentSlug !== '' && $destinationSlug !== '') {
                return localized_url($continentSlug . '/' . $destinationSlug);
            }
        }

        if ($kind === 'domestic_province') {
            $regionSlug = $this->resolveFeaturedDestinationSlug($item['region_slug'] ?? '', $locale);

            if ($locale !== 'vi' && ! DatabaseAvailabilityService::isUnavailable()) {
                $regionSlugVi = $this->resolveFeaturedDestinationSlug($item['region_slug'] ?? '', 'vi');
                $destinationSlugVi = $this->resolveFeaturedDestinationSlug($item['destination_slug'] ?? '', 'vi');
                try {
                    $translatedSegments = (new DomesticRegionService())->translatePathSegments(
                        [
                            'tour-trong-nuoc',
                            $regionSlugVi,
                            $destinationSlugVi,
                        ],
                        'vi',
                        $locale
                    );
                } catch (Throwable $exception) {
                    DatabaseAvailabilityService::markUnavailable($exception, 'Featured destination domestic slug translation failed');
                    $translatedSegments = [];
                }

                $translatedRegionSlug = (string) ($translatedSegments[1] ?? '');
                $translatedDestinationSlug = (string) ($translatedSegments[2] ?? '');

                if ($translatedRegionSlug !== '' && $translatedRegionSlug !== $regionSlugVi) {
                    $regionSlug = $translatedRegionSlug;
                }

                if ($translatedDestinationSlug !== '' && $translatedDestinationSlug !== $destinationSlugVi) {
                    $destinationSlug = $translatedDestinationSlug;
                }
            }

            if ($regionSlug !== '' && $destinationSlug !== '') {
                return localized_url('tour-trong-nuoc/' . $regionSlug . '/' . $destinationSlug);
            }
        }

        return LocalizedPathCatalog::url('search', $locale);
    }

    /**
     * @param mixed $value
     */
    private function resolveFeaturedDestinationSlug($value, string $locale): string
    {
        if (is_array($value)) {
            $localized = trim((string) ($value[$locale] ?? ''));

            if ($localized !== '') {
                return $localized;
            }

            return trim((string) ($value['vi'] ?? ''));
        }

        return trim((string) $value);
    }

    /**
     * @return array{continent_slug: string, destination_slug: string}|null
     */
    private function resolveOutboundDestinationSlugs(string $continentSlugVi, string $destinationSlugVi, string $locale): ?array
    {
        $continentSlugVi = trim($continentSlugVi);
        $destinationSlugVi = trim($destinationSlugVi);

        if ($continentSlugVi === '' || $destinationSlugVi === '') {
            return null;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            return null;
        }

        try {
            $db = db_connect();
            $row = $db->table('locations country')
                ->select(
                    'continent_target.slug AS continent_slug,' .
                    'country_target.slug AS destination_slug',
                    false
                )
                ->join('locations continent', 'continent.id = country.parent_id AND continent.type = "continent"', 'inner')
                ->join('location_translations continent_source', 'continent_source.location_id = continent.id AND continent_source.locale = "vi"', 'inner')
                ->join('location_translations country_source', 'country_source.location_id = country.id AND country_source.locale = "vi"', 'inner')
                ->join('location_translations continent_target', 'continent_target.location_id = continent.id AND continent_target.locale = ' . $db->escape($locale), 'left')
                ->join('location_translations country_target', 'country_target.location_id = country.id AND country_target.locale = ' . $db->escape($locale), 'left')
                ->where('country.type', 'country')
                ->where('continent_source.slug', $continentSlugVi)
                ->where('country_source.slug', $destinationSlugVi)
                ->limit(1)
                ->get()
                ->getRowArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Featured destination slug translation failed');

            return null;
        }

        if (! is_array($row)) {
            return null;
        }

        $continentSlug = trim((string) ($row['continent_slug'] ?? ''));
        $destinationSlug = trim((string) ($row['destination_slug'] ?? ''));

        if ($continentSlug === '' || $destinationSlug === '') {
            return null;
        }

        return [
            'continent_slug' => $continentSlug,
            'destination_slug' => $destinationSlug,
        ];
    }

    /**
     * @param mixed $value
     */
    private function translateFeaturedDestinationValue($value, string $locale): string
    {
        if (is_array($value)) {
            $translated = trim((string) ($value[$locale] ?? ''));

            if ($translated !== '') {
                return $translated;
            }

            return trim((string) ($value['vi'] ?? ''));
        }

        return trim((string) $value);
    }

    private function resolveFeaturedDestinationImage(string $path): string
    {
        if ($path === '') {
            return base_url('assets/images/avt-tour-01.jpg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return base_url(ltrim($path, '/'));
    }

    private function featuredDestinationColClass(int $index): string
    {
        if ($index === 0) {
            return 'col-lg-6 col-md-6';
        }

        return 'col-lg-3 col-md-6';
    }
}
