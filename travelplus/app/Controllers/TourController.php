<?php
namespace App\Controllers;

use App\Services\TourCatalogService;

class TourController extends BaseController
{
    public function featured()
    {
        $tourService = new TourCatalogService();
        $tours = $tourService->getHomeTours($this->request->getLocale(), 6);

        return view('sections/featured-tour', ['tours' => $tours]);
    }

    public function homeTour()
    {
        $tourService = new TourCatalogService();
        $tours = $tourService->getHomeTours($this->request->getLocale(), 6);

        return view('sections/home-tour', ['tours' => $tours]);
    }
}
