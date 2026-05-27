<?php

namespace App\Controllers;

use App\Data\FeaturedDestinationCatalog;
use App\Data\LocalizedPathCatalog;
use App\Services\BlogService;
use App\Services\SeoService;
use App\Services\TourCatalogService;

class Home extends BaseController
{
    public function index()
    {
        $tourService = new TourCatalogService();
        $blogService = new BlogService();
        $seo = new SeoService();
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $canonicalUrl = localized_url('/');
        $searchUrl = LocalizedPathCatalog::url('search', $locale) . '?q={search_term_string}';
        $metaDesc = $t('home.metaDesc');
        $featuredTours = $tourService->getFeaturedTours($locale, 6);
        $featuredDestinations = $this->getCuratedFeaturedDestinations($locale);
        $homeTours = $tourService->getHomeTours($locale, 6);
        $homeBlogs = $blogService->getHomeBlogs($locale, 3);

        return view('home/index', [
            'featuredTours' => $featuredTours,
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
        $destinationSlug = trim((string) ($item['destination_slug'] ?? ''));

        if ($kind === 'outbound_country') {
            $continentSlug = trim((string) ($item['continent_slug'] ?? ''));

            if ($continentSlug !== '' && $destinationSlug !== '') {
                return localized_url($continentSlug . '/' . $destinationSlug);
            }
        }

        if ($kind === 'domestic_province') {
            $regionSlug = trim((string) ($item['region_slug'] ?? ''));

            if ($regionSlug !== '' && $destinationSlug !== '') {
                return localized_url('tour-trong-nuoc/' . $regionSlug . '/' . $destinationSlug);
            }
        }

        return LocalizedPathCatalog::url('search', $locale);
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
