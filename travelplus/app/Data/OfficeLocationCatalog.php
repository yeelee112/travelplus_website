<?php

namespace App\Data;

final class OfficeLocationCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(string $locale = 'vi'): array
    {
        $offices = [
            [
                'title_key' => 'Frontend.footer.office.hcm',
                'address' => [
                    'vi' => '3/30A đường Thích Quảng Đức, Phường Đức Nhuận, TP.HCM',
                    'en' => '3/30A Thich Quang Duc Street, Duc Nhuan Ward, Ho Chi Minh City',
                ],
                'map_url' => 'https://maps.app.goo.gl/PkqKgEp4rthxbNUn9',
                'class' => ' two',
            ],
            [
                'title_key' => 'Frontend.footer.office.hanoi',
                'address' => [
                    'vi' => '47 đường Lê Văn Hưu, Phường Hai Bà Trưng, Hà Nội',
                    'en' => '47 Le Van Huu Street, Hai Ba Trung Ward, Hanoi',
                ],
                'map_url' => 'https://maps.app.goo.gl/9Q5he5PYRqdr1bdEA',
                'class' => '',
            ],
            [
                'title_key' => 'Frontend.footer.office.danang',
                'address' => [
                    'vi' => 'Tầng 4 Tòa nhà Trực thăng Miền Trung, đường Nguyễn Văn Linh, Phường Hòa Cường, Đà Nẵng',
                    'en' => '4th Floor, Mien Trung Helicopter Building, Nguyen Van Linh Street, Hoa Cuong Ward, Da Nang',
                ],
                'map_url' => 'https://maps.app.goo.gl/FFjiLtqRNWxAjvASA',
                'class' => ' three',
            ],
        ];

        $resolved = [];

        foreach ($offices as $office) {
            $resolved[] = [
                'title' => lang($office['title_key'], [], $locale),
                'address' => $office['address'][$locale] ?? $office['address']['vi'],
                'map_url' => $office['map_url'],
                'class' => $office['class'],
            ];
        }

        return $resolved;
    }
}
