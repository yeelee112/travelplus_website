<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Data\MicePageContent;
use App\Services\SeoService;

class Mice extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
        $seo = new SeoService();
        $serviceTypes = $locale === 'en'
            ? ['Corporate MICE', 'Meetings and conferences', 'Incentive travel', 'Team building', 'Gala dinner', 'Healthcare MICE', 'Pharmaceutical MICE']
            : ['MICE doanh nghiệp', 'Tổ chức hội nghị hội thảo', 'Du lịch incentive', 'Team building', 'Gala dinner', 'MICE ngành y/dược'];

        $data['breadcrumbs'] = [
            [
                'label' => $t('common.home'),
                'url' => localized_url('/'),
            ],
            [
                'label' => $t('common.miceService'),
            ],
        ];

        $data['meta_title'] = $t('mice.metaTitle');
        $data['meta_desc'] = $t('mice.metaDesc');
        $data['canonical_url'] = LocalizedPathCatalog::url('service.mice', $locale);
        $data['pageContent'] = MicePageContent::get($locale);
        $data['meta_image'] = base_url('assets/images/mice-1.jpeg');
        $data['meta_image_alt'] = $t('common.miceService');
        $data['alternate_links'] = [
            ['hreflang' => 'vi', 'href' => base_url('dich-vu-mice')],
            ['hreflang' => 'en', 'href' => base_url('en/dich-vu-mice')],
            ['hreflang' => 'x-default', 'href' => base_url('dich-vu-mice')],
        ];
        $data['schema_graph'] = [
            $seo->organizationSchema(),
            $seo->breadcrumbSchema($data['breadcrumbs'], (string) $data['canonical_url']),
            $seo->webpageSchema((string) $data['meta_title'], (string) $data['meta_desc'], (string) $data['canonical_url']),
            $seo->serviceSchema(
                $t('common.miceService'),
                (string) $data['meta_desc'],
                (string) $data['canonical_url'],
                'assets/images/mice-1.jpeg',
                $serviceTypes
            ),
        ];

        return view('mice/index', $data);
    }
}
