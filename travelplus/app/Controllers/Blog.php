<?php

namespace App\Controllers;

class Blog extends BaseController
{
    public function index()
    {
        $data['breadcrumbs'] = [
            [
                'label' => 'Trang chủ',
                'url'   => base_url()
            ],
            [
                'label' => 'Cảm hứng du lịch'
            ]
        ];

        return view('blog/index', $data);
    }
}   