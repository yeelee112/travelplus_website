<?php

use App\Controllers\Admin\Tours;
use App\Services\TourCatalogService;
use CodeIgniter\Test\CIUnitTestCase;

final class TourInboundVisibilityTest extends CIUnitTestCase
{
    private $fixtureDb;
    private TourCatalogService $catalog;
    private array $savedStatics = [];

    protected function setUp(): void
    {
        parent::setUp();
        helper('url');
        $this->fixtureDb = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->fixtureDb->query('CREATE TABLE tours (id INTEGER PRIMARY KEY, tour_type TEXT, show_on_inbound INTEGER DEFAULT 0, status TEXT, duration_days INTEGER, duration_nights INTEGER, thumbnail TEXT, is_featured INTEGER, base_price INTEGER, departure_location_id INTEGER, updated_at TEXT, created_at TEXT)');
        $this->fixtureDb->query('CREATE TABLE tour_translations (tour_id INTEGER, locale TEXT, name TEXT, slug TEXT)');
        $this->fixtureDb->query('CREATE TABLE tour_departures (id INTEGER, tour_id INTEGER, status TEXT, departure_date TEXT, price INTEGER)');
        $this->fixtureDb->query('CREATE TABLE tour_destinations (id INTEGER, tour_id INTEGER, location_id INTEGER)');
        $this->fixtureDb->query('CREATE TABLE locations (id INTEGER, parent_id INTEGER, type TEXT, code TEXT)');
        $this->fixtureDb->query('CREATE TABLE location_translations (location_id INTEGER, locale TEXT, name TEXT, slug TEXT)');
        $this->fixtureDb->query('CREATE TABLE tour_media (id INTEGER, tour_id INTEGER, type TEXT, file_path TEXT, alt_text TEXT, sort_order INTEGER)');
        foreach ([1 => ['domestic', 0, 'published'], 2 => ['domestic', 1, 'published'], 3 => ['inbound', 0, 'published'], 4 => ['outbound', 0, 'published'], 5 => ['domestic', 1, 'draft']] as $id => [$type, $shared, $status]) {
            $this->fixtureDb->table('tours')->insert(['id' => $id, 'tour_type' => $type, 'show_on_inbound' => $shared, 'status' => $status, 'is_featured' => 1, 'base_price' => 2500000]);
            foreach (['vi', 'en'] as $locale) {
                $this->fixtureDb->table('tour_translations')->insert(['tour_id' => $id, 'locale' => $locale, 'name' => 'Journey ' . $id, 'slug' => 'shared-test-' . $id]);
            }
        }
        $this->catalog = new TourCatalogService();
        (new ReflectionProperty($this->catalog, 'db'))->setValue($this->catalog, $this->fixtureDb);
        foreach (['tableExistsCache', 'fieldExistsCache', 'tourCatalogSchemaReady'] as $name) {
            $this->savedStatics[$name] = (new ReflectionProperty(TourCatalogService::class, $name))->getValue();
        }
        $tables = array_fill_keys(['tours', 'tour_translations', 'tour_departures', 'tour_destinations', 'locations', 'location_translations', 'tour_media'], true);
        $tables += array_fill_keys(['tour_collections', 'tour_collection_tours', 'tour_inclusions', 'tour_itinerary_days', 'tour_faqs', 'tour_reviews'], false);
        (new ReflectionProperty(TourCatalogService::class, 'tableExistsCache'))->setValue(null, $tables);
        $fields = array_fill_keys(['show_on_inbound', 'is_featured', 'created_at', 'is_promotion', 'promotion_badge', 'promotion_ends_at', 'promotion_sort'], false);
        $cache = [];
        foreach ($fields as $field => $_) $cache['tours.' . $field] = $this->fixtureDb->fieldExists($field, 'tours');
        // Match the service's schema cache keys without using the persistent application cache.
        (new ReflectionProperty(TourCatalogService::class, 'fieldExistsCache'))->setValue(null, $cache);
        (new ReflectionProperty(TourCatalogService::class, 'tourCatalogSchemaReady'))->setValue(null, true);
    }

    protected function tearDown(): void
    {
        foreach ($this->savedStatics as $name => $value) (new ReflectionProperty(TourCatalogService::class, $name))->setValue(null, $value);
        $this->fixtureDb->close();
        parent::tearDown();
    }

    public function testSharedTourAppearsInBothMarketsWithMatchingLinks(): void
    {
        $inbound = $this->catalog->getPagedTours('en', 20, 1, 'inbound');
        $this->assertSame(2, $inbound['total']);
        $this->assertEqualsCanonicalizing([2, 3], array_column($inbound['tours'], 'id'));
        $shared = array_values(array_filter($inbound['tours'], static fn($tour) => $tour['id'] === 2))[0];
        $this->assertStringContainsString('/inbound-tours/', $shared['link']);
        $this->assertSame('inbound', $shared['tour_type']);
        $domestic = $this->catalog->getPagedTours('en', 20, 1, 'domestic');
        $this->assertEqualsCanonicalizing([1, 2], array_column($domestic['tours'], 'id'));
        foreach ($domestic['tours'] as $tour) $this->assertSame('domestic', $tour['tour_type']);
        $this->assertSame('domestic', $this->fixtureDb->table('tours')->where('id', 2)->get()->getRow('tour_type'));
    }

