const fs=require('fs');const p='app/Views/autumn/index.php';let s=fs.readFileSync(p,'utf8');let a=s.indexOf('$places = [');let b=s.indexOf('$icon = ',a);s=s.slice(0,a)+`$places = [
 ['Bắc Mỹ', 'North America', 'canada.webp', [
  ['Mỹ','USA','Mỹ','USA'], ['Canada','Canada','Canada','Canada'],
 ]],
 ['Châu Úc', 'Oceania', 'australia.jpg', [
  ['Úc','Australia','Úc','Australia'], ['New Zealand','New Zealand','New Zealand','New Zealand'],
 ]],
 ['Châu Âu', 'Europe', 'thuy-si.webp', [
  ['Pháp – Ý – Thụy Sĩ','France – Italy – Switzerland','Pháp','France'],
  ['Anh – Scotland','England – Scotland','Anh','England'],
  ['Đông Âu (Đức – Slovakia – Áo – Czech)','Eastern Europe (Germany – Slovakia – Austria – Czechia)','Đức','Germany'],
 ]],
 ['Châu Á', 'Asia', 'nhat-ban.webp', [
  ['Bali','Bali','Bali','Bali'], ['Nhật Bản','Japan','Nhật Bản','Japan'],
 ]],
 ['Việt Nam', 'Vietnam', 'sa-pa.webp', [
  ['Tây Bắc','Northwest Vietnam','Tây Bắc','Northwest'], ['Đông Bắc','Northeast Vietnam','Đông Bắc','Northeast'],
  ['Miền Trung','Central Vietnam','Miền Trung','Central'], ['Tà Đùng – Đà Lạt – Mũi Né','Ta Dung – Da Lat – Mui Ne','Tà Đùng','Ta Dung'],
 ]],
];
$collectionSearchUrl = \\App\\Data\\LocalizedPathCatalog::url('search', $currentLocale);
`+s.slice(b);
s=s.replace("'Thích lá đỏ, mê phố cổ hay muốn trốn phố lên núi? Bắt đầu từ đây.', 'Maple leaves, old towns or a mountain escape? Start here.'", "'Từ Bắc Mỹ, châu Úc, châu Âu đến châu Á và những cung đường Việt Nam.', 'From North America, Oceania and Europe to Asia and the landscapes of Vietnam.'");
a=s.indexOf('  <div class="at-destinations">');b=s.indexOf('\n </section>',a);s=s.slice(0,a)+`  <div class="at-collection-regions">
   <?php foreach ($places as $place): ?>
    <article class="at-region-card">
     <div class="at-region-photo"><img src="<?= base_url('assets/images/destination/' . $place[2]) ?>" alt="<?= esc($place[$en ? 1 : 0]) ?>" width="800" height="533" loading="lazy"><h3><?= esc($place[$en ? 1 : 0]) ?></h3></div>
     <ul><?php foreach ($place[3] as $journey): ?>
      <li><a href="<?= esc($collectionSearchUrl . '?' . http_build_query(['collection'=>'mua-thu', 'q'=>$journey[$en ? 3 : 2]]), 'attr') ?>"><span><?= esc($journey[$en ? 1 : 0]) ?></span><?= $icon('arrow-up-right') ?></a></li>
     <?php endforeach ?></ul>
    </article>
   <?php endforeach ?>
  </div>`+s.slice(b);fs.writeFileSync(p,s);
