<?php
namespace App\Controllers;

use App\Services\TourCatalogService;

class TourController extends BaseController
{
    public function preview()
    {
        return view('tour/index', [
            'featuredTours' => $this->getFeaturedTours(),
        ]);
    }

    public function featured()
    {
        $tours = $this->getFeaturedTours();

        return view('sections/featured-tour', ['tours' => $tours]);
    }

    public function homeTour()
    {
        $tourService = new TourCatalogService();
        $tours = $tourService->getHomeTours($this->request->getLocale(), 6);

        return view('sections/home-tour', ['tours' => $tours]);
    }

    private function getFeaturedTours(int $limit = 6): array
    {
        $tourService = new TourCatalogService();

        return $tourService->getHomeTours($this->request->getLocale(), $limit);
    }
}
