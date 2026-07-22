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
        'meetingPoint' => 'Điểm đón',
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
        'meetingPoint' => 'Meeting point',
        'departure' => 'Departure',
        'scheduleUpdating' => 'Schedule updating',
        'pricePrefix' => 'From',
    ],
][$locale];

$title = trim((string) ($tour['title'] ?? ''));
$link = trim((string) ($tour['link'] ?? '#'));
$image = trim((string) ($tour['image'] ?? '')) ?: base_url('assets/images/home/banner02.webp');
$imageSrcset = responsive_image_srcset($image, [480, 960]);
$badge = trim((string) ($tour['badge'] ?? ''));
$promotion = is_array($tour['promotion'] ?? null) ? $tour['promotion'] : [];
$promotionBadge = trim((string) ($promotion['badge'] ?? ''));
$isPromotion = ! empty($promotion['is_active']);
$badgeText = $isPromotion && $promotionBadge !== '' ? $promotionBadge : $badge;
$tourType = (string) ($tour['tour_type'] ?? '');
$typeLabel = $tourType === 'inbound' ? $copy['domestic'] : $copy['outbound'];
$departureFromLabel = $tourType === 'inbound' ? $copy['meetingPoint'] : $copy['flightFrom'];
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
$loyaltyPoints = \App\Services\LoyaltyPointService::previewPoints($priceAmount);
$loyaltyPointsLabel = number_format($loyaltyPoints, 0, $locale === 'en' ? '.' : ',', $locale === 'en' ? ',' : '.');
$loyaltyCopy = $locale === 'en'
    ? 'Earn ' . $loyaltyPointsLabel . '+ member points'
    : 'Từ ' . $loyaltyPointsLabel . ' điểm thành viên';
$ariaLabel = $title !== '' ? $t('tourCard.viewDetails', [$title]) : $t('tourCard.cta');
$tourToolCopy = [
    'wishlist' => $locale === 'en' ? 'Save tour' : 'Lưu tour',
    'wishlistSaved' => $locale === 'en' ? 'Saved' : 'Đã lưu',
    'compare' => $locale === 'en' ? 'Compare' : 'So sánh',
    'compareSaved' => $locale === 'en' ? 'Comparing' : 'Đang so sánh',
    'toolsLabel' => $locale === 'en' ? 'Tour saving and comparison actions' : 'Thao tác lưu và so sánh tour',
];
$tourToolId = trim((string) ($tour['id'] ?? ''));
if ($tourToolId === '') {
    $tourToolId = md5(($link !== '#' ? $link : '') . '|' . $title);
}
$travelerLimit = (int) ($tour['max_travelers'] ?? 0);
$travelerLabel = $travelerLimit > 0
    ? ($locale === 'en' ? 'Up to ' . $travelerLimit . ' guests' : 'Tối đa ' . $travelerLimit . ' khách')
    : '';
$singleRoomSupplement = (float) ($tour['single_room_supplement'] ?? 0);
$singleRoomSupplementLabel = $singleRoomSupplement > 0
    ? number_format($singleRoomSupplement, 0, ',', '.') . 'đ'
    : ($locale === 'en' ? 'Contact for quote' : 'Liên hệ báo giá');
$tourToolIncluded = implode(', ', array_slice(array_values(array_filter(array_map(
    static fn($item): string => is_array($item) ? trim((string) ($item['label'] ?? '')) : trim((string) $item),
    (array) ($tour['inclusions']['included'] ?? [])
))), 0, 3));
?>

