<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$allToursUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$miceUrl = \App\Data\LocalizedPathCatalog::url('service.mice', $locale);
$visaUrl = \App\Data\LocalizedPathCatalog::url('service.visa', $locale);
$passportUrl = \App\Data\LocalizedPathCatalog::url('passport.program', $locale);

$dateFieldLabel = $locale === 'en' ? 'Departure window' : 'Khoảng ngày khởi hành';
$dateEmptyLabel = $locale === 'en' ? 'Choose a travel window' : 'Chọn khoảng ngày đi';
$dateFromLabel = $locale === 'en' ? 'From date' : 'Từ ngày';
$dateToLabel = $locale === 'en' ? 'To date' : 'Đến ngày';
$dateUnsetLabel = $locale === 'en' ? 'Not selected' : 'Chưa chọn';
$dateClearLabel = $locale === 'en' ? 'Clear' : 'Xóa';
$dateHintLabel = $locale === 'en'
    ? 'Pick a rough travel window to find departures that match your plan.'
    : 'Chọn khoảng thời gian dự kiến để xem các tour có lịch khởi hành phù hợp.';
$heroImages = [
    ['path' => 'assets/images/home/banner00.png', 'width' => 2051, 'height' => 767],
];

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

$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Explore the world',
        'titleParts' => ['A JOURNEY OF INSPIRATION'],
        'desc' => 'Quality tours – Dedicated service – Lasting value',
        'primaryCta' => 'Watch introduction video',
        'searchTitle' => 'Find a tour',
        'destinationLabel' => 'Destination',
        'destinationPlaceholder' => 'Where would you like to go?',
    ]
    : [
        'eyebrow' => 'Khám phá thế giới',
        'titleParts' => ['SẴN SÀNG CHO CHUYẾN ĐI MỚI'],
        'desc' => 'Tour chất lượng – Dịch vụ tận tâm – Giá trị bền vững',
        'primaryCta' => 'Xem video giới thiệu',
        'searchTitle' => 'Tìm tour',
        'destinationLabel' => 'Điểm đến',
        'destinationPlaceholder' => 'Bạn muốn đi đâu?',
    ];
$popularDestinations = $locale === 'en'
    ? ['South Korea', 'Japan', 'Thailand', 'Europe', 'Da Nang', 'Phu Quoc', 'Nha Trang', 'Da Lat']
    : ['Hàn Quốc', 'Nhật Bản', 'Thái Lan', 'Châu Âu', 'Đà Nẵng', 'Phú Quốc', 'Nha Trang', 'Đà Lạt'];
$trustItems = $locale === 'en'
    ? [
        ['bi-shield-check', 'Thoughtful service', 'Carefully prepared for every journey'],
        ['bi-tag', 'Great prices every day', 'Attractive offers are always available'],
        ['bi-headset', 'Dedicated support', 'With you before, during and after'],
        ['bi-bag-check', 'Secure payment', 'Your information is always protected'],
    ]
    : [
        ['bi-shield-check', 'Dịch vụ chỉn chu', 'Tận tâm trong từng hành trình'],
        ['bi-tag', 'Giá tốt mỗi ngày', 'Luôn có ưu đãi hấp dẫn'],
        ['bi-headset', 'Hỗ trợ tận tâm', 'Đồng hành trước – trong – sau tour'],
        ['bi-bag-check', 'Thanh toán an toàn', 'Bảo mật thông tin tuyệt đối'],
    ];
?>

