<?php
$autumnLink = null;
foreach (($navigationCollections ?? []) as $collection) {
    if ($collection['slug'] === 'mua-thu') { $autumnLink = $collection['url']; break; }
}
$bannerEnglish = ($currentLocale ?? 'vi') === 'en';
?>
<?php if ($autumnLink !== null): ?>
<section class="container home-autumn" aria-labelledby="home-autumn-title">
    <a class="home-autumn__banner" href="<?= esc($autumnLink, 'attr') ?>">
        <div class="home-autumn__copy">
            <span class="home-autumn__eyebrow"><i class="bi bi-leaf" aria-hidden="true"></i> <?= $bannerEnglish ? 'THE AUTUMN COLLECTION' : 'BỘ SƯU TẬP MÙA THU' ?></span>
            <h2 id="home-autumn-title"><?= $bannerEnglish ? 'Autumn is here. Let’s go!' : 'Thu sang, đi thôi!' ?></h2>
            <p><?= $bannerEnglish ? 'Red maple leaves, mountain escapes. Find your kind of autumn.' : 'Ngắm lá đỏ, dạo phố cổ, hẹn nhau giữa núi đồi. Chọn mùa thu của riêng bạn.' ?></p>
            <span class="home-autumn__cta"><?= $bannerEnglish ? 'Explore autumn tours' : 'Khám phá tour mùa thu' ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
        </div>
        <div class="home-autumn__photo"><img src="<?= base_url('assets/images/landing/autumn/banner01.png') ?>" alt="<?= $bannerEnglish ? 'Autumn journeys across America, Europe, Japan and Vietnam' : 'Những điểm đến mùa thu tại Mỹ, châu Âu, Nhật Bản và Việt Nam' ?>" width="1774" height="887" loading="lazy"></div>
    </a>
</section>
<?php endif ?>
