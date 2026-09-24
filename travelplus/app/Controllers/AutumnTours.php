<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;

class AutumnTours extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';

        $month = (string) $this->request->getGet('month');
        $month = in_array($month, ['09', '10', '11'], true) ? $month : '';
        $year = (int) date('Y') + ((int) date('n') > 11 ? 1 : 0);
        $from = $year . '-' . ($month ?: '09') . '-01';
        $to = date('Y-m-t', strtotime($year . '-' . ($month ?: '11') . '-01'));
        $catalog = new \App\Services\TourCatalogService();
        $destinations = $catalog->getCollectionDestinations($locale, 'mua-thu', $year . '-09-01', $year . '-11-30', $locale === 'en' ? 'domestic' : 'inbound');
        $destination = (string) $this->request->getGet('destination');
        $destination = isset($destinations[$destination]) ? $destination : '';
        $destinationId = $destination !== '' ? $destinations[$destination]['id'] : 0;
        $result = $catalog->searchTours(
            $locale, '',
            $from, $to, 6, 1, null, false, $locale === 'en' ? 'domestic' : 'inbound', 'mua-thu', $destinationId
        );

        return view('autumn/index', [
            'autumnTours' => $result['tours'] ?? [],
            'autumnTotal' => (int) ($result['total'] ?? 0),
            'autumnYear' => $year,
            'selectedDestination' => $destination,
            'selectedMonth' => $month,
            'destinationOptions' => $destinations,
            'allToursUrl' => LocalizedPathCatalog::url('search', $locale) . '?' . http_build_query([
                'destination_id' => $destinationId ?: '',
                'departure_from' => $from, 'departure_to' => $to, 'collection' => 'mua-thu',
            ]),
            'breadcrumbs' => [
                ['label' => $locale === 'en' ? 'Home' : 'Trang chủ', 'url' => localized_url('/')],
                ['label' => $locale === 'en' ? 'Autumn tours' : 'Tour mùa thu'],
            ],
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
