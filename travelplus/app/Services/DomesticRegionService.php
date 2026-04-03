<?php

namespace App\Services;

use Config\Database;

class DomesticRegionService
{
    private array $regionDefinitions = [
        'vi' => [
            'north' => ['name' => 'Miền Bắc', 'slug' => 'mien-bac'],
            'central' => ['name' => 'Miền Trung', 'slug' => 'mien-trung'],
            'south' => ['name' => 'Miền Nam', 'slug' => 'mien-nam'],
            'mekong' => ['name' => 'Miền Tây', 'slug' => 'mien-tay'],
            'vietnam' => ['name' => 'Xuyên Việt', 'slug' => 'xuyen-viet'],
        ],
        'en' => [
            'north' => ['name' => 'Northern Vietnam', 'slug' => 'northern-vietnam'],
            'central' => ['name' => 'Central Vietnam', 'slug' => 'central-vietnam'],
            'south' => ['name' => 'Southern Vietnam', 'slug' => 'southern-vietnam'],
            'mekong' => ['name' => 'Mekong Delta', 'slug' => 'mekong-delta'],
            'vietnam' => ['name' => 'Vietnam Tours', 'slug' => 'vietnam-tours'],
        ],
    ];

    private array $provinceCodeMap = [
        'north' => ['HN', 'HP', 'QN', 'NB', 'LC', 'SP', 'BG', 'BN', 'TB', 'ND', 'HD', 'HY', 'VP', 'PT', 'TN', 'BC', 'CB', 'DB', 'HG', 'LS', 'YT', 'TBH'],
        'central' => ['TH', 'NA', 'HT', 'QB', 'QT', 'HUE', 'DN', 'QN', 'QNG', 'BD', 'PY', 'KH', 'NT', 'GL', 'KT', 'DL', 'DNO'],
        'south' => ['HCM', 'SG', 'BDG', 'DNA', 'VT', 'TNH', 'BPH', 'LDG', 'AGI'],
        'mekong' => ['CT', 'AG', 'DT', 'VL', 'TV', 'BTR', 'ST', 'BL', 'CM', 'KG', 'HG', 'TG', 'LA'],
        'vietnam' => ['AVN']
    ];

    public function getMenu(string $locale = 'vi'): array
    {
        $regions = $this->baseRegions($locale);

        foreach ($this->getVietnamProvinces($locale) as $province) {
            $regionKey = $this->resolveRegionKeyByProvinceCode((string) ($province['code'] ?? ''));

            if ($regionKey === null || !isset($regions[$regionKey])) {
                continue;
            }

            $regions[$regionKey]['provinces'][] = [
                'id' => (int) $province['id'],
                'name' => (string) $province['name'],
                'slug' => (string) $province['slug'],
                'code' => (string) $province['code'],
                'link' => localized_url('tour-trong-nuoc/' . $regions[$regionKey]['slug'] . '/' . $province['slug']),
            ];
        }

        foreach ($regions as $key => $region) {
            $regions[$key]['link'] = localized_url('tour-trong-nuoc/' . $region['slug']);
        }

        return $regions;
    }

    public function getRegionBySlug(string $locale, string $slug): ?array
    {
        foreach ($this->baseRegions($locale) as $key => $region) {
            if ($region['slug'] === $slug) {
                $region['key'] = $key;
                $region['provinces'] = $this->getMenu($locale)[$key]['provinces'] ?? [];

                return $region;
            }
        }

        return null;
    }

    public function getProvinceBySlug(string $locale, string $regionSlug, string $provinceSlug): ?array
    {
        $region = $this->getRegionBySlug($locale, $regionSlug);

        if ($region === null) {
            return null;
        }

        foreach ($region['provinces'] as $province) {
            if (($province['slug'] ?? '') === $provinceSlug) {
                return $province;
            }
        }

        return null;
    }

    public function getRegionByProvinceId(string $locale, int $provinceId): ?array
    {
        foreach ($this->getMenu($locale) as $key => $region) {
            foreach ($region['provinces'] as $province) {
                if ((int) ($province['id'] ?? 0) === $provinceId) {
                    $region['key'] = $key;

                    return $region;
                }
            }
        }

        return null;
    }

    public function translatePathSegments(array $segments, string $fromLocale, string $toLocale): array
    {
        if (($segments[0] ?? '') !== 'tour-trong-nuoc') {
            return $segments;
        }

        $translated = ['tour-trong-nuoc'];
        $regionSlug = $segments[1] ?? null;

        if ($regionSlug === null) {
            return $translated;
        }

        $fromRegion = $this->getRegionBySlug($fromLocale, $regionSlug);
        if ($fromRegion === null) {
            return $segments;
        }

        $toRegion = $this->baseRegions($toLocale)[$fromRegion['key'] ?? ''] ?? null;
        if ($toRegion === null) {
            return $segments;
        }

        $translated[] = $toRegion['slug'];

        $provinceSlug = $segments[2] ?? null;
        if ($provinceSlug === null) {
            return $translated;
        }

        $province = $this->getProvinceBySlug($fromLocale, $regionSlug, $provinceSlug);
        if ($province === null) {
            return $segments;
        }

        foreach (($this->getMenu($toLocale)[$fromRegion['key']]['provinces'] ?? []) as $item) {
            if ((int) ($item['id'] ?? 0) === (int) $province['id']) {
                $translated[] = $item['slug'];
                break;
            }
        }

        return $translated;
    }

    private function baseRegions(string $locale): array
    {
        $locale = $locale === 'en' ? 'en' : 'vi';
        $regions = [];

        foreach ($this->regionDefinitions[$locale] as $key => $region) {
            $regions[$key] = $region + ['key' => $key, 'provinces' => []];
        }

        return $regions;
    }

    private function resolveRegionKeyByProvinceCode(string $code): ?string
    {
        foreach ($this->provinceCodeMap as $regionKey => $codes) {
            if (in_array(strtoupper($code), $codes, true)) {
                return $regionKey;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getVietnamProvinces(string $locale): array
    {
        $db = Database::connect();

        return $db->table('locations l')
            ->select('l.id, l.code, lt.name, lt.slug')
            ->join('locations parent', 'parent.id = l.parent_id AND parent.type = "country" AND parent.code = "VN"', 'inner')
            ->join('location_translations lt', 'lt.location_id = l.id AND lt.locale = ' . $db->escape($locale), 'inner')
            ->where('l.type', 'province')
            ->orderBy('lt.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
