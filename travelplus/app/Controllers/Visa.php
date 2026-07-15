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
        $serviceTypes = $locale === 'en'
            ? ['Visa service', 'U.S. visa service', 'Canada visa service', 'Australia visa service', 'Schengen visa service', 'Japan visa service', 'South Korea visa service', 'Visa document checklist', 'Visa file review', 'Visa appointment guidance']
            : ['Dịch vụ làm visa', 'Làm visa Mỹ', 'Làm visa Canada', 'Làm visa Úc', 'Visa Schengen', 'Visa Nhật Bản', 'Visa Hàn Quốc', 'Checklist hồ sơ visa', 'Rà soát hồ sơ visa', 'Hướng dẫn đặt lịch hẹn visa'];

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
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => base_url(LocalizedPathCatalog::path('service.visa', 'vi'))],
            ['hreflang' => 'en', 'href' => base_url('en/' . LocalizedPathCatalog::path('service.visa', 'en'))],
            ['hreflang' => 'x-default', 'href' => base_url(LocalizedPathCatalog::path('service.visa', 'vi'))],
        ];
        $data['pageContent'] = VisaPageContent::get($locale);
        $data['meta_image'] = base_url('assets/images/visa-banner.png');
        $data['meta_image_alt'] = $t('common.visaService');
        $data['contact_form_token'] = bin2hex(random_bytes(16));
        session()->set('contact_form_token', $data['contact_form_token']);
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
            $seo->serviceSchema(
                $t('common.visaService'),
                (string) $data['meta_desc'],
                (string) $data['canonical_url'],
                'assets/images/visa-banner.png',
                $serviceTypes
            ),
            $seo->faqSchema((array) ($data['pageContent']['faqs'] ?? [])),
        ];

        return view('visa/index', $data);
    }
}
