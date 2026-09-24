const fs=require('fs');let s=fs.readFileSync('writable/collections-verify.php','utf8');s=s.slice(0,s.indexOf('$before ='));s+=`
helper(['url','url_helper_custom']);
$catalog = new App\\Services\\TourCatalogService();
foreach (['vi','en'] as $locale) {
 $options=$catalog->getCollectionDestinations($locale,'mua-thu','2026-09-01','2026-11-30',$locale==='en'?'domestic':'inbound');
 echo $locale.': '.json_encode($options,JSON_UNESCAPED_UNICODE)."\\n";
 foreach ($options as $option) {
  $result=$catalog->searchTours($locale,'','2026-09-01','2026-11-30',100,1,null,false,$locale==='en'?'domestic':'inbound','mua-thu',$option['id']);
  if (!$result['total']) throw new RuntimeException('Option has no matching tours');
 }
}
$db->transBegin();
try {
 $id=(int)$db->table('tour_collections')->where('slug','mua-thu')->get()->getRow('id');
 $db->table('tour_collections')->where('id',$id)->update(['is_active'=>0]);
 if ($catalog->getCollectionDestinations('vi','mua-thu','2026-09-01','2026-11-30','inbound')!==[]) throw new RuntimeException('Hidden collection destinations leaked');
 $db->table('tour_collections')->where('id',$id)->update(['is_active'=>1]);
 $db->table('tour_collection_tours')->where('collection_id',$id)->delete();
 if ($catalog->getCollectionDestinations('vi','mua-thu','2026-09-01','2026-11-30','inbound')!==[]) throw new RuntimeException('Unselected destinations leaked');
 echo "PASS option matching, locales, hidden/empty collections.\\n";
} finally { $db->transRollback(); }
`;fs.writeFileSync('writable/destination-options-check.php',s);
