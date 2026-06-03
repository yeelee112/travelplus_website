<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$homeTours = $homeTours ?? $tours ?? getTourCards(null, 6);
$allToursUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Tours with clear departures',
        'title' => 'Popular tours for families, groups and company teams',
        'desc' => 'Explore outbound and domestic tours with transparent pricing, available departures and detailed itineraries before booking.',
        'cta' => 'View all tours',
    ]
    : [
        'eyebrow' => 'Tour có lịch khởi hành rõ',
        'title' => 'Tour nổi bật cho gia đình, nhóm bạn và đoàn công ty',
        'desc' => 'Khám phá tour nước ngoài và tour trong nước với giá minh bạch, lịch khởi hành rõ và thông tin hành trình dễ so sánh trước khi đặt.',
        'cta' => 'Xem tất cả tour',
    ];
?>

<?php if (! empty($homeTours)): ?>
<section class="home-page__tour-grid home-tour-section home-section home-section--white" aria-labelledby="home-tour-title">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-tour-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
            </div>
            <a class="home-section-link" href="<?= esc($allToursUrl, 'attr') ?>">
                <?= esc($copy['cta']) ?>
                <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>

        <div class="home-tour-grid">
            <?php foreach ($homeTours as $tour): ?>
                <div class="home-tour-grid__item">
                    <?= view('components/tour-card', ['tour' => $tour]) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="home-tour-scroll-hint" aria-hidden="true">
            <span></span>
        </div>
    </div>
</section>
<?php endif; ?>
