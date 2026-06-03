<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);

$copy = [
    'vi' => [
        'domestic' => 'Tour trong nước',
        'outbound' => 'Tour nước ngoài',
        'routeFallback' => 'Điểm đến đang cập nhật',
        'duration' => 'Thời lượng',
        'flightFrom' => 'Bay từ',
        'departure' => 'Khởi hành',
        'scheduleUpdating' => 'Đang cập nhật lịch',
        'pricePrefix' => 'Giá từ',
    ],
    'en' => [
        'domestic' => 'Domestic tour',
        'outbound' => 'Outbound tour',
        'routeFallback' => 'Destination updating',
        'duration' => 'Duration',
        'flightFrom' => 'From',
        'departure' => 'Departure',
        'scheduleUpdating' => 'Schedule updating',
        'pricePrefix' => 'From',
    ],
][$locale];

$title = trim((string) ($tour['title'] ?? ''));
$link = trim((string) ($tour['link'] ?? '#'));
$image = trim((string) ($tour['image'] ?? '')) ?: base_url('assets/images/home/banner02.jpg');
$badge = trim((string) ($tour['badge'] ?? ''));
$promotion = is_array($tour['promotion'] ?? null) ? $tour['promotion'] : [];
$promotionBadge = trim((string) ($promotion['badge'] ?? ''));
$isPromotion = ! empty($promotion['is_active']);
$badgeText = $isPromotion && $promotionBadge !== '' ? $promotionBadge : $badge;
$tourType = (string) ($tour['tour_type'] ?? '');
$typeLabel = $tourType === 'inbound' ? $copy['domestic'] : $copy['outbound'];
$locationName = trim((string) ($tour['continent'] ?? '')) ?: $copy['routeFallback'];
$locationLink = trim((string) ($tour['continent_link'] ?? '#')) ?: '#';
$departureFrom = trim((string) ($tour['departure_from'] ?? ''));
$durationLabel = trim((string) ($tour['duration']['label'] ?? ''));

if ($durationLabel === '') {
    $days = (int) ($tour['duration']['days'] ?? 0);
    $nights = (int) ($tour['duration']['nights'] ?? 0);
    $durationLabel = $days > 0
        ? trim($days . ' ' . $t('tour.duration.days') . ' ' . $nights . ' ' . $t('tour.duration.nights'))
        : '';
}

$departureLabel = trim((string) ($tour['departure'] ?? ''));
$priceLabel = trim((string) ($tour['price']['label'] ?? ''));
$priceAmount = (float) ($tour['price']['amount'] ?? 0);
$priceCurrency = trim((string) ($tour['price']['currency'] ?? 'VND')) ?: 'VND';
$ariaLabel = $title !== '' ? $t('tourCard.viewDetails', [$title]) : $t('tourCard.cta');
?>

<article class="package-card tp-tour-card<?= $isPromotion ? ' tp-tour-card--promo' : '' ?>" itemscope itemtype="https://schema.org/TouristTrip">
    <meta itemprop="name" content="<?= esc($title, 'attr') ?>">
    <meta itemprop="url" content="<?= esc($link, 'attr') ?>">
    <meta itemprop="image" content="<?= esc($image, 'attr') ?>">

    <div class="package-img-wrap tp-tour-card__media">
        <a class="package-img tp-tour-card__image" href="<?= esc($link, 'attr') ?>" aria-label="<?= esc($ariaLabel, 'attr') ?>">
            <img src="<?= esc($image, 'attr') ?>" alt="<?= esc($title, 'attr') ?>" loading="lazy" decoding="async" width="420" height="280">
        </a>

        <div class="tp-tour-card__media-top">
            <?php if ($badgeText !== ''): ?>
                <span class="tp-tour-card__badge"><?= esc($badgeText) ?></span>
            <?php endif; ?>
            <span class="tp-tour-card__type"><?= esc($typeLabel) ?></span>
        </div>
    </div>

    <div class="h-100">
        <div class="package-content tp-tour-card__body">
            <div class="tp-tour-card__meta">
                <a class="tp-tour-card__route" href="<?= esc($locationLink, 'attr') ?>">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span><?= esc($locationName) ?></span>
                </a>
                <?php if ($durationLabel !== ''): ?>
                    <span class="tp-tour-card__chip">
                        <i class="bi bi-clock"></i>
                        <?= esc($durationLabel) ?>
                    </span>
                <?php endif; ?>
            </div>

            <h3 class="tp-tour-card__title clamp-2" itemprop="name">
                <a href="<?= esc($link, 'attr') ?>" itemprop="url"><?= esc($title) ?></a>
            </h3>

            <div class="tp-tour-card__schedule">
                <div class="tp-tour-card__schedule-item">
                    <span>
                        <i class="bi bi-calendar3"></i>
                        <?= esc($copy['departure']) ?>
                    </span>
                    <strong><?= esc($departureLabel !== '' ? $departureLabel : $copy['scheduleUpdating']) ?></strong>
                </div>
                <?php if ($departureFrom !== ''): ?>
                    <div class="tp-tour-card__schedule-item">
                        <span>
                            <i class="bi bi-airplane-engines"></i>
                            <?= esc($copy['flightFrom']) ?>
                        </span>
                        <strong><?= esc($departureFrom) ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tp-tour-card__footer" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <div class="tp-tour-card__price">
                    <span><?= esc($copy['pricePrefix']) ?></span>
                    <strong><?= esc($priceLabel !== '' ? $priceLabel : $t('tour.sidebar.checkAvailability')) ?></strong>
                    <?php if ($priceAmount > 0): ?>
                        <meta itemprop="price" content="<?= esc((string) $priceAmount, 'attr') ?>">
                        <meta itemprop="priceCurrency" content="<?= esc($priceCurrency, 'attr') ?>">
                    <?php endif; ?>
                    <link itemprop="availability" href="https://schema.org/InStock">
                    <link itemprop="url" href="<?= esc($link, 'attr') ?>">
                </div>
                <a class="tp-tour-card__cta" href="<?= esc($link, 'attr') ?>">
                    <?= esc($t('tourCard.cta')) ?>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>
    </div>
</article>
