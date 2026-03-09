<?php

namespace App\Controllers;

use App\Models\LocationModel;

class Header extends BaseController
{
    public function index()
    {
        $locationModel = new LocationModel();

        // detect locale
        $segment1 = service('uri')->getSegment(1);
        $locale = ($segment1 === 'en') ? 'en' : 'vi';

        $menu = $locationModel->getMenu($locale);

        return view('partials/header', [
            'menu' => $menu
        ]);
    }
}