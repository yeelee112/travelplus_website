<?php
$vietnamMoments = [
    ['Ha Long Bay', 'Let the world slow down.', 'Emerald waters, limestone islands and a different perspective on Vietnam.', 'assets/images/destination/quang-ninh.jpg', 'northern-vietnam', 'inbound-moment--lead'],
    ['Hoi An', 'Follow the lanterns.', 'Heritage streets and little discoveries.', 'assets/images/gallery-3.jpg', 'central-vietnam', 'inbound-moment--hoi-an'],
    ['Phu Quoc', 'A little island time.', 'Coastal colours and laid-back days.', 'assets/images/destination/phu-quoc.jpg', 'southern-vietnam', ''],
    ['Da Lat', 'Take the scenic route.', 'A fresh perspective in the highlands.', 'assets/images/destination/da-lat.webp', 'central-vietnam', ''],
    ['Nha Trang', 'Wake up by the sea.', 'Golden light on the central coast.', 'assets/images/destination/nha-trang.webp', 'central-vietnam', ''],
];
$vietnamInboundUrl = \App\Data\LocalizedPathCatalog::url('inbound', 'en');
?>
<section class="inbound-photo-essay" aria-labelledby="inbound-photo-essay-title">
    <div class="container">
        <div class="inbound-photo-essay__head">
            <div><span class="inbound-section-kicker">A place to feel, not just see</span><h2 id="inbound-photo-essay-title">Vietnam, in<br><em>a thousand colours.</em></h2></div>
            <p>From quiet bays to lantern-lit streets and sunlit shores. Make room for the moments between the landmarks.</p>
        </div>
        <div class="inbound-photo-essay__grid">
            <?php foreach ($vietnamMoments as $moment): ?>
                <?php $momentImage = base_url($moment[3]); $momentSrcset = responsive_image_srcset($momentImage, [480, 960, 1440]); ?>
                <a class="inbound-moment <?= esc($moment[5], 'attr') ?>" href="<?= esc($vietnamInboundUrl . '/' . $moment[4], 'attr') ?>" aria-label="<?= esc('Explore ' . $moment[0], 'attr') ?>">
                    <img src="<?= esc($momentImage, 'attr') ?>" <?php if ($momentSrcset !== ''): ?>srcset="<?= esc($momentSrcset, 'attr') ?>" sizes="(max-width: 575px) 92vw, (max-width: 991px) 50vw, 46vw"<?php endif; ?> alt="<?= esc($moment[0] . ' — ' . $moment[2], 'attr') ?>" width="1000" height="800" loading="lazy" decoding="async">
                    <span class="inbound-moment__caption"><small><?= esc($moment[0]) ?></small><strong><?= esc($moment[1]) ?></strong><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="inbound-photo-essay__note"><span>Different landscapes. One unforgettable journey.</span><a href="#plan-my-trip">Make these moments yours <i class="bi bi-arrow-right"></i></a></div>
    </div>
</section>
