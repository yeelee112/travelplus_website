<?php
$destinationStories = require __DIR__ . '/destination-stories.php';
$destinationExperiences = require __DIR__ . '/destination-experiences.php';
$glimpses = [
    ['Ha Long Bay', 'Drift between limestone islands.', 'Cruise through calm emerald water, visit hidden caves and watch the bay change colour at sunset.', 'assets/images/destination/quang-ninh.jpg', ['Scenic cruises', 'Limestone islands', 'Sunset views'], '2 days / 1 night', 'October–April', 'Hanoi · Ninh Binh', ['Overnight cruise', 'Sung Sot Cave', 'Kayaking or bamboo boat']],
    ['Hoi An', 'Wander into a world of lanterns.', 'Slow down among ochre streets, riverside cafés, local workshops and lantern-lit evenings.', 'assets/images/gallery-3.jpg', ['Ancient Town', 'Local crafts', 'Food experiences'], '2–3 nights', 'February–August', 'Da Nang · Hue', ['Hoi An Ancient Town', 'Tra Que village', 'An Bang Beach']],
    ['Ninh Binh', 'Follow the river. Find the quiet.', 'Glide past rice fields and limestone cliffs, then explore temples and peaceful country roads.', 'assets/images/landing/inbound/ninh-binh-river.webp', ['River journeys', 'Countryside', 'Ancient temples'], '1–2 nights', 'October–April', 'Hanoi · Ha Long Bay', ['Trang An boat ride', 'Hoa Lu temples', 'Mua Cave viewpoint']],
    ['Phu Quoc', 'Trade busy days for island time.', 'End your journey with warm water, quiet beaches and easy days beneath tropical skies.', 'assets/images/destination/phu-quoc.jpg', ['Island beaches', 'Seafood', 'Slow travel'], '3–4 nights', 'November–April', 'Ho Chi Minh City · Mekong Delta', ['Southern beaches', 'Island boat trip', 'Sunset seafood dinner']],
    ['Sapa', 'Meet the mountains, at your own pace.', 'Walk between terraced valleys and meet the communities whose cultures shape the northern highlands.', 'assets/images/destination/sa-pa.webp', ['Mountain walks', 'Terraced valleys', 'Local culture'], '2–3 nights', 'March–May · September–November', 'Hanoi · Ninh Binh', ['Muong Hoa Valley', 'Village walks', 'Fansipan views']],
    ['Hanoi', 'Discover the character of the capital.', 'Begin with old streets, lakeside mornings, street food and the layered stories of Vietnam’s capital.', 'assets/images/destination/ha-noi.webp', ['Old Quarter', 'Street food', 'Local history'], '2–3 nights', 'October–April', 'Ninh Binh · Ha Long Bay', ['Old Quarter walk', 'Temple of Literature', 'Evening food tour']],
    ['Da Nang', 'A coastal city with room to explore.', 'Combine relaxed beach time with city energy and easy access to Central Vietnam’s heritage sites.', 'assets/images/destination/da-nang.jpg', ['City & beach', 'Son Tra Peninsula', 'Central Vietnam'], '2–3 nights', 'February–August', 'Hoi An · Hue', ['My Khe Beach', 'Son Tra Peninsula', 'Marble Mountains']],
    ['Da Lat', 'Take a breath of highland air.', 'Find pine forests, flower gardens and cooler days in Vietnam’s relaxed central highlands.', 'assets/images/destination/da-lat.webp', ['Highland scenery', 'Coffee culture', 'Outdoor escapes'], '2–3 nights', 'November–March', 'Ho Chi Minh City · Nha Trang', ['Coffee farm visit', 'Pine forest walks', 'Local market']],
    ['Nha Trang', 'Let the coast set the pace.', 'Enjoy a bright coastal city with island excursions, fresh seafood and long stretches of sea.', 'assets/images/destination/nha-trang.webp', ['Island trips', 'Coastal dining', 'Beach days'], '2–3 nights', 'January–August', 'Da Lat · Da Nang', ['Island boat trip', 'Po Nagar towers', 'Beachfront evenings']],
];
$photoFolders = ['ha-long', 'hoi-an', 'ninh-binh', 'phu-quoc', 'sapa', 'ha-noi', 'da-nang', 'da-lat', 'nha-trang'];
$localPhotos = [];
foreach ($glimpses as $index => &$glimpse) {
    $relativeFolder = 'assets/images/landing/inbound/destinations/' . $photoFolders[$index] . '/';
    $files = glob(FCPATH . $relativeFolder . '*') ?: [];
    natsort($files);
    $localPhotos[$index] = [];
    foreach ($files as $file) {
        if (!is_file($file) || !in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'avif'], true)) {
            continue;
        }
        $photoPath = $relativeFolder . rawurlencode(basename($file));
        if (strtolower(pathinfo($file, PATHINFO_FILENAME)) === 'cover') {
            $glimpse[3] = $photoPath;
        } else {
            $localPhotos[$index][] = $photoPath;
        }
    }
}
unset($glimpse);
?>
<section class="inbound-glimpse" aria-labelledby="inbound-glimpse-title">
    <div class="container">
        <div class="inbound-glimpse__head">
            <div><span class="inbound-section-kicker">Your first glimpse</span><h2 id="inbound-glimpse-title">This is <em>Vietnam.</em></h2></div>
            <p>From mountain air to island days. Select a place to discover more.</p>
        </div>
        <div id="inbound-glimpse-slider" class="inbound-glimpse__photos" tabindex="0" role="region" aria-roledescription="carousel" aria-label="Vietnam destinations">
            <?php foreach ($glimpses as $index => $glimpse): ?>
                <button class="inbound-glimpse__card" type="button" data-glimpse-open="glimpse-detail-<?= $index ?>" aria-haspopup="dialog">
                    <img src="<?= esc(base_url($glimpse[3]), 'attr') ?>" alt="<?= esc($glimpse[0] . ', Vietnam', 'attr') ?>" width="760" height="960" loading="lazy" decoding="async">
                    <span class="inbound-glimpse__number" aria-hidden="true"><?= sprintf('%02d', $index + 1) ?></span>
                    <span class="inbound-glimpse__caption"><strong><?= esc($glimpse[0]) ?></strong><small><?= esc($glimpse[1]) ?></small><i class="bi bi-plus-lg" aria-hidden="true"></i></span>
                </button>
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
    <?php foreach ($glimpses as $index => $glimpse): ?>
        <dialog class="inbound-glimpse-dialog" id="glimpse-detail-<?= $index ?>" aria-labelledby="glimpse-detail-title-<?= $index ?>">
            <button class="inbound-glimpse-dialog__close" type="button" data-glimpse-close aria-label="Close details"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            <div class="inbound-glimpse-dialog__image"><img src="<?= esc(base_url($glimpse[3]), 'attr') ?>" alt="<?= esc($glimpse[0] . ', Vietnam', 'attr') ?>" width="900" height="1000" loading="lazy" decoding="async"><span><?= sprintf('%02d', $index + 1) ?> / VIETNAM</span></div>
            <div class="inbound-glimpse-dialog__copy">
                <span class="inbound-section-kicker">A place to discover</span>
                <h3 id="glimpse-detail-title-<?= $index ?>"><?= esc($glimpse[0]) ?></h3>
                <strong><?= esc($glimpse[1]) ?></strong>
                <p><?= esc($glimpse[2]) ?></p>
                <ul><?php foreach ($glimpse[4] as $highlight): ?><li><i class="bi bi-check2" aria-hidden="true"></i><?= esc($highlight) ?></li><?php endforeach; ?></ul>
                <article class="inbound-glimpse-dialog__story" aria-label="<?= esc('Discover ' . $glimpse[0], 'attr') ?>">
                    <?php foreach (array_merge($destinationStories[$glimpse[0]], $destinationExperiences[$glimpse[0]]) as $storyIndex => [$heading, $paragraph]): ?>
                        <section><h4><?= esc($heading) ?></h4><p><?= esc($paragraph) ?></p></section>
                        <?php foreach ($localPhotos[$index] as $photoIndex => $photo): ?>
                            <?php if (min($photoIndex, 4) === $storyIndex): ?>
                                <figure class="inbound-glimpse-dialog__story-photo"><img data-destination-photo="<?= esc(base_url($photo), 'attr') ?>" alt="<?= esc($glimpse[0] . ' — photo ' . ($photoIndex + 1), 'attr') ?>" loading="lazy" decoding="async"></figure>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </article>
                <div class="inbound-glimpse-dialog__places"><span>Places &amp; experiences to explore</span><p><?= esc(implode(' · ', $glimpse[8])) ?></p></div>
                <a class="inbound-btn inbound-btn--primary" href="#plan-my-trip" data-glimpse-plan="<?= esc($glimpse[0], 'attr') ?>">Add <?= esc($glimpse[0]) ?> to my trip <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </dialog>
    <?php endforeach; ?>
</section>
