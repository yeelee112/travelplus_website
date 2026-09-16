<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$homeTours = $homeTours ?? $tours ?? getTourCards(null, 6);
$featuredTours = array_slice(array_values($homeTours), 0, 3);
$allToursUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Travel Plus selections',
        'title' => 'Find the right journey for your travel style',
        'desc' => 'Explore inbound and outbound journeys with clear schedules and pricing.',
        'campaignCta' => 'Explore tours',
        'allToursCta' => 'View all tours',
        'listTitle' => 'Featured tours',
        'highlights' => [
            ['icon' => 'bi-calendar2-check', 'label' => 'Clear departure dates'],
            ['icon' => 'bi-airplane', 'label' => 'Inbound and outbound tours'],
            ['icon' => 'bi-people', 'label' => 'For families and groups'],
        ],
    ]
    : [
        'eyebrow' => 'Travel Plus tuyển chọn',
        'title' => 'Chọn hành trình phù hợp với bạn',
        'desc' => 'Khám phá tour trong nước và nước ngoài với lịch khởi hành cùng mức giá rõ ràng.',
        'campaignCta' => 'Khám phá tour',
        'allToursCta' => 'Xem tất cả tour',
        'listTitle' => 'Tour nổi bật',
        'highlights' => [
            ['icon' => 'bi-calendar2-check', 'label' => 'Lịch khởi hành rõ'],
            ['icon' => 'bi-airplane', 'label' => 'Tour trong nước và nước ngoài'],
            ['icon' => 'bi-people', 'label' => 'Phù hợp gia đình, nhóm bạn'],
        ],
    ];
?>

<?php if ($featuredTours !== []): ?>
<section class="home-page__tour-grid home-tour-section home-summer-section home-section" aria-labelledby="home-tour-title">
    <div class="container">
        <div class="home-summer-spotlight">
            <div class="home-summer-spotlight__copy">
                <span class="home-summer-spotlight__eyebrow"><i class="bi bi-compass" aria-hidden="true"></i><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-tour-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>

                <ul class="home-summer-spotlight__highlights" aria-label="<?= esc($copy['eyebrow'], 'attr') ?>">
                    <?php foreach ($copy['highlights'] as $highlight): ?>
                        <li><i class="bi <?= esc($highlight['icon'], 'attr') ?>" aria-hidden="true"></i><?= esc($highlight['label']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="home-summer-spotlight__actions">
                    <a class="home-summer-spotlight__primary" href="<?= esc($allToursUrl, 'attr') ?>">
                        <?= esc($copy['campaignCta']) ?>
                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                    </a>
                    <a class="home-summer-spotlight__secondary" href="<?= esc($allToursUrl, 'attr') ?>">
                        <?= esc($copy['allToursCta']) ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="home-summer-list-head">
            <h3><?= esc($copy['listTitle']) ?></h3>
            <a href="<?= esc($allToursUrl, 'attr') ?>">
                <?= esc($copy['campaignCta']) ?>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="home-tour-grid">
            <?php foreach ($featuredTours as $tour): ?>
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
