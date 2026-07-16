<?php

namespace App\Data;

use App\Services\WebsiteSettingsService;

final class OfficeLocationCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(string $locale = 'vi'): array
    {
        $settings = new WebsiteSettingsService();
        $offices = [
            [
                'title_key' => 'Frontend.footer.office.hcm',
                'address' => [
                    'vi' => $settings->get('office_hcm_address_vi'),
                    'en' => $settings->get('office_hcm_address_en'),
                ],
                'map_url' => $settings->get('office_hcm_map_url'),
                'class' => ' two',
            ],
            [
                'title_key' => 'Frontend.footer.office.hanoi',
                'address' => [
                    'vi' => $settings->get('office_hanoi_address_vi'),
                    'en' => $settings->get('office_hanoi_address_en'),
                ],
                'map_url' => $settings->get('office_hanoi_map_url'),
                'class' => '',
            ],
            [
                'title_key' => 'Frontend.footer.office.danang',
                'address' => [
                    'vi' => $settings->get('office_danang_address_vi'),
                    'en' => $settings->get('office_danang_address_en'),
                ],
                'map_url' => $settings->get('office_danang_map_url'),
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