    public function testEnglishSearchIncludesSharedTourAndRespectsKeyword(): void
    {
        $result = $this->catalog->searchTours('en', 'Journey 2', '', '', 20, 1, null, false, 'domestic');
        $this->assertSame(1, $result['total']);
        $this->assertSame(2, $result['tours'][0]['id']);
        $this->assertSame('inbound', $result['tours'][0]['tour_type']);
        $vi = $this->catalog->getPagedTours('vi', 20, 1, null, [], false, 'inbound');
        $this->assertEqualsCanonicalizing([1, 2, 4], array_column($vi['tours'], 'id'));
    }

    public function testDisablingSharingRemovesTourFromInbound(): void
    {
        $this->fixtureDb->table('tours')->where('id', 2)->update(['show_on_inbound' => 0]);
        $result = $this->catalog->getPagedTours('en', 20, 1, 'inbound');
        $this->assertSame([3], array_column($result['tours'], 'id'));
    }

    public function testFeaturedAndDetailUseSharedTour(): void
    {
        $featured = $this->catalog->getFeaturedTours('en', 20, 'inbound');
        $this->assertEqualsCanonicalizing([2, 3], array_column($featured, 'id'));
        $detail = $this->catalog->findTourBySlug('en', 'shared-test-2', 'inbound', 'journeys');
        $this->assertSame(2, $detail['id']);
        $this->assertSame('inbound', $detail['tour_type']);
        $this->assertSame(2500000.0, $detail['price']['amount']);
        $this->assertStringContainsString('/inbound-tours/', $detail['link']);
        $this->assertNull($this->catalog->findTourBySlug('en', 'shared-test-1', 'inbound', 'journeys'));
    }

    public function testSharedSlugsCannotCollideWithInboundInEitherDirection(): void
    {
        $admin = new Tours();
        $validate = new ReflectionMethod($admin, 'validateUniqueTourSlugs');
        $post = ['tour_type' => 'domestic', 'show_on_inbound' => 1, 'slug_en' => 'shared-test-3'];
        $this->assertNotEmpty($validate->invoke($admin, $this->fixtureDb, $post, 2));
        $post['show_on_inbound'] = 0;
        $this->assertSame([], $validate->invoke($admin, $this->fixtureDb, $post, 2));
        $this->assertNotEmpty($validate->invoke($admin, $this->fixtureDb, ['tour_type' => 'inbound', 'slug_en' => 'shared-test-2'], 3));
    }

    public function testAdminPersistsToggleAndClearsItForOtherTourTypes(): void
    {
        $admin = new Tours();
        $persist = new ReflectionMethod($admin, 'persistTour');
        $post = ['category_id' => 1, 'departure_location_id' => 1, 'tour_type' => 'domestic', 'duration_days' => 3, 'duration_nights' => 2, 'show_on_inbound' => '1'];
        $persist->invoke($admin, $this->fixtureDb, 2, $post, date('Y-m-d H:i:s'));
        $this->assertSame(1, (int) $this->fixtureDb->table('tours')->where('id', 2)->get()->getRow('show_on_inbound'));
        $post['show_on_inbound'] = '0';
        $persist->invoke($admin, $this->fixtureDb, 2, $post, date('Y-m-d H:i:s'));
        $this->assertSame(0, (int) $this->fixtureDb->table('tours')->where('id', 2)->get()->getRow('show_on_inbound'));
        $post['show_on_inbound'] = '1';
        $post['tour_type'] = 'outbound';
        $persist->invoke($admin, $this->fixtureDb, 2, $post, date('Y-m-d H:i:s'));
        $this->assertSame(0, (int) $this->fixtureDb->table('tours')->where('id', 2)->get()->getRow('show_on_inbound'));
    }

    public function testMigrationDefaultsExistingToursToNotSharedAndCanRunTwice(): void
    {
        require_once APPPATH . 'Database/Migrations/2026-10-08-000001_AddTourInboundVisibility.php';
        $forge = \Config\Database::forge($this->fixtureDb);
        $migration = new \App\Database\Migrations\AddTourInboundVisibility($forge);
        $migration->down();
        $migration->up();
        $migration->up();
        $this->assertTrue($this->fixtureDb->fieldExists('show_on_inbound', 'tours'));
        $this->assertSame(0, $this->fixtureDb->table('tours')->where('show_on_inbound !=', 0)->countAllResults());
        $this->assertSame(5, $this->fixtureDb->table('tours')->countAllResults());
    }
}
