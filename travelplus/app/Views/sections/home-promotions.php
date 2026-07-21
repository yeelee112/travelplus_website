<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$searchUrl = \App\Data\LocalizedPathCatalog::url('search', $locale) . '?promotion=1';
$homeTours = array_values($homeTours ?? []);
$promotionalTours = array_values($promotionalTours ?? []);
$promoTours = $promotionalTours;
$uniqueTours = static function (array $tours): array {
    $seen = [];
    $items = [];

    foreach ($tours as $tour) {
        if (! is_array($tour)) {
            continue;
        }

        $key = (string) ($tour['id'] ?? '');
        if ($key === '') {
            $key = trim((string) ($tour['link'] ?? ''));
        }
        if ($key === '') {
            $key = mb_strtolower(trim((string) ($tour['title'] ?? '')));
        }
        if ($key === '' || isset($seen[$key])) {
            continue;
        }

        $seen[$key] = true;
        $items[] = $tour;
    }

    return $items;
};

$excludeTour = static function (array $tours, ?array $excludedTour) use ($uniqueTours): array {
    if ($excludedTour === null) {
        return $uniqueTours($tours);
    }

    $excludedId = (string) ($excludedTour['id'] ?? '');
    $excludedLink = trim((string) ($excludedTour['link'] ?? ''));
    $excludedTitle = mb_strtolower(trim((string) ($excludedTour['title'] ?? '')));

    return $uniqueTours(array_values(array_filter($tours, static function (array $tour) use ($excludedId, $excludedLink, $excludedTitle): bool {
        if ($excludedId !== '' && (string) ($tour['id'] ?? '') === $excludedId) {
            return false;
        }

        $link = trim((string) ($tour['link'] ?? ''));
        if ($excludedLink !== '' && $link === $excludedLink) {
            return false;
        }

        $title = mb_strtolower(trim((string) ($tour['title'] ?? '')));
        return $excludedTitle === '' || $title !== $excludedTitle;
    })));
};

$promoTours = $uniqueTours($promoTours);

if ($promoTours === []) {
    return;
}

$featureTour = $promoTours[0] ?? null;
$sideTours = array_slice($excludeTour($promoTours, $featureTour), 0, 3);

$fallbackTitle = $locale === 'en'
    ? 'Selected departures for families and groups'
    : 'Lịch khởi hành tốt cho gia đình và đoàn nhỏ';
$featureTitle = (string) ($featureTour['title'] ?? $fallbackTitle);
$featureLink = (string) ($featureTour['link'] ?? $searchUrl);
$featureImage = (string) ($featureTour['image'] ?? base_url('assets/images/home/banner02.webp'));
$featurePrice = (string) ($featureTour['price']['label'] ?? '');
$featureDeparture = (string) ($featureTour['departure'] ?? '');
$featureContinent = (string) ($featureTour['continent'] ?? '');
$featureDuration = (string) ($featureTour['duration']['label'] ?? '');
$featurePromotion = is_array($featureTour['promotion'] ?? null) ? $featureTour['promotion'] : [];
$featureBadge = trim((string) ($featurePromotion['badge'] ?? ''));
$featureEndsAt = trim((string) ($featurePromotion['ends_at_iso'] ?? $featurePromotion['ends_at'] ?? ''));
$hasRealPromotion = $promotionalTours !== [];

if ($featureEndsAt === '' && ! $hasRealPromotion && $featureTour !== null) {
    $featureEndsAt = date(DATE_ATOM, strtotime('+3 days'));
}

