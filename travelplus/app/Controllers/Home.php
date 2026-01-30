<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $tours = model('TourModel')->findAll();

        return view('home/index', [
            'tours' => $tours,
            'meta_title' => 'Travel Plus – Explore the World',
            'meta_desc' => 'Book unforgettable travel experiences worldwide'
        ]);
    }
}
