<?php
$glimpseUrl = \App\Data\LocalizedPathCatalog::url('inbound', 'en');
$glimpses = [
    ['Ha Long Bay', 'Drift between limestone islands.', 'assets/images/destination/quang-ninh.jpg', 'northern-vietnam'],
    ['Hoi An', 'Wander into a world of lanterns.', 'assets/images/gallery-3.jpg', 'central-vietnam'],
    ['Ninh Binh', 'Follow the river. Find the quiet.', 'assets/images/landing/inbound/ninh-binh-river.webp', 'northern-vietnam'],
    ['Phu Quoc', 'Trade busy days for island time.', 'assets/images/destination/phu-quoc.jpg', 'southern-vietnam'],
    ['Sapa', 'Meet the mountains, at your own pace.', 'assets/images/destination/sa-pa.webp', 'northern-vietnam'],
    ['Hanoi', 'Discover the character of the capital.', 'assets/images/destination/ha-noi.webp', 'northern-vietnam'],
    ['Da Nang', 'A coastal city with room to explore.', 'assets/images/destination/da-nang.jpg', 'central-vietnam'],
    ['Da Lat', 'Take a breath of highland air.', 'assets/images/destination/da-lat.webp', 'central-vietnam'],
    ['Nha Trang', 'Let the coast set the pace.', 'assets/images/destination/nha-trang.webp', 'central-vietnam'],
];
?>
<section class="inbound-glimpse" aria-labelledby="inbound-glimpse-title">
    <div class="container">
        <div class="inbound-glimpse__head">
            <div><span class="inbound-section-kicker">Your first glimpse</span><h2 id="inbound-glimpse-title">This is <em>Vietnam.</em></h2></div>
            <p>From mountain air to island days. Find your Vietnam.</p>
        </div>
        <div id="inbound-glimpse-slider" class="inbound-glimpse__photos" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Vietnam destinations">
            <?php foreach ($glimpses as $index => $glimpse): ?>
                <a class="inbound-glimpse__card" href="<?= esc($glimpseUrl . '/' . $glimpse[3], 'attr') ?>">
                    <img src="<?= esc(base_url($glimpse[2]), 'attr') ?>" alt="<?= esc($glimpse[0] . ', Vietnam' . ($index === 2 ? ' — illustrative landscape' : ''), 'attr') ?>" width="760" height="960" loading="lazy" decoding="async">
                    <span class="inbound-glimpse__number" aria-hidden="true">0<?= $index + 1 ?></span>
                    <span class="inbound-glimpse__caption"><strong><?= esc($glimpse[0]) ?></strong><small><?= esc($glimpse[1]) ?></small><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="inbound-glimpse__navigation">
            <span>Discover Vietnam <small class="inbound-glimpse__position">1 / <?= count($glimpses) ?></small></span>
            <div class="inbound-glimpse__arrows">
                <button type="button" data-glimpse-direction="-1" aria-label="Previous destinations" aria-controls="inbound-glimpse-slider"><i class="bi bi-arrow-left" aria-hidden="true"></i></button>
                <button type="button" data-glimpse-direction="1" aria-label="Next destinations" aria-controls="inbound-glimpse-slider"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
</section>
