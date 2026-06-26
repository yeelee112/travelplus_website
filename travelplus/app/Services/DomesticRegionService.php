<?php

namespace App\Services;

use Config\Database;
use Throwable;

class DomesticRegionService
{
    private const MENU_CACHE_TTL = 3600;

    private static array $menuCache = [];

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
        'north' => [
            'VN-01', // Ha Noi
            'VN-04', // Cao Bang
            'VN-08', // Tuyen Quang
            'VN-11', // Dien Bien
            'VN-12', // Lai Chau
            'VN-14', // Son La
            'VN-15', // Lao Cai
            'VN-19', // Thai Nguyen
            'VN-20', // Lang Son
            'VN-22', // Quang Ninh
            'VN-24', // Bac Ninh
            'VN-25', // Phu Tho
            'VN-31', // Hai Phong
            'VN-33', // Hung Yen
            'VN-37', // Ninh Binh
        ],
        'central' => [
            'VN-38', // Thanh Hoa
            'VN-40', // Nghe An
            'VN-42', // Ha Tinh
            'VN-44', // Quang Tri
            'VN-46', // Hue
            'VN-48', // Da Nang
            'VN-51', // Quang Ngai
            'VN-52', // Gia Lai
            'VN-56', // Khanh Hoa
            'VN-66', // Dak Lak
            'VN-68', // Lam Dong
        ],
        'south' => [
            'VN-75', // Dong Nai
            'VN-79', // Ho Chi Minh City
            'VN-80', // Tay Ninh
        ],
        'mekong' => [
            'VN-82', // Dong Thap
            'VN-86', // Vinh Long
            'VN-91', // An Giang
            'VN-92', // Can Tho
            'VN-96', // Ca Mau
        ],
        'vietnam' => ['VN-ALL'],
    ];

    private array $provinceCodeAliases = [
        'AVN' => 'VN-ALL',

        'HN' => 'VN-01',
        'CB' => 'VN-04',
        'TQ' => 'VN-08',
        'HG' => 'VN-08',
        'DB' => 'VN-11',
        'LCH' => 'VN-12',
        'LC' => 'VN-15',
        'LCA' => 'VN-15',
        'SP' => 'VN-15',
        'YB' => 'VN-15',
        'SL' => 'VN-14',
        'TN' => 'VN-19',
        'TNG' => 'VN-19',
        'BC' => 'VN-19',
        'BK' => 'VN-19',
        'LS' => 'VN-20',
        'QNH' => 'VN-22',
        'QNI' => 'VN-22',
        'BN' => 'VN-24',
        'BG' => 'VN-24',
        'PT' => 'VN-25',
        'VP' => 'VN-25',
        'HB' => 'VN-25',
        'HP' => 'VN-31',
        'HD' => 'VN-31',
        'HY' => 'VN-33',
        'TB' => 'VN-33',
        'TBH' => 'VN-33',
        'NB' => 'VN-37',
        'ND' => 'VN-37',
        'HNA' => 'VN-37',

        'TH' => 'VN-38',
        'NA' => 'VN-40',
        'HT' => 'VN-42',
        'QT' => 'VN-44',
        'QB' => 'VN-44',
        'HUE' => 'VN-46',
        'TTH' => 'VN-46',
        'DN' => 'VN-48',
        'DNG' => 'VN-48',
        'QNA' => 'VN-48',
        'QNG' => 'VN-51',
        'KT' => 'VN-51',
        'GL' => 'VN-52',
        'BD' => 'VN-52',
        'BDI' => 'VN-52',
        'KH' => 'VN-56',
        'NT' => 'VN-56',
        'DL' => 'VN-66',
        'DDL' => 'VN-66',
        'PY' => 'VN-66',
        'LDG' => 'VN-68',
        'LD' => 'VN-68',
        'DNO' => 'VN-68',
        'BTH' => 'VN-68',

        'DNA' => 'VN-75',
        'BPH' => 'VN-75',
        'HCM' => 'VN-79',
        'SG' => 'VN-79',
        'BDG' => 'VN-79',
        'BRVT' => 'VN-79',
        'VT' => 'VN-79',
        'TNH' => 'VN-80',
        'LA' => 'VN-80',

        'DT' => 'VN-82',
        'TG' => 'VN-82',
        'VL' => 'VN-86',
        'VLG' => 'VN-86',
        'BTR' => 'VN-86',
        'TV' => 'VN-86',
        'AG' => 'VN-91',
        'AGI' => 'VN-91',
        'KG' => 'VN-91',
        'CT' => 'VN-92',
        'CTH' => 'VN-92',
        'HGI' => 'VN-92',
        'ST' => 'VN-92',
        'CM' => 'VN-96',
        'BL' => 'VN-96',
    ];

    public function getMenu(string $locale = 'vi'): array
    {
        $locale = $locale === 'en' ? 'en' : 'vi';
        if (isset(self::$menuCache[$locale])) {
            return self::$menuCache[$locale];
        }

        $regions = $this->baseRegions($locale);

        if (DatabaseAvailabilityService::isUnavailable()) {
            foreach ($regions as $key => $region) {
                $regions[$key]['link'] = localized_url('tour-trong-nuoc/' . $region['slug']);
            }

            self::$menuCache[$locale] = $regions;

            return $regions;
        }

        $cacheKey = 'domestic_region_menu_' . $locale;
        try {
            $cached = cache()->get($cacheKey);
            if (is_array($cached)) {
                self::$menuCache[$locale] = $cached;

                return $cached;
            }
        } catch (Throwable) {
        }

        try {
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
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Domestic region menu load failed');
        }

        foreach ($regions as $key => $region) {
            $regions[$key]['link'] = localized_url('tour-trong-nuoc/' . $region['slug']);
        }

        try {
            cache()->save($cacheKey, $regions, self::MENU_CACHE_TTL);
        } catch (Throwable) {
        }

        self::$menuCache[$locale] = $regions;

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
        $code = strtoupper($code);
        $code = $this->provinceCodeAliases[$code] ?? $code;

        foreach ($this->provinceCodeMap as $regionKey => $codes) {
            if (in_array($code, $codes, true)) {
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