$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Current offers',
        'title' => 'Tour deals worth checking first',
        'desc' => 'Limited-time departures, group-friendly routes and clear pricing for travelers who want to plan early.',
        'sectionTitle' => 'Tour deals with clear schedules and limited-time pricing',
        'signalCountLabel' => 'active tour deals',
        'signalDepartureLabel' => 'nearest departure',
        'badge' => $featureBadge !== '' ? $featureBadge : 'Tour deal',
        'kicker' => 'Limited-time travel offer',
        'featureNote' => 'Early booking or group requests may receive better available slots.',
        'priceLabel' => 'Deal price',
        'departureLabel' => 'Departure',
        'countdownLabel' => 'Offer ends in',
        'days' => 'Days',
        'hours' => 'Hours',
        'minutes' => 'Minutes',
        'seconds' => 'Seconds',
        'expired' => 'Offer ended',
        'featureCta' => 'View deal',
        'moreTitle' => 'More tour deals',
        'moreBadge' => 'Tour deal',
        'moreCta' => 'View deal',
        'emptyMore' => 'More promotional tours can be displayed here after they are marked in the tour database.',
    ]
    : [
        'eyebrow' => 'Ưu đãi đang mở',
        'title' => 'Tour khuyến mãi đáng xem trước khi lên lịch',
        'desc' => 'Các lịch khởi hành có ưu đãi, phù hợp cho gia đình, nhóm nhỏ và đoàn công ty muốn chốt kế hoạch sớm.',
        'badge' => $featureBadge !== '' ? $featureBadge : 'Tour khuyến mãi',
        'kicker' => 'Ưu đãi nổi bật trong thời gian này',
        'featureNote' => 'Đặt sớm hoặc đi theo nhóm giúp giữ lịch tốt và tối ưu chi phí hơn.',
        'priceLabel' => 'Giá khuyến mãi',
        'departureLabel' => 'Khởi hành',
        'countdownLabel' => 'Ưu đãi còn',
        'days' => 'Ngày',
        'hours' => 'Giờ',
        'minutes' => 'Phút',
        'seconds' => 'Giây',
        'expired' => 'Ưu đãi đã kết thúc',
        'featureCta' => 'Xem tour',
        'moreTitle' => 'Tour khuyến mãi khác',
        'moreBadge' => 'Tour ưu đãi',
        'moreCta' => 'Xem ưu đãi',
        'emptyMore' => 'Có thể hiển thị thêm tour khuyến mãi tại đây sau khi đánh dấu tour trong database.',
    ];

$copy['sectionTitle'] = $locale === 'en'
    ? 'Tour deals with clear schedules and limited-time pricing'
    : 'Tour khuyến mãi nổi bật hôm nay';
$copy['featureCta'] = $locale === 'en' ? 'View deal' : 'Xem ưu đãi';
$sideTourCount = count($sideTours);
$allToursCompactLabel = $locale === 'en' ? 'View all' : 'Xem tất cả';
?>

