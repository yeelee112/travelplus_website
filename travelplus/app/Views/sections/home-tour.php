<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$homeTours = $homeTours ?? $tours ?? getTourCards(null, 6);
$summerTours = array_slice(array_values($homeTours), 0, 3);
$allToursUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$summerUrl = \App\Data\LocalizedPathCatalog::url('summer', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Summer collection 2026',
        'title' => 'A summer journey for every travel style',
        'desc' => 'From beaches and vibrant cities to long-haul discoveries, choose a well-planned route with clear departure dates.',
        'campaignCta' => 'Explore summer tours',
        'allToursCta' => 'View all tours',
        'listTitle' => 'Suggestions for this summer',
        'highlights' => [
            ['icon' => 'bi-calendar2-check', 'label' => 'Clear departure dates'],
            ['icon' => 'bi-airplane', 'label' => 'Domestic and outbound'],
            ['icon' => 'bi-people', 'label' => 'For families and groups'],
        ],
    ]
    : [
        'eyebrow' => 'Bộ sưu tập hè 2026',
        'title' => 'Tour hè cho từng kiểu trải nghiệm',
        'desc' => 'Từ biển xanh, thành phố sôi động đến hành trình đường dài, chọn chuyến đi được chuẩn bị rõ lịch và dễ lên kế hoạch.',
        'campaignCta' => 'Khám phá Tour hè',
        'allToursCta' => 'Xem tất cả tour',
        'listTitle' => 'Gợi ý cho mùa hè này',
        'highlights' => [
            ['icon' => 'bi-calendar2-check', 'label' => 'Lịch khởi hành rõ'],
            ['icon' => 'bi-airplane', 'label' => 'Trong nước và nước ngoài'],
            ['icon' => 'bi-people', 'label' => 'Phù hợp gia đình, nhóm bạn'],
        ],
    ];
?>

<?php if ($summerTours !== []): ?>
<section class="home-page__tour-grid home-tour-section home-summer-section home-section" aria-labelledby="home-tour-title">
    <div class="container">
        <div class="home-summer-spotlight">
            <div class="home-summer-spotlight__copy">
                <span class="home-summer-spotlight__eyebrow"><i class="bi bi-sun-fill" aria-hidden="true"></i><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-tour-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>

                <ul class="home-summer-spotlight__highlights" aria-label="<?= esc($copy['eyebrow'], 'attr') ?>">
                    <?php foreach ($copy['highlights'] as $highlight): ?>
                        <li><i class="bi <?= esc($highlight['icon'], 'attr') ?>" aria-hidden="true"></i><?= esc($highlight['label']) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="home-summer-spotlight__actions">
                    <a class="home-summer-spotlight__primary" href="<?= esc($summerUrl, 'attr') ?>">
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
            <a href="<?= esc($summerUrl, 'attr') ?>">
                <?= esc($copy['campaignCta']) ?>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="home-tour-grid">
            <?php foreach ($summerTours as $tour): ?>
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
