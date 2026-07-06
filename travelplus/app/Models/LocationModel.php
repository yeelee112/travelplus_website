<?php

namespace App\Models;

use App\Services\DatabaseAvailabilityService;
use CodeIgniter\Model;
use Throwable;

class LocationModel extends Model
{
    protected $table = 'locations';
    private const MENU_CACHE_TTL = 300;
    private const MENU_CACHE_VERSION = 5;
    private static array $megaMenuCache = [];

    public function findTranslatedLocationBySlug(
        string $locale,
        string $slug,
        ?string $type = null,
        ?int $parentId = null
    ): ?array {
        if (DatabaseAvailabilityService::isUnavailable()) {
            return null;
        }

        try {
            $builder = $this->db->table('locations l')
                ->select('l.id, l.parent_id, l.type, l.code, lt.name, lt.slug')
                ->join('location_translations lt', 'lt.location_id = l.id AND lt.locale = ' . $this->db->escape($locale), 'inner')
                ->where('lt.slug', $slug);

            if ($type !== null) {
                $builder->where('l.type', $type);
            }

            if ($parentId !== null) {
                $builder->where('l.parent_id', $parentId);
            }

            $row = $builder->get()->getRowArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Location slug lookup failed');

            return null;
        }

        return is_array($row) ? $row : null;
    }

    public function findTranslatedLocationById(string $locale, int $id): ?array
    {
        if (DatabaseAvailabilityService::isUnavailable()) {
            return null;
        }

        try {
            $row = $this->db->table('locations l')
                ->select('l.id, l.parent_id, l.type, l.code, lt.name, lt.slug')
                ->join('location_translations lt', 'lt.location_id = l.id AND lt.locale = ' . $this->db->escape($locale), 'inner')
                ->where('l.id', $id)
                ->get()
                ->getRowArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Location id lookup failed');

            return null;
        }

        return is_array($row) ? $row : null;
    }

    public function getMegaMenu(string $locale = 'vi'): array
    {
        $locale = $locale === 'en' ? 'en' : 'vi';

        if (isset(self::$megaMenuCache[$locale])) {
            return self::$megaMenuCache[$locale];
        }

        $cacheKey = 'location_mega_menu_v' . self::MENU_CACHE_VERSION . '_' . $locale;
        $cached = cache()->get($cacheKey);
        if (is_array($cached)) {
            self::$megaMenuCache[$locale] = $cached;

            return $cached;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            self::$megaMenuCache[$locale] = [];

            return [];
        }

        try {
            $primaryLocale = $this->db->escape($locale);
            $fallbackLocale = $this->db->escape($locale === 'en' ? 'vi' : 'en');

            $rows = $this->db->table('locations l')
                ->select(
                    'l.id, l.parent_id, l.type, l.code,' .
                    'COALESCE(NULLIF(primary_lt.name, ""), fallback_lt.name, "") AS name,' .
                    'COALESCE(NULLIF(primary_lt.slug, ""), fallback_lt.slug, "") AS slug',
                    false
                )
                ->join('location_translations primary_lt', 'primary_lt.location_id = l.id AND primary_lt.locale = ' . $primaryLocale, 'left')
                ->join('location_translations fallback_lt', 'fallback_lt.location_id = l.id AND fallback_lt.locale = ' . $fallbackLocale, 'left')
                ->whereIn('l.type', ['continent', 'country'])
                ->groupStart()
                    ->where('primary_lt.id IS NOT NULL', null, false)
                    ->orWhere('fallback_lt.id IS NOT NULL', null, false)
                ->groupEnd()
                ->orderBy('l.parent_id', 'ASC')
                ->orderBy('name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Mega menu load failed');
            self::$megaMenuCache[$locale] = [];

            return [];
        }

        $menu = [];

        foreach ($rows as $row) {
            if ($row['type'] === 'continent') {
                $menu[$row['id']] = $row;
                $menu[$row['id']]['countries'] = [];
            }
        }

        foreach ($rows as $row) {
            if ($row['type'] === 'country' && isset($menu[$row['parent_id']])) {
                if (
                    $this->isDomesticCountryForOutboundMenu($row)
                    || $this->isDuplicateContinentCountryForOutboundMenu($row, $menu[$row['parent_id']])
                ) {
                    continue;
                }

                $menu[$row['parent_id']]['countries'][] = $row;
            }
        }

        $menu = array_filter(
            $menu,
            static fn (array $continent): bool => ! empty($continent['countries'])
        );

        cache()->save($cacheKey, $menu, self::MENU_CACHE_TTL);
        self::$megaMenuCache[$locale] = $menu;

        return $menu;
    }

    private function isDomesticCountryForOutboundMenu(array $row): bool
    {
        $code = strtolower(trim((string) ($row['code'] ?? '')));
        $slug = strtolower(trim((string) ($row['slug'] ?? '')));
        $name = mb_strtolower(trim((string) ($row['name'] ?? '')), 'UTF-8');

        return $code === 'vn'
            || in_array($slug, ['viet-nam', 'vietnam'], true)
            || in_array($name, ['việt nam', 'vietnam'], true);
    }

    private function isDuplicateContinentCountryForOutboundMenu(array $row, array $continent): bool
    {
        $countrySlug = strtolower(trim((string) ($row['slug'] ?? '')));
        $countryName = mb_strtolower(trim((string) ($row['name'] ?? '')), 'UTF-8');
        $continentSlug = strtolower(trim((string) ($continent['slug'] ?? '')));
        $continentName = mb_strtolower(trim((string) ($continent['name'] ?? '')), 'UTF-8');

        return ($countrySlug !== '' && $countrySlug === $continentSlug)
            || ($countryName !== '' && $countryName === $continentName);
    }
}
