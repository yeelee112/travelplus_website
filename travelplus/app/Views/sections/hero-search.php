<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$allToursUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$miceUrl = \App\Data\LocalizedPathCatalog::url('service.mice', $locale);
$visaUrl = \App\Data\LocalizedPathCatalog::url('service.visa', $locale);
$dateFieldLabel = $locale === 'en' ? 'Departure date' : 'Ngày khởi hành';
$dateEmptyLabel = lang('Frontend.hero.search.dateEmpty');
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Travel Plus Vietnam',
        'titleParts' => ['Tours, visa and MICE', 'designed with purpose'],
        'desc' => 'Travel Plus plans outbound tours, domestic journeys, visa support and corporate MICE programs for families, teams and business groups.',
        'primaryCta' => 'Find a tour',
        'secondaryCta' => 'Explore MICE services',
        'secondaryUrl' => $miceUrl,
        'searchTitle' => 'Start with a destination',
        'destinationLabel' => 'Destination',
        'destinationPlaceholder' => 'Japan, Europe, Da Nang...',
        'quickLinks' => [
            ['MICE', $miceUrl, 'bi-briefcase-fill'],
            ['Visa', $visaUrl, 'bi-passport-fill'],
            ['Tours', $allToursUrl, 'bi-map-fill'],
        ],
    ]
    : [
        'eyebrow' => 'Travel Plus Vietnam',
        'titleParts' => ['Tour, visa và MICE', 'thiết kế đúng mục tiêu'],
        'desc' => 'Khám phá tour nước ngoài, tour trong nước, dịch vụ visa và chương trình MICE doanh nghiệp được Travel Plus thiết kế trọn gói cho từng mục tiêu.',
        'primaryCta' => 'Tìm tour phù hợp',
        'secondaryCta' => 'Xem dịch vụ MICE',
        'secondaryUrl' => $miceUrl,
        'searchTitle' => 'Bắt đầu từ điểm đến',
        'destinationLabel' => 'Điểm đến',
        'destinationPlaceholder' => 'Nhật Bản, Châu Âu, Đà Nẵng...',
        'quickLinks' => [
            ['MICE', $miceUrl, 'bi-briefcase-fill'],
            ['Visa', $visaUrl, 'bi-passport-fill'],
            ['Tour', $allToursUrl, 'bi-map-fill'],
        ],
    ];
?>

<section class="home-modern-hero" aria-labelledby="home-hero-title">
    <div class="home-modern-hero__media" aria-hidden="true">
        <img
            src="<?= esc(base_url('assets/images/home/banner01.jpg'), 'attr') ?>"
            alt=""
            width="1920"
            height="680"
            loading="eager"
            fetchpriority="high"
            decoding="async">
    </div>
    <div class="container">
        <div class="home-modern-hero__content">
            <span class="home-modern-eyebrow"><?= esc($copy['eyebrow']) ?></span>
            <h1 id="home-hero-title">
                <?php foreach ($copy['titleParts'] as $titlePart): ?>
                    <span><?= esc((string) $titlePart) ?></span>
                <?php endforeach; ?>
            </h1>
            <p><?= esc($copy['desc']) ?></p>
            <div class="home-modern-hero__actions">
                <a class="home-modern-btn home-modern-btn--primary" href="<?= esc($allToursUrl, 'attr') ?>">
                    <?= esc($copy['primaryCta']) ?>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
                <a class="home-modern-btn home-modern-btn--ghost" href="<?= esc((string) $copy['secondaryUrl'], 'attr') ?>">
                    <?= esc($copy['secondaryCta']) ?>
                </a>
            </div>
        </div>

        <div class="home-modern-search" aria-label="<?= esc($copy['searchTitle'], 'attr') ?>">
            <div class="home-modern-search__head">
                <strong><?= esc($copy['searchTitle']) ?></strong>
                <div class="home-modern-search__links">
                    <?php foreach ($copy['quickLinks'] as $item): ?>
                        <a href="<?= esc((string) $item[1], 'attr') ?>">
                            <i class="bi <?= esc((string) $item[2], 'attr') ?>"></i>
                            <?= esc((string) $item[0]) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <form class="filter-input show home-modern-search__form" action="<?= esc($allToursUrl, 'attr') ?>" method="get" data-tour-search-form>
                <div class="home-modern-search__field destination-box">
                    <label for="homeSearchDestination"><?= esc($copy['destinationLabel']) ?></label>
                    <div class="home-modern-search__input-wrap">
                        <i class="bi bi-geo-alt-fill"></i>
                        <input
                            id="homeSearchDestination"
                            type="text"
                            name="q"
                            class="destination-input"
                            placeholder="<?= esc($copy['destinationPlaceholder'], 'attr') ?>"
                            autocomplete="off">
                        <button type="button" class="clear-destination hidden" aria-label="Clear destination">&times;</button>
                    </div>
                    <div class="custom-select-wrap">
                        <ul class="option-list-destination"></ul>
                    </div>
                </div>

                <div class="home-modern-search__field home-modern-search__field--date">
                    <label for="homeSearchDeparture"><?= esc($dateFieldLabel) ?></label>
                    <div class="home-modern-search__input-wrap home-modern-search__input-wrap--date">
                        <i class="bi bi-calendar2-week-fill" aria-hidden="true"></i>
                        <div class="home-search-date" data-home-date-picker data-locale="<?= esc($locale, 'attr') ?>">
                            <input type="hidden" name="departure_date" value="" data-home-date-input>
                            <button
                                type="button"
                                id="homeSearchDeparture"
                                class="home-search-date__trigger"
                                data-home-date-trigger
                                data-empty-label="<?= esc($dateEmptyLabel, 'attr') ?>"
                                aria-expanded="false"
                                aria-haspopup="dialog">
                                <span class="home-search-date__value" data-home-date-display><?= esc($dateEmptyLabel) ?></span>
                            </button>
                            <div class="home-search-date__panel" data-home-date-panel hidden>
                                <div class="home-search-date__calendar" role="dialog" aria-label="<?= esc($dateFieldLabel, 'attr') ?>">
                                    <div class="home-search-date__calendar-head">
                                        <button type="button" class="home-search-date__nav" data-home-date-prev aria-label="Previous month">&lsaquo;</button>
                                        <strong class="home-search-date__month" data-home-date-month></strong>
                                        <button type="button" class="home-search-date__nav" data-home-date-next aria-label="Next month">&rsaquo;</button>
                                    </div>
                                    <div class="home-search-date__weekdays" data-home-date-weekdays aria-hidden="true"></div>
                                    <div class="home-search-date__days" data-home-date-days></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="home-modern-search__submit">
                    <i class="bi bi-search"></i>
                    <?= esc(lang('Frontend.hero.search.submit')) ?>
                </button>
            </form>
        </div>
    </div>
</section>