<article
    class="package-card tp-tour-card<?= $isPromotion ? ' tp-tour-card--promo' : '' ?>"
    itemscope
    itemtype="https://schema.org/TouristTrip"
    data-tour-tools-source
    data-tour-id="<?= esc($tourToolId, 'attr') ?>"
    data-tour-title="<?= esc($title, 'attr') ?>"
    data-tour-url="<?= esc($link, 'attr') ?>"
    data-tour-image="<?= esc($image, 'attr') ?>"
    data-tour-price="<?= esc($priceLabel !== '' ? $priceLabel : $t('tour.sidebar.checkAvailability'), 'attr') ?>"
    data-tour-duration="<?= esc($durationLabel, 'attr') ?>"
    data-tour-departure="<?= esc($departureLabel !== '' ? $departureLabel : $copy['scheduleUpdating'], 'attr') ?>"
    data-tour-destination="<?= esc($locationName, 'attr') ?>"
    data-tour-type="<?= esc($typeLabel, 'attr') ?>"
    data-tour-departure-from="<?= esc($departureFrom, 'attr') ?>"
    data-tour-travelers="<?= esc($travelerLabel, 'attr') ?>"
    data-tour-room="<?= esc($singleRoomSupplementLabel, 'attr') ?>"
    data-tour-highlight="<?= esc($badgeText, 'attr') ?>"
    data-tour-included="<?= esc($tourToolIncluded, 'attr') ?>">
    <meta itemprop="name" content="<?= esc($title, 'attr') ?>">
    <meta itemprop="url" content="<?= esc($link, 'attr') ?>">
    <meta itemprop="image" content="<?= esc($image, 'attr') ?>">

    <div class="package-img-wrap tp-tour-card__media">
        <a class="package-img tp-tour-card__image" href="<?= esc($link, 'attr') ?>" aria-label="<?= esc($ariaLabel, 'attr') ?>">
            <img
                src="<?= esc($image, 'attr') ?>"
                <?php if ($imageSrcset !== ''): ?>srcset="<?= esc($imageSrcset, 'attr') ?>" sizes="(max-width: 575px) calc(100vw - 40px), (max-width: 991px) 50vw, 420px"<?php endif; ?>
                alt="<?= esc($title, 'attr') ?>"
                loading="lazy"
                decoding="async"
                width="420"
                height="280">
        </a>

        <div class="tp-tour-card__media-top">
            <?php if ($badgeText !== ''): ?>
                <span class="tp-tour-card__badge"><?= esc($badgeText) ?></span>
            <?php endif; ?>
            <span class="tp-tour-card__type"><?= esc($typeLabel) ?></span>
        </div>

        <div class="tp-tour-card__tools" aria-label="<?= esc($tourToolCopy['toolsLabel'], 'attr') ?>">
            <button
                type="button"
                class="tp-tour-tool-btn"
                data-tour-action="wishlist"
                data-label-add="<?= esc($tourToolCopy['wishlist'], 'attr') ?>"
                data-label-remove="<?= esc($tourToolCopy['wishlistSaved'], 'attr') ?>"
                aria-pressed="false">
                <i class="bi bi-heart" aria-hidden="true"></i>
                <span data-tour-action-text><?= esc($tourToolCopy['wishlist']) ?></span>
            </button>
            <button
                type="button"
                class="tp-tour-tool-btn"
                data-tour-action="compare"
                data-label-add="<?= esc($tourToolCopy['compare'], 'attr') ?>"
                data-label-remove="<?= esc($tourToolCopy['compareSaved'], 'attr') ?>"
                aria-pressed="false">
                <i class="bi bi-bar-chart" aria-hidden="true"></i>
                <span data-tour-action-text><?= esc($tourToolCopy['compare']) ?></span>
            </button>
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
                            <?= esc($departureFromLabel) ?>
                        </span>
                        <strong><?= esc($departureFrom) ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tp-tour-card__footer" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <div class="tp-tour-card__price">
                    <span><?= esc($copy['pricePrefix']) ?></span>
                    <strong><?= esc($priceLabel !== '' ? $priceLabel : $t('tour.sidebar.checkAvailability')) ?></strong>
                    <?php if ($loyaltyPoints > 0): ?>
                        <small class="tp-tour-card__points" title="<?= esc($locale === 'en' ? 'Actual points are based on the paid booking amount.' : 'Điểm thực nhận được tính theo số tiền booking đã thanh toán.', 'attr') ?>">
                            <i class="bi bi-stars" aria-hidden="true"></i>
                            <?= esc($loyaltyCopy) ?>
                        </small>
                    <?php endif; ?>
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
