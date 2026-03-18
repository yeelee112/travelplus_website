<?php

namespace App\Controllers;
use App\Services\TourCatalogService;

class Home extends BaseController
{
    public function index()
    {
        $tourService = new TourCatalogService();
        $tours = $tourService->getHomeTours($this->request->getLocale(), 6);

        return view('home/index', [
            'tours' => $tours
        ]);
    }

}
