<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table = 'locations';

    public function getMegaMenu($locale = 'vi')
    {
        $db = \Config\Database::connect();

        // Lấy toàn bộ location + translation
        $rows = $db->table('locations l')
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
            if ($row['type'] === 'country') {
                if (isset($menu[$row['parent_id']])) {
                    $menu[$row['parent_id']]['countries'][] = $row;
                }
            }
        }

        return $menu;
    }
}