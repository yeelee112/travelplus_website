<?php
use CodeIgniter\Test\CIUnitTestCase;
use App\Services\TourCollectionService;
final class TourCollectionTest extends CIUnitTestCase
{
    public function testSavingWithoutCollectionsWorksBeforeMigration(): void
    {
        $db = db_connect();
        $this->assertSame(':memory:', $db->database);
        $forge = \Config\Database::forge();
        $forge->dropTable('tour_collection_tours', true);
        $forge->dropTable('tour_collections', true);

        $service = new TourCollectionService();
        $service->sync(1, []);
        $this->assertSame([], $service->selected(1));
        $this->assertSame([], $service->all());
    }

    public function testSelectedCollectionsRequireMigration(): void
    {
        $db = db_connect();
        $this->assertSame(':memory:', $db->database);
        $forge = \Config\Database::forge();
        $forge->dropTable('tour_collection_tours', true);
        $forge->dropTable('tour_collections', true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('cần cập nhật cơ sở dữ liệu bộ sưu tập tour');
        (new TourCollectionService())->sync(1, [1]);
    }

    public function testMigrationAndMultipleMemberships(): void
    {
        $db = db_connect();
        $this->assertSame(':memory:', $db->database);
        $forge = \Config\Database::forge();
        $forge->dropTable('tour_collection_tours', true);
        $forge->dropTable('tour_collections', true);
        $forge->dropTable('tours', true);
        $forge->addField(['id'=>['type'=>'INT'], 'is_autumn'=>['type'=>'INT','default'=>0]]);
        $forge->addKey('id',true); $forge->createTable('tours');
        $db->table('tours')->insertBatch([['id'=>1,'is_autumn'=>1],['id'=>2,'is_autumn'=>0]]);
        require_once APPPATH.'Database/Migrations/2026-09-24-000002_CreateTourCollections.php';
        $migration = new \App\Database\Migrations\CreateTourCollections();
        $migration->up(); $migration->up();
        $this->assertFalse($db->fieldExists('is_autumn','tours'));
        $service = new TourCollectionService();
        $autumn = (int)$service->all()[0]['id'];
        $this->assertSame([$autumn],$service->selected(1));
        $this->assertSame([],$service->selected(2));
        $db->table('tour_collections')->insert(['slug'=>'mua-dong','name_vi'=>'Mùa đông','name_en'=>'Winter','is_active'=>1]);
        $winter = (int)$db->insertID();
        $service->sync(1,[$autumn,$winter,$winter,9999]);
        $this->assertCount(2,$service->selected(1));
        $service->sync(1,[$winter]);
        $this->assertSame([$winter],$service->selected(1));
        $service->sync(1,[]);
        $this->assertSame([],$service->selected(1));
        $service->sync(2,[$autumn]);
        $migration->down();
        $this->assertSame(1,(int)$db->table('tours')->where('id',2)->get()->getRow('is_autumn'));
    }
}
