<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;

class AutumnTours extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';

        return view('autumn/index', [
            'currentLocale' => $locale,
            'meta_title' => $locale === 'en' ? 'Autumn journeys | Travel Plus' : 'Tour mùa thu — Hẹn nhau giữa mùa lá đỏ | Travel Plus',
            'meta_desc' => $locale === 'en'
                ? 'Find your autumn escape in Japan, Korea and Vietnam. Explore tours and plan your journey with Travel Plus.'
                : 'Khám phá mùa thu Nhật Bản, Hàn Quốc và Việt Nam. Tìm hành trình yêu thích và nhận tư vấn lịch khởi hành cùng Travel Plus.',
            'canonical_url' => LocalizedPathCatalog::url('autumn', $locale),
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => LocalizedPathCatalog::url('autumn', 'vi')],
                ['hreflang' => 'en', 'href' => LocalizedPathCatalog::url('autumn', 'en')],
            ],
        ]);
    }
}
