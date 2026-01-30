<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Load models for content
        $tourModel = model(\App\Models\TourModel::class);
        $categoryModel = model(\App\Models\CategoryModel::class);
        $postModel = model(\App\Models\PostModel::class);

        $featured = $tourModel->getFeatured(3);
        $categories = $categoryModel->getTop(8);
        $recent = $postModel->getRecent(3);

        $meta = [
            'title' => lang('Frontend.site_title'),
            'description' => lang('Frontend.featured_tours'),
            'canonical' => site_url((service('request')->getLocale() ? service('request')->getLocale().'/' : '').''),
        ];

        return view('home/index', compact('meta','featured','categories','recent'));
    }
}
