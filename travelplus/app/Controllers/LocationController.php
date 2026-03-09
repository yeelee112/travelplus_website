<?php

namespace App\Controllers;

use App\Models\TourModel;
use App\Models\LocationModel;

class LocationController extends BaseController
{
    public function continent($locale, $continentSlug)
    {
        // tìm continent theo slug
        // lấy tất cả tour thuộc continent đó
        echo "Continent page: $continentSlug ($locale)";
    }

    public function country($locale, $continentSlug, $countrySlug)
    {
        echo "Country page: $countrySlug ($locale)";
    }

    public function province($locale, $continentSlug, $countrySlug, $provinceSlug)
    {
        echo "Province page: $provinceSlug ($locale)";
    }
}