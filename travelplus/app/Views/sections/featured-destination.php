<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$featuredDestinations = $featuredDestinations ?? [];
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Featured destinations',
        'title' => 'Choose a destination before choosing a tour',
        'desc' => 'Browse the destinations Travel Plus guests search most often, then continue into curated tours by region and country.',
        'cta' => 'Explore tours',
    ]
    : [
        'eyebrow' => 'Điểm đến nổi bật',
        'title' => 'Chọn điểm đến trước khi chọn tour',
        'desc' => 'Xem nhanh các điểm đến được khách Travel Plus quan tâm nhiều, sau đó đi tiếp vào tour theo khu vực, quốc gia hoặc tỉnh thành.',
        'cta' => 'Khám phá tour',
    ];
?>

<?php if ($featuredDestinations !== []): ?>
<section class="home-page__featured-destinations home-section home-section--soft" aria-labelledby="home-destinations-title">
    <div class="container">
        <div class="home-section-head">
            <div>
                <span><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-destinations-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
            </div>
        </div>

        <ul class="nav nav-pills home-destination-tabs" id="featured-destination-tabs" role="tablist">
            <?php foreach ($featuredDestinations as $index => $tab): ?>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                        id="featured-destination-tab-<?= esc((string) $tab['key'], 'attr') ?>"
                        data-bs-toggle="pill"
                        data-bs-target="#featured-destination-<?= esc((string) $tab['key'], 'attr') ?>"
                        type="button"
                        role="tab"
                        aria-controls="featured-destination-<?= esc((string) $tab['key'], 'attr') ?>"
                        aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                        <?= esc((string) $tab['label']) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="featured-destination-contents">
            <?php foreach ($featuredDestinations as $index => $tab): ?>
                <div
                    class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>"
                    id="featured-destination-<?= esc((string) $tab['key'], 'attr') ?>"
                    role="tabpanel"
                    aria-labelledby="featured-destination-tab-<?= esc((string) $tab['key'], 'attr') ?>">
                    <div class="home-destination-grid">
                        <?php foreach (($tab['items'] ?? []) as $itemIndex => $item): ?>
                            <article class="home-destination-card <?= $itemIndex === 0 ? 'home-destination-card--wide' : '' ?>">
                                <a href="<?= esc((string) ($item['link'] ?? '#'), 'attr') ?>">
                                    <img
                                        src="<?= esc((string) ($item['image'] ?? base_url('assets/images/avt-tour-01.jpg')), 'attr') ?>"
                                        alt="<?= esc((string) ($item['title'] ?? ''), 'attr') ?>"
                                        loading="lazy"
                                        decoding="async"
                                        width="640"
                                        height="420">
                                    <span><?= esc((string) ($item['subtitle'] ?? '')) ?></span>
                                    <h3><?= esc((string) ($item['title'] ?? '')) ?></h3>
                                    <strong>
                                        <?= esc($copy['cta']) ?>
                                        <i class="bi bi-arrow-up-right"></i>
                                    </strong>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
