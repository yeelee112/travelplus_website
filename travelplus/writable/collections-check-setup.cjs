const fs=require('fs');const p='app/Controllers/Admin/Tours.php';let s=fs.readFileSync(p,'utf8').replace("$formData['collection_ids'] = []; ","$formData['collection_ids'] = [];").replace("$this->deleteTourRelations($db, $tourId);","$this->deleteTourRelations($db, $tourId);\n        if ($db->tableExists('tour_collection_tours')) $db->table('tour_collection_tours')->where('tour_id', $tourId)->delete();");fs.writeFileSync(p,s);
const old=fs.readFileSync('writable/autumn-flag-verify.php','utf8');const prefix=old.slice(0,old.indexOf("require APPPATH"));fs.writeFileSync('writable/collections-verify.php',prefix+`
$before = $db->fieldExists('is_autumn','tours') ? array_map('intval',array_column($db->table('tours')->select('id')->where('is_autumn',1)->get()->getResultArray(),'id')) : [];
require APPPATH.'Database/Migrations/2026-09-24-000002_CreateTourCollections.php';
(new App\\Database\\Migrations\\CreateTourCollections())->up();
helper(['url','url_helper_custom']);
$service = new App\\Services\\TourCollectionService();
$autumn = (int)$db->table('tour_collections')->where('slug','mua-thu')->get()->getRow('id');
foreach ($before as $id) if (!in_array($autumn,$service->selected($id),true)) throw new RuntimeException('Migration lost membership');
echo 'Migration preserved '.count($before)." autumn selections.\\n";
$catalog = new App\\Services\\TourCatalogService();
$all=$catalog->searchTours('vi','','2026-09-01','2026-11-30',100,1,null,false,'inbound');
$id=$all['tours'][0]['id'];
$db->transBegin();
try {
 $db->table('tour_collection_tours')->emptyTable();
 $db->table('tour_collections')->insert(['slug'=>'verify-winter','name_vi'=>'Kiểm tra mùa đông','name_en'=>'Winter check','is_active'=>1]);
 $winter=(int)$db->insertID();
 $post=$db->table('tours')->where('id',$id)->get()->getRowArray();$post['collection_ids']=[$autumn,$winter];
 (new ReflectionMethod(App\\Controllers\\Admin\\Tours::class,'persistTour'))->invoke(new App\\Controllers\\Admin\\Tours(),$db,$id,$post,date('Y-m-d H:i:s'));
 foreach (['mua-thu','verify-winter'] as $slug) {
  $r=$catalog->searchTours('vi','','2026-09-01','2026-11-30',1,99,null,false,'inbound',$slug);
  if ($r['total']!==1 || (int)$r['tours'][0]['id']!==(int)$id || $r['page']!==1) throw new RuntimeException('Membership search failed');
 }
 $db->table('tour_collections')->where('id',$winter)->update(['is_active'=>0]);
 if ($catalog->searchTours('vi','','','',9,1,null,false,'inbound','verify-winter')['total']!==0) throw new RuntimeException('Hidden collection leaked');
 if ($catalog->searchTours('vi','','2099-09-01','2099-11-30',9,1,null,false,'inbound','mua-thu')['total']!==0) throw new RuntimeException('Date filter failed');
 if ($catalog->searchTours('vi','nonexistent-destination-check','','',9,1,null,false,'inbound','mua-thu')['total']!==0) throw new RuntimeException('Destination filter failed');
 $db->table('tours')->where('id',$id)->update(['status'=>'draft']);
 if ($catalog->searchTours('vi','','','',9,1,null,false,'inbound','mua-thu')['total']!==0) throw new RuntimeException('Draft leaked');
 $service->sync($id,[]);if ($service->selected($id)!==[]) throw new RuntimeException('Unselect failed');
 echo "PASS admin multi-selection, collection filtering, pagination, hidden collections, dates, destinations, drafts and unselect.\\n";
}finally{$db->transRollback();echo "Verification data rolled back.\\n";}
`);
