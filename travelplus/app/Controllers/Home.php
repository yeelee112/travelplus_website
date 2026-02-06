<?php

namespace App\Controllers;
use App\Data\TourCard;
class Home extends BaseController
{
    public function index()
    {
        $tours = TourCard::getAll();

        return view('home/index', [
            'tours' => $tours
        ]);
    }
}
