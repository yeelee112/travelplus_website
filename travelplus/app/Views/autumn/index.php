<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$en = $currentLocale === 'en';
$t = static fn(string $vi, string $english): string => $en ? $english : $vi;
$searchUrl = \App\Data\LocalizedPathCatalog::url('search', $currentLocale);
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $currentLocale);
$settings = new \App\Services\WebsiteSettingsService();
$destinations = [
    ['Nhật Bản', 'Japan', 'nhat-ban.webp', 'Dưới tán phong đỏ', 'Beneath the red maples', 'Một buổi dạo chơi bên hồ, một góc phố cổ, một Nhật Bản thật khác khi thu về.', 'Lakeside walks, old town streets and a different side of Japan as autumn arrives.'],
    ['Hàn Quốc', 'South Korea', 'han-quoc.jpg', 'Chạm vào mùa lãng mạn', 'A season for romance', 'Chậm bước trên những con đường rợp lá, ghé quán nhỏ và tận hưởng tiết trời se lạnh.', 'Wander along leafy avenues, stop at a little café and enjoy the crisp air.'],
    ['Sa Pa', 'Sa Pa', 'sa-pa.webp', 'Đi giữa miền mây núi', 'Among mountains and clouds', 'Đổi nhịp phố thị lấy một sớm vùng cao, những nếp nhà nhỏ và núi đồi nối tiếp.', 'Trade the city rush for highland mornings, hillside homes and mountain views.'],
];
?>
<div class="autumn-landing">
    <section class="autumn-hero" aria-labelledby="autumn-title">
        <img class="autumn-hero-image" src="<?= base_url('assets/images/destination/nhat-ban.webp') ?>" alt="<?= $t('Núi Phú Sĩ và hồ nước dưới tán lá phong đỏ', 'Mount Fuji and a lake framed by red maple leaves') ?>" fetchpriority="high" width="800" height="533">
        <div class="autumn-hero-content autumn-wrap">
            <p class="autumn-eyebrow">TRAVEL PLUS &nbsp; / &nbsp; AUTUMN COLLECTION</p>
            <h1 id="autumn-title"><?= $t('Hẹn nhau giữa<br>mùa <em>lá đỏ.</em>', 'Meet me in<br><em>autumn.</em>') ?></h1>
            <p class="autumn-intro"><?= $t('Khi những tán cây thay màu, cũng là lúc mình đổi một khung trời. Cùng Travel Plus đi tìm mùa thu của riêng bạn.', 'As the leaves turn, let your view change too. Find your own autumn escape with Travel Plus.') ?></p>
            <a class="autumn-button" href="#autumn-destinations"><?= $t('Khám phá hành trình', 'Explore the journeys') ?> <span aria-hidden="true">↗</span></a>
            <div class="autumn-hero-bottom"><span><?= $t('MỘT MÙA THU. NHIỀU MIỀN NHỚ.', 'ONE SEASON. SO MANY MEMORIES.') ?></span><span>01 / <?= $t('Nhật Bản', 'Japan') ?> &nbsp; — &nbsp; <?= $t('Hồ Kawaguchi', 'Lake Kawaguchi') ?></span></div>
        </div>
    </section>
    <div class="autumn-ribbon"><span><?= $t('Đi để thấy thu thật khác', 'See autumn differently') ?></span><span aria-hidden="true">✳</span><span><?= $t('Sắc lá · Hương thu · Những hành trình', 'Golden leaves · Crisp air · New journeys') ?></span><span aria-hidden="true">✳</span><span>Travel your way</span></div>
    <section class="autumn-wrap autumn-destinations" id="autumn-destinations" aria-labelledby="destinations-title">
        <div class="autumn-section-top"><div><p class="autumn-eyebrow"><?= $t('CHỌN MỘT MIỀN THU', 'FIND YOUR AUTUMN') ?></p><h2 id="destinations-title"><?= $t('Bạn muốn đón thu<br>ở <em>đâu?</em>', 'Where will autumn<br>take <em>you?</em>') ?></h2></div><p><?= $t('Có mùa thu rực rỡ sắc phong. Có mùa thu bình yên giữa núi đồi. Chọn nơi khiến bạn muốn xách vali lên.', 'Some autumns glow with red maples. Others unfold quietly in the mountains. Choose the place that calls to you.') ?></p></div>
        <div class="autumn-cards">
        <?php foreach ($destinations as $i => $destination): ?>
            <article class="autumn-card">
                <a class="autumn-card-image" href="<?= esc($searchUrl . '?' . http_build_query(['q' => $destination[$en ? 1 : 0]]), 'attr') ?>" aria-label="<?= esc($t('Xem tour ', 'Explore tours to ') . $destination[$en ? 1 : 0]) ?>"><img src="<?= base_url('assets/images/destination/' . $destination[2]) ?>" alt="<?= esc($destination[$en ? 1 : 0]) ?>" loading="lazy" width="800" height="533"><span><?= esc($destination[$en ? 1 : 0]) ?></span><b aria-hidden="true">0<?= $i + 1 ?></b></a>
                <h3><?= esc($destination[$en ? 4 : 3]) ?></h3><p><?= esc($destination[$en ? 6 : 5]) ?></p>
                <a class="autumn-text-link" href="<?= esc($searchUrl . '?' . http_build_query(['q' => $destination[$en ? 1 : 0]]), 'attr') ?>"><?= $t('Khám phá tour', 'Explore tours') ?> <span aria-hidden="true">↗</span></a>
            </article>
        <?php endforeach ?>
        </div>
    </section>
    <section class="autumn-story">
        <div class="autumn-story-image"><img src="<?= base_url('assets/images/destination/nhat-ban.webp') ?>" alt="<?= $t('Sắc thu bên hồ dưới chân núi Phú Sĩ', 'Autumn colours by the lake at Mount Fuji') ?>" loading="lazy" width="800" height="533"><span>A little slower.<br>A little closer.</span></div>
        <div class="autumn-story-copy"><p class="autumn-eyebrow"><?= $t('THU NÀY, ĐI CHẬM MỘT CHÚT', 'THIS AUTUMN, SLOW DOWN') ?></p><h2><?= $t('Để mỗi ngày đi<br>là một ngày <em>nhớ.</em>', 'Make every day<br>one to <em>remember.</em>') ?></h2><p><?= $t('Không chỉ là một tấm ảnh đẹp. Đó còn là cái se lạnh buổi sớm, tiếng lá dưới chân và những câu chuyện trên đường. Một chuyến đi vừa đủ để bạn gần hơn với thế giới, và với nhau.', 'More than a beautiful photo. The chill of an early morning, leaves underfoot and stories shared along the way. A journey that brings you closer to the world, and to each other.') ?></p><a class="autumn-text-link" href="<?= esc($contactUrl, 'attr') ?>"><?= $t('Cùng lên kế hoạch chuyến đi', 'Let’s plan your journey') ?> <span aria-hidden="true">↗</span></a></div>
    </section>
    <section class="autumn-wrap autumn-planning" aria-labelledby="planning-title"><p class="autumn-eyebrow"><?= $t('TRƯỚC KHI LÊN ĐƯỜNG', 'BEFORE YOU GO') ?></p><h2 id="planning-title"><?= $t('Một chút chuẩn bị,<br>một mùa thu <em>trọn vẹn.</em>', 'A little planning.<br>A wonderful <em>autumn.</em>') ?></h2><div class="autumn-notes">
        <div><span>01</span><h3><?= $t('Chọn đúng thời điểm', 'Find your moment') ?></h3><p><?= $t('Sắc lá thay đổi theo vùng và thời tiết từng năm. Hãy trao đổi với tư vấn viên để chọn lịch trình phù hợp.', 'Foliage varies by region and weather each year. Ask our team to help you choose the right itinerary.') ?></p></div>
        <div><span>02</span><h3><?= $t('Sẵn sàng giấy tờ', 'Prepare your documents') ?></h3><p><?= $t('Kiểm tra hộ chiếu và yêu cầu visa của điểm đến trước khi chốt ngày khởi hành.', 'Check your passport and destination visa requirements before confirming your departure.') ?></p></div>
        <div><span>03</span><h3><?= $t('Mang theo chút ấm', 'Pack a little warmth') ?></h3><p><?= $t('Áo khoác nhẹ, giày đi bộ thoải mái và một khoảng trống trong vali dành cho những món quà nhỏ.', 'A light jacket, comfortable walking shoes and a little room in your suitcase for souvenirs.') ?></p></div>
    </div></section>
    <section class="autumn-contact"><div class="autumn-wrap"><p class="autumn-eyebrow">YOUR NEXT CHAPTER</p><h2><?= $t('Mùa thu không đợi.<br><em>Mình đi thôi.</em>', 'Autumn is calling.<br><em>Let’s go.</em>') ?></h2><p><?= $t('Kể Travel Plus nghe về chuyến đi bạn đang mong chờ.', 'Tell Travel Plus about the journey you’ve been dreaming of.') ?></p><div class="autumn-contact-actions"><a class="autumn-button" href="<?= esc($contactUrl, 'attr') ?>"><?= $t('Nhận tư vấn hành trình', 'Plan my journey') ?> <span aria-hidden="true">↗</span></a><a class="autumn-phone" href="tel:<?= esc($settings->get('hotline_e164'), 'attr') ?>"><?= esc($settings->phoneDisplay($currentLocale)) ?></a></div></div></section>
</div>
<?= $this->endSection() ?>
