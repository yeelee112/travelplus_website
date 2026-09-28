<?php
use App\Services\TourCatalogService;
use CodeIgniter\Test\CIUnitTestCase;

final class TourCardCampaignTest extends CIUnitTestCase
{
    public function testCampaignsUseActiveCollectionMembership(): void
    {
        $db = \Config\Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $db->query('CREATE TABLE tours (id INTEGER, is_promotion INTEGER, promotion_badge TEXT, promotion_ends_at TEXT, promotion_sort INTEGER)');
        $db->query('CREATE TABLE tour_collections (id INTEGER, slug TEXT, is_active INTEGER)');
        $db->query('CREATE TABLE tour_collection_tours (tour_id INTEGER, collection_id INTEGER)');
        $db->table('tours')->insertBatch([
            ['id'=>1,'is_promotion'=>1,'promotion_badge'=>'Ưu đãi hè','promotion_ends_at'=>null,'promotion_sort'=>0],
            ['id'=>2,'is_promotion'=>0,'promotion_badge'=>'','promotion_ends_at'=>null,'promotion_sort'=>0],
        ]);
        $db->table('tour_collections')->insert(['id'=>1,'slug'=>'mua-thu','is_active'=>1]);
        $db->table('tour_collection_tours')->insert(['tour_id'=>1,'collection_id'=>1]);
        $service = new TourCatalogService();
        (new ReflectionProperty($service, 'db'))->setValue($service, $db);
        $method = new ReflectionMethod($service, 'fetchTourCampaigns');
        $result = $method->invoke($service, [1,2]);
        $this->assertTrue($result[1]['is_autumn']);
        $this->assertFalse($result[2]['is_autumn']);
        $this->assertSame('Ưu đãi hè', $result[1]['promotion_badge']);
        $db->table('tour_collections')->where('id',1)->update(['is_active'=>0]);
        $this->assertFalse($method->invoke($service, [1])[1]['is_autumn']);
        $this->assertSame([], $method->invoke($service, []));
        $db->close();
    }
}
