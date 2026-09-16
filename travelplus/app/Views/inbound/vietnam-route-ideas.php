<?php
$routeIdeas = [
    ['City stories & island days', 'Ho Chi Minh City + Phu Quoc', 'Pair the energy of the city with a few unhurried days by the sea.', 'assets/images/destination/phu-quoc.jpg', '01'],
    ['Heritage & coastal light', 'Hoi An + Da Nang + Nha Trang', 'Discover old-town streets, then follow the coast at your own pace.', 'assets/images/gallery-3.jpg', '02'],
    ['Capital & highland air', 'Hanoi + Sapa + Ninh Binh', 'Connect cultural discoveries with mountain views and gentle river journeys.', 'assets/images/destination/sa-pa.webp', '03'],
];
?>
<section class="inbound-route-ideas" aria-labelledby="inbound-route-ideas-title">
    <div class="container">
        <div class="inbound-route-ideas__head"><span class="inbound-section-kicker">Journey inspiration</span><h2 id="inbound-route-ideas-title">Places that pair<br><em>beautifully.</em></h2><p>These are starting points for a custom trip. We will adapt the route to your dates, interests and pace.</p></div>
        <div class="inbound-route-ideas__grid">
            <?php foreach ($routeIdeas as $index => $idea): ?>
                <article class="inbound-route-idea">
                    <div class="inbound-route-idea__photo"><img src="<?= esc(base_url($idea[3]), 'attr') ?>" alt="<?= esc(['Phu Quoc island, Vietnam', 'Hoi An heritage streets, Vietnam', 'Mountain landscapes of Sapa, Vietnam'][$index], 'attr') ?>" width="700" height="600" loading="lazy" decoding="async"><span><?= esc($idea[4]) ?> / JOURNEY IDEA</span></div>
                    <div class="inbound-route-idea__copy"><small><?= esc($idea[1]) ?></small><h3><?= esc($idea[0]) ?></h3><p><?= esc($idea[2]) ?></p><a class="inbound-story-link" href="#plan-my-trip">Make it my journey <i class="bi bi-arrow-up-right"></i></a></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
