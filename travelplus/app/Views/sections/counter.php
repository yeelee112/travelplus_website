<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$statsBackground = base_url('assets/images/summary-tp.png');
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Travel Plus in numbers',
        'title' => 'A stable operator for tours, MICE and repeat group travel',
        'stats' => [
            ['value' => '90+', 'label' => 'Countries'],
            ['value' => '2500+', 'label' => 'Groups'],
            ['value' => '200K', 'label' => 'Customers'],
            ['value' => '99%', 'label' => 'Satisfaction'],
        ],
    ]
    : [
        'eyebrow' => 'Travel Plus qua số liệu',
        'title' => 'Đối tác vận hành ổn định cho tour, MICE và các đoàn khách đi lặp lại',
        'stats' => [
            ['value' => '90+', 'label' => 'Quốc gia'],
            ['value' => '2500+', 'label' => 'Đoàn'],
            ['value' => '200K', 'label' => 'Khách hàng'],
            ['value' => '99%', 'label' => 'Độ hài lòng'],
        ],
    ];
?>

<section class="home-page__stats home-section home-section--dark" aria-labelledby="home-stats-title" style="--home-stats-bg: url('<?= esc($statsBackground, 'attr') ?>');">
    <div class="container">
        <div class="home-stats-head">
            <span><?= esc($copy['eyebrow']) ?></span>
            <h2 id="home-stats-title"><?= esc($copy['title']) ?></h2>
        </div>
        <div class="home-stats-grid">
            <?php foreach ($copy['stats'] as $stat): ?>
                <div class="home-stat-card">
                    <strong><?= esc($stat['value']) ?></strong>
                    <span><?= esc($stat['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
