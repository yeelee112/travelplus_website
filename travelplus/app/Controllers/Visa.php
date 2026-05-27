<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Data\VisaPageContent;
use App\Services\SeoService;

class Visa extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();

        $data['breadcrumbs'] = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $t('common.visaService'),
            ],
        ];
        $data['meta_title'] = $t('visa.metaTitle');
        $data['meta_desc'] = $t('visa.metaDesc');
        $data['canonical_url'] = LocalizedPathCatalog::url('service.visa', $locale);
        $data['pageContent'] = VisaPageContent::get($locale);
        $data['meta_image'] = base_url('assets/images/visa-banner.png');
        $data['meta_image_alt'] = $t('common.visaService');
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
            $seo->serviceSchema(
                $t('common.visaService'),
                (string) $data['meta_desc'],
                (string) $data['canonical_url'],
                'assets/images/visa-banner.png',
                ['Visa support', 'Travel document support']
            ),
            $seo->faqSchema((array) ($data['pageContent']['faqs'] ?? [])),
        ];

        return view('visa/index', $data);
    }
}
