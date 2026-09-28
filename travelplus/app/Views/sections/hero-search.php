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
    ['path' => 'assets/images/home/banner01.webp', 'width' => 1920, 'height' => 1024],
    ['path' => 'assets/images/home/banner02.webp', 'width' => 1693, 'height' => 929],
    ['path' => 'assets/images/home/banner03.webp', 'width' => 2012, 'height' => 782],
];

$copy = $locale === 'en'
    ? [
        'eyebrow' => 'TRAVEL PLUS · EXPLORE & EXPERIENCE',
        'titleParts' => ['A new journey.', 'Memories to keep.'],
        'desc' => 'Find your destination. Choose your dates. Let Travel Plus take care of the journey.',
        'searchTitle' => 'Where will your next journey take you?',
        'destinationLabel' => 'Destination',
        'destinationPlaceholder' => 'Where would you like to go?',
    ]
    : [
        'eyebrow' => 'TRAVEL PLUS · KHÁM PHÁ & TRẢI NGHIỆM',
        'titleParts' => ['Chuyến đi mới,', 'kỷ niệm đáng nhớ.'],
        'desc' => 'Chọn điểm đến bạn yêu thích. Cùng Travel Plus lên kế hoạch cho hành trình tiếp theo.',
        'searchTitle' => 'Bạn muốn khám phá nơi đâu?',
        'destinationLabel' => 'Điểm đến',
        'destinationPlaceholder' => 'Nhập điểm đến bạn muốn đi',
    ];
$dateFieldLabel = $locale === 'en' ? 'Departure dates · optional' : 'Ngày khởi hành · không bắt buộc';
$dateEmptyLabel = $locale === 'en' ? 'Choose your dates' : 'Chọn khoảng ngày đi';
$heroDestinations = (new \App\Services\TourCatalogService())->getHeroDestinations($locale);
$popularDestinations = array_slice(array_column($heroDestinations, 'name'), 0, 5);
?>

<section class="home-modern-hero home-hero-redesign" aria-labelledby="home-hero-title">
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

        <div class="home-modern-search" aria-label="<?= esc($copy['searchTitle'], 'attr') ?>">
            <div class="home-modern-search__tabs">
                <strong><?= esc($copy['searchTitle']) ?></strong>
                <a class="home-hero-browse" href="<?= esc($allToursUrl, 'attr') ?>"><?= $locale === 'en' ? 'Explore all tours' : 'Khám phá tất cả tour' ?> <span aria-hidden="true">↗</span></a>
            </div>

            <form class="filter-input show home-modern-search__form" action="<?= esc($allToursUrl, 'attr') ?>" method="get" data-tour-search-form>
                <div class="home-modern-search__field destination-box" data-destination-suggestions="<?= esc(json_encode($heroDestinations, JSON_UNESCAPED_UNICODE), 'attr') ?>">
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
                        <button type="button" class="clear-destination hidden" aria-label="<?= $locale === 'en' ? 'Clear destination' : 'Xóa điểm đến' ?>">&times;</button>
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
            <?php if ($popularDestinations !== []): ?>
            <div class="home-modern-search__popular">
                <span><?= esc($locale === 'en' ? 'Suggestions:' : 'Gợi ý cho bạn:') ?></span>
                <?php foreach ($popularDestinations as $popularDestination): ?>
                    <a href="<?= esc($allToursUrl . '?q=' . rawurlencode($popularDestination), 'attr') ?>"><?= esc($popularDestination) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="home-hero-search-footer">
                <span><i class="bi bi-headset" aria-hidden="true"></i> <?= $locale === 'en' ? 'Need help choosing? We’re here for you.' : 'Cần chọn tour? Travel Plus luôn sẵn sàng tư vấn.' ?></span>
                <a href="<?= esc($passportUrl, 'attr') ?>"><i class="bi bi-person-vcard" aria-hidden="true"></i> <?= $locale === 'en' ? 'Discover member benefits' : 'Khám phá quyền lợi thành viên' ?> <span aria-hidden="true">→</span></a>
            </div>
        </div>

    </div>
</section>

<?= $this->include('sections/home-autumn-banner') ?>
