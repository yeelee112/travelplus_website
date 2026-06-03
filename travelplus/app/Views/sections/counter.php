<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Travel Plus in numbers',
        'title' => 'A partner for repeat travel, MICE and group programs',
        'stats' => [
            ['value' => '26K+', 'label' => 'Journeys organized'],
            ['value' => '19+', 'label' => 'Years of experience'],
            ['value' => '2,000+', 'label' => 'Guests and business travelers'],
            ['value' => '98%', 'label' => 'Customer satisfaction'],
        ],
    ]
    : [
        'eyebrow' => 'Travel Plus qua số liệu',
        'title' => 'Đối tác cho tour, MICE và các đoàn khách cần vận hành ổn định',
        'stats' => [
            ['value' => '26K+', 'label' => 'Hành trình đã tổ chức'],
            ['value' => '19+', 'label' => 'Năm kinh nghiệm'],
            ['value' => '2.000+', 'label' => 'Khách hàng và khách đoàn'],
            ['value' => '98%', 'label' => 'Mức độ hài lòng'],
        ],
    ];
?>

<section class="home-page__stats home-section home-section--dark" aria-labelledby="home-stats-title">
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
