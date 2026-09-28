<?php
use App\Services\TourCatalogService;
use CodeIgniter\Test\CIUnitTestCase;
final class HeroDestinationsTest extends CIUnitTestCase
{
    public function testOnlyPublishedLocalizedToursSupplySuggestions(): void
    {
        $db = \Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        foreach ([
            'CREATE TABLE tours (id INTEGER, status TEXT, tour_type TEXT)',
            'CREATE TABLE tour_destinations (tour_id INTEGER, location_id INTEGER)',
            'CREATE TABLE tour_translations (tour_id INTEGER, locale TEXT)',
            'CREATE TABLE locations (id INTEGER, type TEXT)',
            'CREATE TABLE location_translations (location_id INTEGER, locale TEXT, name TEXT)',
            "INSERT INTO tours VALUES (1,'published','outbound'),(2,'draft','outbound'),(3,'published','inbound')",
            'INSERT INTO tour_destinations VALUES (1,1),(2,2),(3,3)',
            "INSERT INTO tour_translations VALUES (1,'vi'),(1,'en'),(2,'vi'),(3,'vi'),(3,'en')",
            "INSERT INTO locations VALUES (1,'country'),(2,'country'),(3,'province'),(4,'country')",
            "INSERT INTO location_translations VALUES (1,'vi','Pháp'),(1,'en','France'),(2,'vi','Hàn Quốc'),(3,'vi','Hà Nội'),(3,'en','Hanoi'),(4,'vi','Nhật Bản')",
        ] as $sql) $db->query($sql);
        $service = new TourCatalogService();
        (new ReflectionProperty($service, 'db'))->setValue($service, $db);
        $this->assertSame(['Pháp'], array_column($service->getHeroDestinations('vi'), 'name'));
        $this->assertSame(['France','Hanoi'], array_column($service->getHeroDestinations('en'), 'name'));
        $db->table('tours')->where('id', 1)->update(['status'=>'draft']);
        $this->assertSame([], $service->getHeroDestinations('vi'));
        $db->close();
    }
}
