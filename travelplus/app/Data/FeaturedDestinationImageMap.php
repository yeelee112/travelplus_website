<?php

namespace App\Data;

class FeaturedDestinationImageMap
{
    /**
     * @return array<string, string>
     */
    public static function getAll(): array
    {
        return [
            // Outbound countries
            'phap' => 'assets/images/tour-temp/eiffel.webp',
            'y' => 'assets/images/destination/italy.jpg',
            'thuy-si' => 'assets/images/tour-temp/Titlis-Paranoma.jpeg',
            'nhat-ban' => 'assets/images/gallery-1.jpg',
            'my' => 'assets/images/gallery-3.jpg',

            // Domestic provinces / cities
            'thanh-pho-ho-chi-minh' => 'assets/images/gallery-6.jpg',
            'da-nang' => 'assets/images/destination/da-nang.jpg',
            'ha-noi' => 'assets/images/destination/ha-noi.webp',
            'sa-pa' => 'assets/images/destination/sa-pa.jpg',
        ];
    }
}
