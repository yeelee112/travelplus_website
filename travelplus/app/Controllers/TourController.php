<?php
namespace App\Controllers;

use App\Data\TourCard;

class TourController extends BaseController
{
    public function featured()
    {
        $tours = TourCard::getAll();

        return view('sections/featured-tour', ['tours' => $tours]);
    }

    public function homeTour()
    {
        $tours = TourCard::getAll();

        return view('sections/home-tour', ['tours' => $tours]);
    }
}
