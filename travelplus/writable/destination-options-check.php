<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('ENVIRONMENT', 'development');
define('FCPATH', dirname(__DIR__) . '/public/');
require dirname(__DIR__) . '/app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootConsole($paths);
$db = db_connect();
if (!in_array($db->hostname, ['localhost', '127.0.0.1', '::1'], true)) throw new RuntimeException('Local database required');


helper(['url','url_helper_custom']);
$catalog = new App\Services\TourCatalogService();
foreach (['vi','en'] as $locale) {
 $options=$catalog->getCollectionDestinations($locale,'mua-thu','2026-09-01','2026-11-30',$locale==='en'?'domestic':'inbound');
 echo $locale.': '.json_encode($options,JSON_UNESCAPED_UNICODE)."\n";
 foreach ($options as $option) {
  $result=$catalog->searchTours($locale,'','2026-09-01','2026-11-30',100,1,null,false,$locale==='en'?'domestic':'inbound','mua-thu',$option['id']);
  if (!$result['total']) throw new RuntimeException('Option has no matching tours');
 }
}
$db->transBegin();
try {
 $id=(int)$db->table('tour_collections')->where('slug','mua-thu')->get()->getRow('id');
 $all=$catalog->searchTours('vi','','2026-09-01','2026-11-30',100,1,null,false,'inbound');
 foreach ($all['tours'] as $tour) (new App\Services\TourCollectionService())->sync((int)$tour['id'],[$id]);
 $options=$catalog->getCollectionDestinations('vi','mua-thu','2026-09-01','2026-11-30','inbound');
 if (!$options) throw new RuntimeException('No destinations for selected tours');
 $ids=array_column($options,'id');if (count($ids)!==count(array_unique($ids))) throw new RuntimeException('Duplicate destination');
 foreach ($options as $option) if (!$catalog->searchTours('vi','','2026-09-01','2026-11-30',100,1,null,false,'inbound','mua-thu',$option['id'])['total']) throw new RuntimeException('Invalid destination option');
 echo 'PASS positive fixture: '.count($options)." unique destinations and exact ID filters.\n";
 $db->table('tour_collections')->where('id',$id)->update(['is_active'=>0]);
 if ($catalog->getCollectionDestinations('vi','mua-thu','2026-09-01','2026-11-30','inbound')!==[]) throw new RuntimeException('Hidden collection destinations leaked');
 $db->table('tour_collections')->where('id',$id)->update(['is_active'=>1]);
 $db->table('tour_collection_tours')->where('collection_id',$id)->delete();
 if ($catalog->getCollectionDestinations('vi','mua-thu','2026-09-01','2026-11-30','inbound')!==[]) throw new RuntimeException('Unselected destinations leaked');
 echo "PASS option matching, locales, hidden/empty collections.\n";
} finally { $db->transRollback(); }
