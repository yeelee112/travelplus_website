<?php

namespace App\Controllers;

use App\Services\TourCatalogService;

class Outbound extends BaseController
{
    public function index()
    {
        $data['breadcrumbs'] = [
            [
                'label' => 'Trang chu',
                'url'   => base_url()
            ],
            [
                'label' => 'Tour nuoc ngoai'
            ]
        ];

        $tourService = new TourCatalogService();
        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = $tourService->getPagedTours($this->request->getLocale(), 9, $page, 'outbound');

        $data['tours'] = $result['tours'];
        $data['pagination'] = [
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
        ];

        return view('tour-nuoc-ngoai/index', $data);
    }
}
