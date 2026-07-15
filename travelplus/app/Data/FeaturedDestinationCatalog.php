<?php

namespace App\Data;

class FeaturedDestinationCatalog
{
    /**
     * Chỉnh danh sách featured destination tại đây.
     *
     * outbound_country:
     * - continent_slug: slug châu lục
     * - destination_slug: slug quốc gia
     *
     * domestic_province:
     * - region_slug: slug miền
     * - destination_slug: slug tỉnh/thành
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(): array
    {
        return [
            [
                'key' => 'vietnam',
                'label' => [
                    'vi' => 'Việt Nam',
                    'en' => 'Vietnam',
                ],
                'items' => [
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Đà Nẵng', 'en' => 'Da Nang'],
                        'subtitle' => ['vi' => 'Miền Trung', 'en' => 'Central Vietnam'],
                        'region_slug' => ['vi' => 'mien-trung', 'en' => 'central-vietnam'],
                        'destination_slug' => ['vi' => 'da-nang', 'en' => 'danang'],
                        'image' => 'assets/images/destination/da-nang.jpg',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Hà Nội', 'en' => 'Hanoi'],
                        'subtitle' => ['vi' => 'Miền Bắc', 'en' => 'Northern Vietnam'],
                        'region_slug' => ['vi' => 'mien-bac', 'en' => 'northern-vietnam'],
                        'destination_slug' => ['vi' => 'ha-noi', 'en' => 'hanoi'],
                        'image' => 'assets/images/destination/ha-noi.webp',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Sa Pa', 'en' => 'Sapa'],
                        'subtitle' => ['vi' => 'Miền Bắc', 'en' => 'Northern Vietnam'],
                        'region_slug' => ['vi' => 'mien-bac', 'en' => 'northern-vietnam'],
                        'destination_slug' => ['vi' => 'sa-pa', 'en' => 'sapa'],
                        'image' => 'assets/images/destination/sa-pa.webp',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Nha Trang', 'en' => 'Nha Trang'],
                        'subtitle' => ['vi' => 'Miền Trung', 'en' => 'Central Vietnam'],
                        'region_slug' => ['vi' => 'mien-trung', 'en' => 'central-vietnam'],
                        'destination_slug' => ['vi' => 'nha-trang', 'en' => 'nha-trang'],
                        'image' => 'assets/images/destination/nha-trang.webp',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Đà Lạt', 'en' => 'Dalat'],
                        'subtitle' => ['vi' => 'Miền Nam', 'en' => 'Southern Vietnam'],
                        'region_slug' => ['vi' => 'mien-nam', 'en' => 'southern-vietnam'],
                        'destination_slug' => ['vi' => 'da-lat', 'en' => 'dalat'],
                        'image' => 'assets/images/destination/da-lat.webp',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Phú Quốc', 'en' => 'Phu Quoc'],
                        'subtitle' => ['vi' => 'Miền Nam', 'en' => 'Southern Vietnam'],
                        'region_slug' => ['vi' => 'mien-nam', 'en' => 'southern-vietnam'],
                        'destination_slug' => ['vi' => 'phu-quoc', 'en' => 'phu-quoc'],
                        'image' => 'assets/images/destination/phu-quoc.jpg',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Quảng Ninh', 'en' => 'Quang Ninh'],
                        'subtitle' => ['vi' => 'Miền Bắc', 'en' => 'Northern Vietnam'],
                        'region_slug' => ['vi' => 'mien-bac', 'en' => 'northern-vietnam'],
                        'destination_slug' => ['vi' => 'quang-ninh', 'en' => 'quang-ninh'],
                        'image' => 'assets/images/destination/quang-ninh.jpg',
                    ],
                ],
            ],
            [
                'key' => 'chau-au',
                'label' => [
                    'vi' => 'Châu Âu',
                    'en' => 'Europe',
                ],
                'items' => [
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Pháp', 'en' => 'France'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'phap', 'en' => 'france'],
                        'image' => 'assets/images/tour-temp/eiffel.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Ý', 'en' => 'Italy'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'nuoc-y', 'en' => 'italy'],
                        'image' => 'assets/images/destination/italy.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Thụy Sĩ', 'en' => 'Switzerland'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'thuy-si', 'en' => 'switzerland'],
                        'image' => 'assets/images/destination/thuy-si.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Đức', 'en' => 'Germany'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'duc', 'en' => 'germany'],
                        'image' => 'assets/images/destination/germany.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Hy Lạp', 'en' => 'Greece'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'hy-lap', 'en' => 'greece'],
                        'image' => 'assets/images/destination/hy-lap.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Anh', 'en' => 'England'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'anh', 'en' => 'england'],
                        'image' => 'assets/images/destination/anh.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Tây Ban Nha', 'en' => 'Spain'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => ['vi' => 'chau-au', 'en' => 'europe'],
                        'destination_slug' => ['vi' => 'tay-ban-nha', 'en' => 'spain'],
                        'image' => 'assets/images/destination/spain.jpg',
                    ],
                    
                ],
            ],
            [
                'key' => 'chau-a',
                'label' => [
                    'vi' => 'Châu Á',
                    'en' => 'Asia',
                ],
                'items' => [
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Nhật Bản', 'en' => 'Japan'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'nhat-ban', 'en' => 'japan'],
                        'image' => 'assets/images/destination/nhat-ban.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Thái Lan', 'en' => 'Thailand'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'thai-lan', 'en' => 'thailand'],
                        'image' => 'assets/images/destination/thai-land.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Maldives', 'en' => 'Maldives'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'maldives', 'en' => 'maldives'],
                        'image' => 'assets/images/destination/maldives.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Hàn Quốc', 'en' => 'South Korea'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'han-quoc', 'en' => 'south-korea'],
                        'image' => 'assets/images/destination/han-quoc.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Trung Quốc', 'en' => 'China'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'trung-quoc', 'en' => 'china'],
                        'image' => 'assets/images/destination/china.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Singapore', 'en' => 'Singapore'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'singapore', 'en' => 'singapore'],
                        'image' => 'assets/images/destination/singapore.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Các tiểu Vương quốc Ả rập Thống nhất', 'en' => 'United Arab Emirates'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => ['vi' => 'chau-a', 'en' => 'asia'],
                        'destination_slug' => ['vi' => 'uae', 'en' => 'united-arab-emirates'],
                        'image' => 'assets/images/destination/uae.webp',
                    ],
                    
                ],
            ],
            [
                'key' => 'bac-my',
                'label' => [
                    'vi' => 'Bắc Mỹ',
                    'en' => 'North America',
                ],
                'items' => [
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Mỹ', 'en' => 'United States'],
                        'subtitle' => ['vi' => 'Bắc Mỹ', 'en' => 'North America'],
                        'continent_slug' => ['vi' => 'bac-my', 'en' => 'north-america'],
                        'destination_slug' => ['vi' => 'my', 'en' => 'usa'],
                        'image' => 'assets/images/destination/us.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Canada', 'en' => 'Canada'],
                        'subtitle' => ['vi' => 'Bắc Mỹ', 'en' => 'North America'],
                        'continent_slug' => ['vi' => 'bac-my', 'en' => 'north-america'],
                        'destination_slug' => ['vi' => 'canada', 'en' => 'canada'],
                        'image' => 'assets/images/destination/canada.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Mexico', 'en' => 'Mexico'],
                        'subtitle' => ['vi' => 'Bắc Mỹ', 'en' => 'North America'],
                        'continent_slug' => ['vi' => 'bac-my', 'en' => 'north-america'],
                        'destination_slug' => ['vi' => 'mexico', 'en' => 'mexico'],
                        'image' => 'assets/images/destination/mexico.webp',
                    ]
                ],
            ],
            [
                'key' => 'chau-dai-duong',
                'label' => [
                    'vi' => 'Châu Đại Dương',
                    'en' => 'Oceania',
                ],
                'items' => [
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Úc', 'en' => 'Australia'],
                        'subtitle' => ['vi' => 'Châu Đại Dương', 'en' => 'Oceania'],
                        'continent_slug' => ['vi' => 'chau-dai-duong', 'en' => 'oceania'],
                        'destination_slug' => ['vi' => 'uc', 'en' => 'australia'],
                        'image' => 'assets/images/destination/australia.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'New Zealand', 'en' => 'New Zealand'],
                        'subtitle' => ['vi' => 'Châu Đại Dương', 'en' => 'Oceania'],
                        'continent_slug' => ['vi' => 'chau-dai-duong', 'en' => 'oceania'],
                        'destination_slug' => ['vi' => 'new-zealand', 'en' => 'new-zealand'],
                        'image' => 'assets/images/destination/new-zealand.webp',
                    ]
                ],
            ]
        ];
    }
}
