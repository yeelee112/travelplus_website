<?php

namespace App\Controllers;

use App\Data\LegalPageCatalog;
use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;
use CodeIgniter\Exceptions\PageNotFoundException;

class LegalController extends BaseController
{
    public function terms(string $locale = 'vi')
    {
        return $this->renderPage('terms', $locale);
    }

    public function privacy(string $locale = 'vi')
    {
        return $this->renderPage('privacy', $locale);
    }

    private function renderPage(string $type, string $locale)
    {
        $locale = $locale === 'en' ? 'en' : 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $page = LegalPageCatalog::get($type, $locale);

        if ($page === []) {
            throw PageNotFoundException::forPageNotFound();
        }

        $pathKey = $type === 'privacy' ? 'legal.privacy' : 'legal.terms';
        $relatedPathKey = $type === 'privacy' ? 'legal.terms' : 'legal.privacy';
        $relatedLabel = $type === 'privacy'
            ? lang('Frontend.footer.link.terms', [], $locale)
            : lang('Frontend.footer.link.privacy', [], $locale);
        $page['related_links'] = [
            [
                'label' => $relatedLabel,
                'url' => LocalizedPathCatalog::url($relatedPathKey, $locale),
            ],
            [
                'label' => $locale === 'en' ? 'Visa service' : 'Dịch vụ visa',
                'url' => LocalizedPathCatalog::url('service.visa', $locale),
            ],
            [
                'label' => $locale === 'en' ? 'MICE service' : 'Dịch vụ MICE',
                'url' => LocalizedPathCatalog::url('service.mice', $locale),
            ],
            [
                'label' => $locale === 'en' ? 'Contact Travel Plus' : 'Liên hệ Travel Plus',
                'url' => LocalizedPathCatalog::url('contact', $locale),
            ],
        ];
        $page['cta']['primary_url'] = LocalizedPathCatalog::url('contact', $locale);
        $page['cta']['secondary_url'] = LocalizedPathCatalog::url($relatedPathKey, $locale);

        $data = [
            'page' => $page,
            'pageType' => $type,
            'locale' => $locale,
            'breadcrumbs' => [
                [
                    'label' => $t('common.home'),
                    'url' => localized_url('/'),
                ],
                [
                    'label' => $page['title'],
                ],
            ],
            'meta_title' => $page['meta_title'],
            'meta_desc' => $page['meta_desc'],
            'canonical_url' => LocalizedPathCatalog::url($pathKey, $locale),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url(LocalizedPathCatalog::path($pathKey, 'vi'))],
                ['hreflang' => 'en', 'href' => base_url('en/' . LocalizedPathCatalog::path($pathKey, 'en'))],
                ['hreflang' => 'x-default', 'href' => base_url(LocalizedPathCatalog::path($pathKey, 'vi'))],
            ],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
            $seo->faqSchema($page['faqs'] ?? []),
        ];

        return view('legal/page', $data);
    }
}