<section class="home-promo-section home-promo-section--side-<?= esc((string) min(3, $sideTourCount), 'attr') ?>" aria-label="<?= esc($copy['title'], 'attr') ?>">
    <div class="home-promo-decor" aria-hidden="true">
        <span class="home-promo-decor__route"><i class="bi bi-airplane-engines"></i></span>
        <span class="home-promo-decor__route home-promo-decor__route--lower"><i class="bi bi-airplane-fill"></i></span>
        <i class="home-promo-decor__compass bi bi-compass-fill"></i>
        <i class="home-promo-decor__luggage bi bi-suitcase2"></i>
        <i class="home-promo-decor__globe bi bi-globe2"></i>
        <i class="home-promo-decor__camera bi bi-camera-fill"></i>
        <i class="home-promo-decor__map bi bi-map-fill"></i>
        <i class="home-promo-decor__pin bi bi-geo-alt-fill"></i>
    </div>
    <div class="container">
        <div class="home-promo-head">
            <div class="home-promo-head__copy">
                <span><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i><?= esc($copy['eyebrow']) ?></span>
                <h2><?= esc($copy['sectionTitle']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
            </div>
        </div>

        <div class="home-promo-layout">
            <article class="home-promo-feature">
                <a class="home-promo-feature__media" href="<?= esc($featureLink, 'attr') ?>">
                    <span class="home-promo-feature__ribbon"><i class="bi bi-fire" aria-hidden="true"></i><?= esc($copy['badge']) ?></span>
                    <img
                        src="<?= esc($featureImage, 'attr') ?>"
                        alt="<?= esc($featureTitle, 'attr') ?>"
                        width="720"
                        height="460"
                        loading="lazy"
                        decoding="async">
                </a>
                <div class="home-promo-feature__body">
                    <div class="home-promo-feature__top">
                        <strong class="home-promo-feature__kicker"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i><?= esc($copy['kicker']) ?></strong>
                        <h3><a href="<?= esc($featureLink, 'attr') ?>"><?= esc($featureTitle) ?></a></h3>
                    </div>

                    <?php if ($featureContinent !== '' || $featureDuration !== ''): ?>
                        <div class="home-promo-feature__meta">
                            <?php if ($featureContinent !== ''): ?>
                                <span><i class="bi bi-geo-alt"></i><?= esc($featureContinent) ?></span>
                            <?php endif; ?>
                            <?php if ($featureDuration !== ''): ?>
                                <span><i class="bi bi-clock"></i><?= esc($featureDuration) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($featurePrice !== '' || $featureDeparture !== ''): ?>
                        <div class="home-promo-feature__facts">
                            <?php if ($featurePrice !== ''): ?>
                                <div class="home-promo-price">
                                    <span><?= esc($copy['priceLabel']) ?></span>
                                    <strong><?= esc($featurePrice) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($featureDeparture !== ''): ?>
                                <div class="home-promo-departure">
                                    <span><?= esc($copy['departureLabel']) ?></span>
                                    <strong><?= esc($featureDeparture) ?></strong>
                                </div>
                            <?php endif; ?>
                            <a class="home-promo-link" href="<?= esc($featureLink, 'attr') ?>">
                                <?= esc($copy['featureCta']) ?>
                                <i class="bi bi-arrow-up-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($featureEndsAt !== ''): ?>
                        <div
                            class="home-promo-countdown"
                            data-countdown
                            data-countdown-end="<?= esc($featureEndsAt, 'attr') ?>"
                            data-expired-label="<?= esc($copy['expired'], 'attr') ?>">
                            <span class="home-promo-countdown__label"><i class="bi bi-clock-history" aria-hidden="true"></i><?= esc($copy['countdownLabel']) ?></span>
                            <div class="home-promo-countdown__grid">
                                <span><strong data-countdown-days>00</strong><small><?= esc($copy['days']) ?></small></span>
                                <span><strong data-countdown-hours>00</strong><small><?= esc($copy['hours']) ?></small></span>
                                <span><strong data-countdown-minutes>00</strong><small><?= esc($copy['minutes']) ?></small></span>
                                <span><strong data-countdown-seconds>00</strong><small><?= esc($copy['seconds']) ?></small></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($featurePrice === '' && $featureDeparture === ''): ?>
                        <a class="home-promo-link" href="<?= esc($featureLink, 'attr') ?>">
                            <?= esc($copy['featureCta']) ?>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </article>

            <?php if ($sideTours !== []): ?>
            <aside class="home-promo-list home-promo-list--count-<?= esc((string) min(3, $sideTourCount), 'attr') ?>" aria-label="<?= esc($copy['moreTitle'], 'attr') ?>">
                <div class="home-promo-list__head">
                    <h3><?= esc($copy['moreTitle']) ?></h3>
                    <a href="<?= esc($searchUrl, 'attr') ?>">
                        <?= esc($allToursCompactLabel) ?>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="home-promo-list__scroller">
                    <?php foreach ($sideTours as $tour): ?>
                        <?php
                        $tourPromotion = is_array($tour['promotion'] ?? null) ? $tour['promotion'] : [];
                        $tourBadge = trim((string) ($tourPromotion['badge'] ?? '')) ?: (string) $copy['moreBadge'];
                        $tourPrice = (string) ($tour['price']['label'] ?? '');
                        $tourPriceLabel = $tourPromotion !== []
                            ? (string) $copy['priceLabel']
                            : ($locale === 'en' ? 'Tour price' : 'Giá tour');
                        $tourDeparture = (string) ($tour['departure'] ?? '');
                        $tourContinent = (string) ($tour['continent'] ?? '');
                        $tourDuration = (string) ($tour['duration']['label'] ?? '');
                        ?>
                        <article class="home-promo-card">
                            <a
                                class="home-promo-card__link-full"
                                href="<?= esc((string) ($tour['link'] ?? $searchUrl), 'attr') ?>"
                                aria-label="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>">
                                <div class="home-promo-card__media">
                                    <span><?= esc($tourBadge) ?></span>
                                    <img
                                        src="<?= esc((string) ($tour['image'] ?? base_url('assets/images/avt-tour-01.webp')), 'attr') ?>"
                                        alt=""
                                        width="320"
                                        height="220"
                                        loading="lazy"
                                        decoding="async">
                                </div>
                                <div class="home-promo-card__body">
                                    <div class="home-promo-card__content">
                                        <h3 title="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>"><?= esc((string) ($tour['title'] ?? '')) ?></h3>
                                        <?php if ($tourContinent !== '' || $tourDuration !== '' || $tourDeparture !== ''): ?>
                                            <div class="home-promo-card__meta">
                                                <?php if ($tourContinent !== ''): ?>
                                                    <span><i class="bi bi-geo-alt"></i><?= esc($tourContinent) ?></span>
                                                <?php endif; ?>
                                                <?php if ($tourDuration !== ''): ?>
                                                    <span><i class="bi bi-clock"></i><?= esc($tourDuration) ?></span>
                                                <?php endif; ?>
                                                <?php if ($tourDeparture !== ''): ?>
                                                    <span><i class="bi bi-calendar3"></i><?= esc($tourDeparture) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="home-promo-card__footer">
                                        <?php if ($tourPrice !== ''): ?>
                                            <div class="home-promo-card__price">
                                                <span><?= esc($tourPriceLabel) ?></span>
                                                <strong><?= esc($tourPrice) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <span class="home-promo-card__arrow" aria-hidden="true">
                                            <i class="bi bi-arrow-up-right"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