<section class="home-modern-hero" aria-labelledby="home-hero-title">
    <div class="home-modern-hero__media" aria-hidden="true" data-hero-rotator data-interval="7000">
        <?php foreach ($heroImages as $index => $heroImage): ?>
            <?php
            $heroImagePath = (string) $heroImage['path'];
            $heroImageUrl = base_url($heroImagePath);
            $heroImageSrcset = $heroImageUrl . ' ' . (int) $heroImage['width'] . 'w';
            ?>
            <img
                class="<?= $index === 0 ? 'is-active' : '' ?>"
                <?php if ($index === 0): ?>
                src="<?= esc($heroImageUrl, 'attr') ?>"
                srcset="<?= esc($heroImageSrcset, 'attr') ?>"
                <?php else: ?>
                data-hero-src="<?= esc($heroImageUrl, 'attr') ?>"
                data-hero-srcset="<?= esc($heroImageSrcset, 'attr') ?>"
                <?php endif; ?>
                sizes="100vw"
                alt=""
                width="<?= (int) $heroImage['width'] ?>"
                height="<?= (int) $heroImage['height'] ?>"
                loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                <?= $index === 0 ? 'fetchpriority="high"' : 'fetchpriority="low"' ?>
                decoding="async">
        <?php endforeach; ?>
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
        </div>

        <a class="home-hero-offer" href="<?= esc($passportUrl, 'attr') ?>">
            <i class="bi bi-passport"></i>
            <span>
                <strong>TravelPlus Passport</strong>
                <small><?= esc($locale === 'en' ? 'Book tours and earn journey miles' : 'Đặt tour để tích Dặm Hành Trình') ?></small>
                <em><?= esc($locale === 'en' ? 'View benefits' : 'Xem quyền lợi') ?> →</em>
            </span>
        </a>

        <div class="home-modern-search" aria-label="<?= esc($copy['searchTitle'], 'attr') ?>">
            <div class="home-modern-search__tabs">
                <strong><i class="bi bi-airplane"></i><?= esc($copy['searchTitle']) ?></strong>
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
                        <div class="home-search-date" data-date-range-picker data-locale="<?= esc($locale, 'attr') ?>">
                            <input type="hidden" name="departure_from" value="" data-date-range-input-start>
                            <input type="hidden" name="departure_to" value="" data-date-range-input-end>
                            <button
                                type="button"
                                id="homeSearchDeparture"
                                class="home-search-date__trigger"
                                data-date-range-trigger
                                data-empty-label="<?= esc($dateEmptyLabel, 'attr') ?>"
                                data-start-empty-label="<?= esc($dateUnsetLabel, 'attr') ?>"
                                data-end-empty-label="<?= esc($dateUnsetLabel, 'attr') ?>"
                                aria-expanded="false"
                                aria-haspopup="dialog">
                                <span class="home-search-date__value" data-date-range-display><?= esc($dateEmptyLabel) ?></span>
                            </button>
                            <div class="home-search-date__panel" data-date-range-panel hidden>
                                <div class="home-search-date__calendar" role="dialog" aria-label="<?= esc($dateFieldLabel, 'attr') ?>">
                                    <div class="home-search-date__selection">
                                        <div class="home-search-date__selection-item">
                                            <span><?= esc($dateFromLabel) ?></span>
                                            <strong data-date-range-preview-start><?= esc($dateUnsetLabel) ?></strong>
                                        </div>
                                        <div class="home-search-date__selection-item">
                                            <span><?= esc($dateToLabel) ?></span>
                                            <strong data-date-range-preview-end><?= esc($dateUnsetLabel) ?></strong>
                                        </div>
                                    </div>
                                    <div class="home-search-date__calendar-head">
                                        <button type="button" class="home-search-date__nav" data-date-range-prev aria-label="Previous month">&lsaquo;</button>
                                        <strong class="home-search-date__month" data-date-range-month></strong>
                                        <button type="button" class="home-search-date__nav" data-date-range-next aria-label="Next month">&rsaquo;</button>
                                    </div>
                                    <div class="home-search-date__weekdays" data-date-range-weekdays aria-hidden="true"></div>
                                    <div class="home-search-date__days" data-date-range-days></div>
                                    <div class="home-search-date__footer">
                                        <p><?= esc($dateHintLabel) ?></p>
                                        <button type="button" class="home-search-date__clear" data-date-range-clear><?= esc($dateClearLabel) ?></button>
                                    </div>
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
            <div class="home-modern-search__popular">
                <span><?= esc($locale === 'en' ? 'Popular:' : 'Điểm đến phổ biến:') ?></span>
                <?php foreach ($popularDestinations as $popularDestination): ?>
                    <a href="<?= esc($allToursUrl . '?q=' . rawurlencode($popularDestination), 'attr') ?>"><?= esc($popularDestination) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<section class="home-hero-trust-section" aria-label="<?= esc($locale === 'en' ? 'Travel Plus commitments' : 'Cam kết của Travel Plus', 'attr') ?>">
    <div class="container">
        <ul class="home-hero-trust">
            <?php foreach ($trustItems as $trustItem): ?>
                <li>
                    <i class="bi <?= esc($trustItem[0], 'attr') ?>"></i>
                    <span><strong><?= esc($trustItem[1]) ?></strong><small><?= esc($trustItem[2]) ?></small></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
