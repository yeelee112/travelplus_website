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
require APPPATH . 'Database/Migrations/2026-09-24-000001_AddAutumnTourFlag.php';
(new App\Database\Migrations\AddAutumnTourFlag())->up();
cache()->delete('db_field_exists_tours_is_autumn');
echo "Autumn schema ready.\n";
helper(['url', 'url_helper_custom']);
$catalog = new App\Services\TourCatalogService();
$all = $catalog->searchTours('vi', '', '2026-09-01', '2026-11-30', 100, 1, null, false, 'inbound');
if (count($all['tours']) < 2) throw new RuntimeException('Need two existing local tours for transaction check');
$id = $all['tours'][0]['id'];
$db->transBegin();
try {
 $db->table('tours')->update(['is_autumn'=>0]);
 $assert = static function($value, $label) { if (!$value) throw new RuntimeException($label); echo "PASS $label\n"; };
 $assert($catalog->searchTours('vi','','2026-09-01','2026-11-30',9,1,null,false,'inbound',true)['total'] === 0, 'unselected tours excluded');
 $post = $db->table('tours')->where('id',$id)->get()->getRowArray();
 $controller = new App\Controllers\Admin\Tours();
 $persist = new ReflectionMethod($controller, 'persistTour');
 $post['is_autumn']='1';
 $persist->invoke($controller,$db,$id,$post,date('Y-m-d H:i:s'));
 $selected = $catalog->searchTours('vi','','2026-09-01','2026-11-30',1,99,null,false,'inbound',true);
 $assert($selected['total'] === 1 && (int)$selected['tours'][0]['id'] === (int)$id && $selected['page'] === 1, 'admin selection, filtered total and pagination');
 $assert($catalog->searchTours('vi','','','',9,1,null,false,'inbound',true)['total'] === 1, 'collection filter works without dates');
 $assert($catalog->searchTours('vi','no-match-autumn-check','2026-09-01','2026-11-30',9,1,null,false,'inbound',true)['total'] === 0, 'destination filter retained');
 $assert($catalog->searchTours('vi','','2099-09-01','2099-11-30',9,1,null,false,'inbound',true)['total'] === 0, 'departure filter retained');
 $db->table('tours')->where('id',$id)->update(['status'=>'draft']);
 $assert($catalog->searchTours('vi','','','',9,1,null,false,'inbound',true)['total'] === 0, 'draft tour excluded');
 unset($post['is_autumn']);
 $persist->invoke($controller,$db,$id,$post,date('Y-m-d H:i:s'));
 $assert((int)$db->table('tours')->where('id',$id)->get()->getRow('is_autumn') === 0, 'unchecking clears flag');
 $assert($catalog->searchTours('vi','','2026-09-01','2026-11-30',100,1,null,false,'inbound')['total'] === $all['total'], 'ordinary search unchanged');
} finally { $db->transRollback(); echo "Test changes rolled back.\n"; }

