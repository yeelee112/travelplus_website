<?php

namespace App\Controllers;

class Visa extends BaseController
{
    public function index()
    {
        $data['breadcrumbs'] = [
            [
                'label' => 'Trang chủ',
                'url'   => base_url()
            ],
            [
                'label' => 'Dịch vụ Visa'
            ]
        ];

        return view('visa/index', $data);
    }
}   