<?php

namespace App\Data;

class TourCard
{
    public static function getAll()
    {
        return [
            [
                'id'    => 1,
                'title' => 'Bali Paradise Tour One Two Three Tour Supers',
                'slug'  => 'bali-paradise-tour',
                'link'  => site_url('details/bali-paradise-tour'),
                'image' => base_url('assets/images/avt-tour-01.jpg'),
                'badge' => 'Hot Sale!',
                'continent' => 'Asia',
                'departure' => '13/02/2026',
                'duration' => [
                    'days' => 7,
                    'nights' => 6,
                    'label' => '07 Days - 06 Nights'
                ],
                'price' => [
                    'amount' => 139990000,
                    'currency' => 'đ',
                    'label' => '139.990.000 đ'
                ],
            ],

            [
                'id'    => 2,
                'title' => 'Backwaters & Beaches',
                'slug'  => 'backwaters-beaches',
                'link'  => site_url('travel-package/details/backwaters-beaches'),
                'image' => base_url('assets/images/avt-tour-02.jpg'),
                'badge' => null,
                'continent' => 'European',
                'departure' => '22/02/2026',
                'duration' => [
                    'days' => 3,
                    'nights' => 2,
                    'label' => '03 Days / 02 Nights'
                ],
                'price' => [
                    'amount' => 139990000,
                    'currency' => 'đ',
                    'label' => '139.990.000 đ'
                ],
            ],

            [
                'id'    => 3,
                'title' => 'France - Italy - Switzerland',
                'slug'  => 'france-italy-switzerland',
                'link'  => site_url('travel-package/details/france-italy-switzerland'),
                'image' => base_url('assets/images/avt-tour-01.jpg'),
                'badge' => null,
                'continent' => 'Oceania',
                'departure' => '14/02/2026',
                'duration' => [
                    'days' => 7,
                    'nights' => 6,
                    'label' => '07 Days / 06 Nights'
                ],
                'price' => [
                    'amount' => 139990000,
                    'currency' => 'đ',
                    'label' => '139.990.000 đ'
                ],
            ],
            [
                'id'    => 4,
                'title' => 'France - Italy - Switzerland',
                'slug'  => 'france-italy-switzerland',
                'link'  => site_url('travel-package/details/france-italy-switzerland'),
                'image' => base_url('assets/images/avt-tour-01.jpg'),
                'badge' => null,
                'continent' => 'Oceania',
                'departure' => '14/02/2026',
                'duration' => [
                    'days' => 7,
                    'nights' => 6,
                    'label' => '07 Days / 06 Nights'
                ],
                'price' => [
                    'amount' => 139990000,
                    'currency' => 'đ',
                    'label' => '139.990.000 đ'
                ],
            ],
            [
                'id'    => 5,
                'title' => 'France - Italy - Switzerland',
                'slug'  => 'france-italy-switzerland',
                'link'  => site_url('travel-package/details/france-italy-switzerland'),
                'image' => base_url('assets/images/avt-tour-01.jpg'),
                'badge' => null,
                'continent' => 'Oceania',
                'departure' => '14/02/2026',
                'duration' => [
                    'days' => 7,
                    'nights' => 6,
                    'label' => '07 Days / 06 Nights'
                ],
                'price' => [
                    'amount' => 139990000,
                    'currency' => 'đ',
                    'label' => '139.990.000 đ'
                ],
            ],
            [
                'id'    => 6,
                'title' => 'France - Italy - Switzerland',
                'slug'  => 'france-italy-switzerland',
                'link'  => site_url('travel-package/details/france-italy-switzerland'),
                'image' => base_url('assets/images/avt-tour-01.jpg'),
                'badge' => null,
                'continent' => 'Oceania',
                'departure' => '14/02/2026',
                'duration' => [
                    'days' => 7,
                    'nights' => 6,
                    'label' => '07 Days / 06 Nights'
                ],
                'price' => [
                    'amount' => 139990000,
                    'currency' => 'đ',
                    'label' => '139.990.000 đ'
                ],
            ],
        ];
    }
}
