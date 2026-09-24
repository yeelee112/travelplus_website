<?php
namespace App\Services;
class TourCollectionService
{
    public function all(): array
    {
        $db = db_connect();
        return $db->tableExists('tour_collections') ? $db->table('tour_collections')->orderBy('name_vi','ASC')->get()->getResultArray() : [];
    }
    public function selected(int $tourId): array
    {
        $db = db_connect();
        if (!$db->tableExists('tour_collection_tours')) return [];
        return array_map('intval', array_column($db->table('tour_collection_tours')->select('collection_id')->where('tour_id',$tourId)->get()->getResultArray(),'collection_id'));
    }
    // Called within the tour save transaction, including empty selections.
    public function sync(int $tourId, $values): void
    {
        $db = db_connect();
        if (!$db->tableExists('tour_collection_tours')) throw new \RuntimeException('Cần cập nhật cơ sở dữ liệu bộ sưu tập tour.');
        $ids = array_values(array_unique(array_filter(array_map(static fn($value): int => is_scalar($value) ? (int)$value : 0, is_array($values) ? $values : []), static fn(int $id): bool => $id > 0)));
        if ($ids) $ids = array_column($db->table('tour_collections')->select('id')->whereIn('id',$ids)->get()->getResultArray(),'id');
        $db->table('tour_collection_tours')->where('tour_id',$tourId)->delete();
        foreach ($ids as $id) $db->table('tour_collection_tours')->insert(['collection_id'=>$id,'tour_id'=>$tourId]);
    }
}
