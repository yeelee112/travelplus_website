<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\BlogService;
use App\Services\DatabaseAvailabilityService;
use App\Services\PublicContentCacheService;
use App\Services\TourCatalogService;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    public function index()
    {
        $contentCache = new PublicContentCacheService();
        $cacheKey = 'sitemap:' . base_url();
        $cachedXml = $contentCache->get($cacheKey);
        if (is_string($cachedXml) && $cachedXml !== '') {
            return $this->response
                ->setCache(['public', 'max-age' => 900])
                ->setContentType('application/xml')
                ->setBody($cachedXml);
        }

        $locales = ['vi', 'en'];
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"></urlset>');
        $urls = [];
        $staticLastmod = $this->lastModifiedFromFiles([
            APPPATH . 'Config/Routes.php',
            APPPATH . 'Views/layouts/main.php',
            APPPATH . 'Controllers/Home.php',
        ]);

        $staticPathKeys = [
            'about',
            'blog',
            'summer',
            'service.visa',
            'service.mice',
            'service.airlineTickets',
            'service.transport',
            'service.translation',
            'service.hotels',
            'contact',
            'legal.terms',
            'legal.privacy',
            'outbound',
            'domestic',
        ];
        $staticPaths = [
            'vi' => [''],
            'en' => ['en'],
        ];

        foreach ($staticPathKeys as $key) {
            foreach ($locales as $locale) {
                $path = LocalizedPathCatalog::path($key, $locale);
                if ($path === '') {
                    continue;
                }

                $staticPaths[$locale][] = $locale === 'en' ? 'en/' . $path : $path;
            }
        }

        foreach ($locales as $locale) {
            foreach ($staticPaths[$locale] as $path) {
                $urls[] = [
                    'loc' => base_url($path),
                    'lastmod' => $staticLastmod,
                    'changefreq' => $path === '' || $path === 'en' ? 'daily' : 'weekly',
                    'priority' => $path === '' || $path === 'en' ? '1.0' : '0.8',
                    'locale' => $locale,
                    'group' => $path === '' || $path === 'en' ? 'static:home' : 'static:' . $this->staticPathGroup($path, $locale),
                ];
            }
        }

        $tourService = new TourCatalogService();
        foreach ($locales as $locale) {
            $result = $tourService->getPagedTours($locale, 5000, 1);

            foreach ($result['tours'] as $tour) {
                if (empty($tour['link'])) {
                    continue;
                }

                $urls[] = [
                    'loc' => (string) $tour['link'],
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                    'locale' => $locale,
                    'group' => 'tour:' . (string) ($tour['id'] ?? md5((string) $tour['link'])),
                ];
            }
        }

        $blogService = new BlogService();
        if ($blogService->hasTables()) {
            foreach ($locales as $locale) {
                foreach ($blogService->getPublishedBlogs($locale, 5000) as $blog) {
                    if (empty($blog['link'])) {
                        continue;
                    }

                    $urls[] = [
                        'loc' => (string) $blog['link'],
                        'lastmod' => $this->sitemapDate((string) ($blog['updated_at'] ?? $blog['published_at'] ?? '')),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                        'locale' => $locale,
                        'group' => 'blog:' . (string) ($blog['id'] ?? md5((string) $blog['link'])),
                    ];
                }
            }
        }

        $alternateGroups = [];
        foreach ($urls as $entry) {
            $group = (string) ($entry['group'] ?? '');
            $locale = (string) ($entry['locale'] ?? '');
            $loc = (string) ($entry['loc'] ?? '');

            if ($group !== '' && in_array($locale, $locales, true) && $loc !== '') {
                $alternateGroups[$group][$locale] = $loc;
            }
        }

        $seen = [];
        foreach ($urls as $entry) {
            $loc = (string) ($entry['loc'] ?? '');

            if ($loc === '' || isset($seen[$loc])) {
                continue;
            }

            $seen[$loc] = true;
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($loc, ENT_XML1));
            $lastmod = $this->sitemapDate((string) ($entry['lastmod'] ?? ''));
            if ($lastmod !== '') {
                $url->addChild('lastmod', $lastmod);
            }
            $url->addChild('changefreq', (string) ($entry['changefreq'] ?? 'weekly'));
            $url->addChild('priority', (string) ($entry['priority'] ?? '0.7'));

            $alternates = $alternateGroups[(string) ($entry['group'] ?? '')] ?? [];
            if (isset($alternates['vi'], $alternates['en'])) {
                foreach (['vi', 'en'] as $alternateLocale) {
                    $link = $url->addChild('link', null, 'http://www.w3.org/1999/xhtml');
                    $link->addAttribute('rel', 'alternate');
                    $link->addAttribute('hreflang', $alternateLocale);
                    $link->addAttribute('href', $alternates[$alternateLocale]);
                }

                $defaultLink = $url->addChild('link', null, 'http://www.w3.org/1999/xhtml');
                $defaultLink->addAttribute('rel', 'alternate');
                $defaultLink->addAttribute('hreflang', 'x-default');
                $defaultLink->addAttribute('href', $alternates['vi']);
            }
        }

        $xmlBody = (string) $xml->asXML();
        if (! DatabaseAvailabilityService::isUnavailable()) {
            $contentCache->save($cacheKey, $xmlBody, 900);
        }

        return $this->response
            ->setCache(['public', 'max-age' => 900])
            ->setContentType('application/xml')
            ->setBody($xmlBody);
    }

    /**
     * @param list<string> $paths
     */
    private function lastModifiedFromFiles(array $paths): string
    {
        $timestamps = [];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $modified = filemtime($path);
                if ($modified !== false) {
                    $timestamps[] = $modified;
                }
            }
        }

        if ($timestamps === []) {
            return '';
        }

        return gmdate('Y-m-d', max($timestamps));
    }

    private function sitemapDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : gmdate('Y-m-d', $timestamp);
    }

    private function staticPathGroup(string $path, string $locale): string
    {
        $path = trim($path, '/');

        if ($locale === 'en' && str_starts_with($path, 'en/')) {
            $path = substr($path, 3);
        }

        foreach ([
            'travel-inspiration' => 'cam-hung-du-lich',
            'summer-tours' => 'tour-he',
            'airline-ticket-service' => 'dich-vu-ve-may-bay',
            'transport-service' => 'dich-vu-van-chuyen',
            'translation-service' => 'dich-vu-dich-thuat',
            'hotel-service' => 'dich-vu-khach-san',
            'terms-of-service' => 'dieu-khoan-su-dung',
            'privacy-statement' => 'chinh-sach-bao-mat',
        ] as $englishPath => $vietnamesePath) {
            if ($path === $englishPath) {
                return $vietnamesePath;
            }
        }

        return $path;
    }
}
