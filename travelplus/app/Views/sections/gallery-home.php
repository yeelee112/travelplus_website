<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$blogListUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Travel moments',
        'title' => 'A glimpse of destinations and experiences with Travel Plus',
        'desc' => 'From city breaks and nature routes to corporate programs, these moments show the kind of journeys Travel Plus designs and operates.',
        'cta' => 'Read travel inspiration',
    ]
    : [
        'eyebrow' => 'Khoảnh khắc hành trình',
        'title' => 'Hình ảnh điểm đến và trải nghiệm cùng Travel Plus',
        'desc' => 'Từ city break, thiên nhiên đến chương trình doanh nghiệp, mỗi hình ảnh gợi mở cách Travel Plus thiết kế và vận hành hành trình.',
        'cta' => 'Xem cảm hứng du lịch',
    ];
$images = [
    ['src' => 'assets/images/gallery-1.jpg', 'alt' => 'Travel Plus city travel experience', 'wide' => true],
    ['src' => 'assets/images/gallery-3.jpg', 'alt' => 'Travel Plus mountain and lake itinerary', 'wide' => false],
    ['src' => 'assets/images/gallery-4.jpg', 'alt' => 'Travel Plus group travel moment', 'wide' => false],
    ['src' => 'assets/images/gallery-5.jpg', 'alt' => 'Travel Plus curated destination', 'wide' => false],
    ['src' => 'assets/images/gallery-6.jpg', 'alt' => 'Travel Plus travel detail', 'wide' => false],
];
?>

<section class="home-page__gallery home-section home-section--white" aria-labelledby="home-gallery-title">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-gallery-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
            </div>
            <a class="home-section-link" href="<?= esc($blogListUrl, 'attr') ?>">
                <?= esc($copy['cta']) ?>
                <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>

        <div class="home-gallery-grid">
            <?php foreach ($images as $image): ?>
                <a
                    class="home-gallery-item <?= ! empty($image['wide']) ? 'home-gallery-item--wide' : '' ?>"
                    href="<?= esc(base_url((string) $image['src']), 'attr') ?>"
                    data-fancybox="gallery-01"
                    aria-label="<?= esc((string) $image['alt'], 'attr') ?>">
                    <img
                        src="<?= esc(base_url((string) $image['src']), 'attr') ?>"
                        alt="<?= esc((string) $image['alt'], 'attr') ?>"
                        width="<?= ! empty($image['wide']) ? '720' : '360' ?>"
                        height="280"
                        loading="lazy"
                        decoding="async">
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
