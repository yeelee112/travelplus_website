<?php
namespace App\Services;
class TourCollectionService
{
    public const SETUP_MESSAGE = 'Chưa cập nhật cơ sở dữ liệu bộ sưu tập. Vui lòng import file database/sql/2026-09-24_create_tour_collections.sql vào cơ sở dữ liệu trên hosting rồi thử lại.';

    public function isReady(): bool
    {
        $db = db_connect();
        return $db->tableExists('tour_collections') && $db->tableExists('tour_collection_tours');
    }
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
        $ids = array_values(array_unique(array_filter(array_map(static fn($value): int => is_scalar($value) ? (int)$value : 0, is_array($values) ? $values : []), static fn(int $id): bool => $id > 0)));
        if (!$db->tableExists('tour_collection_tours') || !$db->tableExists('tour_collections')) {
            // Older installations can still save tours without collection memberships.
            if ($ids === []) return;
            throw new \DomainException('Chưa thể lưu bộ sưu tập: cần cập nhật cơ sở dữ liệu bộ sưu tập tour.');
        }
        if ($ids) $ids = array_column($db->table('tour_collections')->select('id')->whereIn('id',$ids)->get()->getResultArray(),'id');
        $db->table('tour_collection_tours')->where('tour_id',$tourId)->delete();
        foreach ($ids as $id) $db->table('tour_collection_tours')->insert(['collection_id'=>$id,'tour_id'=>$tourId]);
    }
}
