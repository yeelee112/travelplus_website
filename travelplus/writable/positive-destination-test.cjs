const fs=require('fs');const p='writable/destination-options-check.php';let s=fs.readFileSync(p,'utf8').replace(" $db->table('tour_collections')->where('id',$id)->update(['is_active'=>0]);",` $all=$catalog->searchTours('vi','','2026-09-01','2026-11-30',100,1,null,false,'inbound');
 foreach ($all['tours'] as $tour) (new App\\Services\\TourCollectionService())->sync((int)$tour['id'],[$id]);
 $options=$catalog->getCollectionDestinations('vi','mua-thu','2026-09-01','2026-11-30','inbound');
 if (!$options) throw new RuntimeException('No destinations for selected tours');
 $ids=array_column($options,'id');if (count($ids)!==count(array_unique($ids))) throw new RuntimeException('Duplicate destination');
 foreach ($options as $option) if (!$catalog->searchTours('vi','','2026-09-01','2026-11-30',100,1,null,false,'inbound','mua-thu',$option['id'])['total']) throw new RuntimeException('Invalid destination option');
 echo 'PASS positive fixture: '.count($options)." unique destinations and exact ID filters.\\n";
 $db->table('tour_collections')->where('id',$id)->update(['is_active'=>0]);`);fs.writeFileSync(p,s);
