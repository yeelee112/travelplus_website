<?php

namespace App\Controllers;

use App\Services\BlogService;
use App\Services\TourCatalogService;
use CodeIgniter\Controller;

class Sitemap extends Controller
{
    public function index()
    {
        $locales = ['vi', 'en'];
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
        $urls = [];

        $staticPaths = [
            'vi' => [
                '',
                've-chung-toi',
                'cam-hung-du-lich',
                'dich-vu-visa',
                'dich-vu-mice',
                'dich-vu-ve-may-bay',
                'dich-vu-van-chuyen',
                'dich-vu-dich-thuat',
                'dich-vu-khach-san',
                'contact',
                'dieu-khoan-su-dung',
                'chinh-sach-bao-mat',
                'tour-nuoc-ngoai',
                'tour-trong-nuoc',
            ],
            'en' => [
                'en',
                'en/ve-chung-toi',
                'en/travel-inspiration',
                'en/dich-vu-visa',
                'en/dich-vu-mice',
                'en/airline-ticket-service',
                'en/transport-service',
                'en/translation-service',
                'en/hotel-service',
                'en/contact',
                'en/terms-of-service',
                'en/privacy-statement',
                'en/tour-nuoc-ngoai',
                'en/tour-trong-nuoc',
            ],
        ];

        foreach ($locales as $locale) {
            foreach ($staticPaths[$locale] as $path) {
                $urls[] = [
                    'loc' => base_url($path),
                    'changefreq' => $path === '' || $path === 'en' ? 'daily' : 'weekly',
                    'priority' => $path === '' || $path === 'en' ? '1.0' : '0.8',
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
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ];
                }
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
            $url->addChild('changefreq', (string) ($entry['changefreq'] ?? 'weekly'));
            $url->addChild('priority', (string) ($entry['priority'] ?? '0.7'));
        }

        return $this->response->setContentType('application/xml')->setBody($xml->asXML());
    }
}
