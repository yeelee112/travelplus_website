<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class LocationModel extends Model
{
    protected $table = 'locations';

    public function findTranslatedLocationBySlug(
        string $locale,
        string $slug,
        ?string $type = null,
        ?int $parentId = null
    ): ?array {
        if (!$this->db->tableExists('locations') || !$this->db->tableExists('location_translations')) {
            return null;
        }

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

        return is_array($row) ? $row : null;
    }

    public function findTranslatedLocationById(string $locale, int $id): ?array
    {
        if (!$this->db->tableExists('locations') || !$this->db->tableExists('location_translations')) {
            return null;
        }

        $row = $this->db->table('locations l')
            ->select('l.id, l.parent_id, l.type, l.code, lt.name, lt.slug')
            ->join('location_translations lt', 'lt.location_id = l.id AND lt.locale = ' . $this->db->escape($locale), 'inner')
            ->where('l.id', $id)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function getMegaMenu(string $locale = 'vi'): array
    {
        if (!$this->db->tableExists('locations') || !$this->db->tableExists('location_translations')) {
            return [];
        }

        $cache = Services::cache();
        $cacheKey = 'mega_menu_' . $locale;
        $cachedMenu = $cache->get($cacheKey);

        if (is_array($cachedMenu)) {
            return $cachedMenu;
        }

        $rows = $this->db->table('locations l')
            ->select('l.id, l.parent_id, l.type, l.code, lt.name, lt.slug')
            ->join('location_translations lt', 'lt.location_id = l.id')
            ->where('lt.locale', $locale)
            ->orderBy('l.parent_id', 'ASC')
            ->get()
            ->getResultArray();

        $menu = [];

        foreach ($rows as $row) {
            if ($row['type'] === 'continent') {
                $menu[$row['id']] = $row;
                $menu[$row['id']]['countries'] = [];
            }
        }

        foreach ($rows as $row) {
            if ($row['type'] === 'country' && isset($menu[$row['parent_id']])) {
                $menu[$row['parent_id']]['countries'][] = $row;
            }
        }

        $cache->save($cacheKey, $menu, 3600);

        return $menu;
    }
}
