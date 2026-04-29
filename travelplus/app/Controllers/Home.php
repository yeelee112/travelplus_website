<?php

namespace App\Controllers;
use App\Services\TourCatalogService;

class Home extends BaseController
{
    public function index()
    {
        $tourService = new TourCatalogService();
        $locale = $this->request->getLocale();

        return view('home/index', [
            'featuredTours' => $tourService->getFeaturedTours($locale, 6),
            'homeTours' => $tourService->getHomeTours($locale, 6),
        ]);
    }

}
