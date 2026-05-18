<?php

namespace App\Controllers;

use App\Data\ServicePageCatalog;
use App\Services\SeoService;

class Services extends BaseController
{
    private const DEFAULT_LOCALE = 'vi';

    public function airlineTickets()
    {
        return $this->renderServicePage('airline_tickets');
    }

    public function transport()
    {
        return $this->renderServicePage('transport');
    }

    public function translation()
    {
        return $this->renderServicePage('translation');
    }

    public function hotels()
    {
        return $this->renderServicePage('hotels');
    }

    private function renderServicePage(string $serviceKey)
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $pages = ServicePageCatalog::getAll();
        $page = $pages[$serviceKey] ?? null;

        if ($page === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $viPath = $page['paths']['vi'];
        $enPath = $page['paths']['en'];

        $data = $page;
        $data['breadcrumbs'] = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $page['nav_label'][$locale] ?? $page['nav_label']['vi'],
            ],
        ];
        $data['meta_title'] = $page['meta_title'][$locale] ?? $page['meta_title']['vi'];
        $data['meta_desc'] = $page['meta_desc'][$locale] ?? $page['meta_desc']['vi'];
        $data['canonical_url'] = localized_url($page['paths'][$locale] ?? $viPath);
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => base_url($viPath)],
            ['hreflang' => 'en', 'href' => base_url('en/' . $enPath)],
            ['hreflang' => 'x-default', 'href' => base_url($viPath)],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
        ];

        return view('services/page', $data);
    }
}
