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
                        'region_slug' => 'mien-trung',
                        'destination_slug' => 'da-nang',
                        'image' => 'assets/images/destination/da-nang.jpg',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Hà Nội', 'en' => 'Hanoi'],
                        'subtitle' => ['vi' => 'Miền Bắc', 'en' => 'Northern Vietnam'],
                        'region_slug' => 'mien-bac',
                        'destination_slug' => 'ha-noi',
                        'image' => 'assets/images/destination/ha-noi.webp',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Sa Pa', 'en' => 'Sapa'],
                        'subtitle' => ['vi' => 'Miền Bắc', 'en' => 'Northern Vietnam'],
                        'region_slug' => 'mien-bac',
                        'destination_slug' => 'sa-pa',
                        'image' => 'assets/images/destination/sa-pa.jpg',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Nha Trang', 'en' => 'Nha Trang'],
                        'subtitle' => ['vi' => 'Miền Trung', 'en' => 'Central Vietnam'],
                        'region_slug' => 'mien-trung',
                        'destination_slug' => 'nha-trang',
                        'image' => 'assets/images/destination/nha-trang.png',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Đà Lạt', 'en' => 'Dalat'],
                        'subtitle' => ['vi' => 'Miền Nam', 'en' => 'Southern Vietnam'],
                        'region_slug' => 'mien-nam',
                        'destination_slug' => 'da-lat',
                        'image' => 'assets/images/destination/da-lat.png',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Phú Quốc', 'en' => 'Phu Quoc'],
                        'subtitle' => ['vi' => 'Miền Nam', 'en' => 'Southern Vietnam'],
                        'region_slug' => 'mien-nam',
                        'destination_slug' => 'phu-quoc',
                        'image' => 'assets/images/destination/phu-quoc.jpg',
                    ],
                    [
                        'kind' => 'domestic_province',
                        'title' => ['vi' => 'Quảng Ninh', 'en' => 'Quang Ninh'],
                        'subtitle' => ['vi' => 'Miền Bắc', 'en' => 'Northern Vietnam'],
                        'region_slug' => 'mien-bac',
                        'destination_slug' => 'quang-ninh',
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
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'phap',
                        'image' => 'assets/images/tour-temp/eiffel.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Ý', 'en' => 'Italy'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'nuoc-y',
                        'image' => 'assets/images/destination/italy.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Thụy Sĩ', 'en' => 'Switzerland'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'thuy-si',
                        'image' => 'assets/images/destination/thuy-si.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Đức', 'en' => 'Germany'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'duc',
                        'image' => 'assets/images/destination/germany.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Hy Lạp', 'en' => 'Greece'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'hy-lap',
                        'image' => 'assets/images/destination/hy-lap.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Anh', 'en' => 'England'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'anh',
                        'image' => 'assets/images/destination/anh.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Tây Ban Nha', 'en' => 'Spain'],
                        'subtitle' => ['vi' => 'Châu Âu', 'en' => 'Europe'],
                        'continent_slug' => 'chau-au',
                        'destination_slug' => 'tay-ban-nha',
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
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'nhat-ban',
                        'image' => 'assets/images/destination/nhat-ban.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Thái Lan', 'en' => 'Thailand'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'thai-lan',
                        'image' => 'assets/images/destination/thai-land.webp',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Maldives', 'en' => 'Maldives'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'maldives',
                        'image' => 'assets/images/destination/maldives.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Hàn Quốc', 'en' => 'South Korea'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'han-quoc',
                        'image' => 'assets/images/destination/han-quoc.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Trung Quốc', 'en' => 'China'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'trung-quoc',
                        'image' => 'assets/images/destination/china.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Singapore', 'en' => 'Singapore'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'singapore',
                        'image' => 'assets/images/destination/singapore.jpg',
                    ],
                    [
                        'kind' => 'outbound_country',
                        'title' => ['vi' => 'Các tiểu Vương quốc Ả rập Thống nhất', 'en' => 'United Arab Emirates'],
                        'subtitle' => ['vi' => 'Châu Á', 'en' => 'Asia'],
                        'continent_slug' => 'chau-a',
                        'destination_slug' => 'uae',
                        'image' => 'assets/images/destination/uae.png',
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
                        'continent_slug' => 'bac-my',
                        'destination_slug' => 'my',
                        'image' => 'assets/images/gallery-3.jpg',
                    ],
                ],
            ],
        ];
    }
}
