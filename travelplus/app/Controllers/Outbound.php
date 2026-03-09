<?php

namespace App\Controllers;
use App\Data\TourCard;
class Outbound extends BaseController
{
    public function index()
    {
        $data['breadcrumbs'] = [
            [
                'label' => 'Trang chủ',
                'url'   => base_url()
            ],
            [
                'label' => 'Tour nước ngoài'
            ]
        ];

        $data['tours'] = TourCard::getAll();

        return view('tour-nuoc-ngoai/index', $data);
    }
}   