<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
if (! function_exists('tour_detail_html')) {
    function tour_detail_html(?string $html): string
    {
        return trim(strip_tags((string) $html, '<p><br><strong><em><u><ul><ol><li>'));
    }
}

if (! function_exists('render_review_stars')) {
    function render_review_stars(float $rating): string
    {
        $full = (int) floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = max(0, 5 - $full - $half);
        $html = '';

        for ($i = 0; $i < $full; $i++) {
            $html .= '<li><i class="bi bi-star-fill"></i></li>';
        }

        if ($half) {
            $html .= '<li><i class="bi bi-star-half"></i></li>';
        }

        for ($i = 0; $i < $empty; $i++) {
            $html .= '<li><i class="bi bi-star"></i></li>';
        }

        return $html;
    }
}

$locale = service('request')->getLocale();
$gallery = array_values(array_filter($tour['media'] ?? [], static fn(array $item): bool => ($item['type'] ?? '') === 'gallery'));
$departures = array_values(array_filter($tour['departures'] ?? [], static fn($item): bool => is_array($item) && ! empty($item['date'])));
$firstDeparture = $departures[0] ?? null;
$adultPrice = (float) ($firstDeparture['price'] ?? $tour['price']['amount'] ?? 0);
$adultPrice = $adultPrice > 0 ? $adultPrice : (float) ($tour['price']['amount'] ?? 0);
$childPrice = $adultPrice * 0.85;
$infantPrice = $adultPrice * 0.25;
$maxTravelers = max(1, (int) ($tour['max_travelers'] ?? 15));
$departureLabel = (string) ($firstDeparture['date_label'] ?? $tour['departure'] ?? '');
$hasBookableDepartures = $firstDeparture !== null;
$departureOptions = [];
foreach ($departures as $departure) {
    $departureAdultPrice = (float) ($departure['price'] ?? 0);
    $departureAdultPrice = $departureAdultPrice > 0 ? $departureAdultPrice : (float) ($tour['price']['amount'] ?? 0);
    $departureAdultPrice = $departureAdultPrice > 0 ? $departureAdultPrice : $adultPrice;
    $departureSlots = (int) ($departure['available_slots'] ?? 0);
    $departureMaxTravelers = $departureSlots > 0 ? min($maxTravelers, $departureSlots) : $maxTravelers;

    $departureOptions[] = [
        'id' => (int) ($departure['id'] ?? 0),
        'date' => (string) ($departure['date'] ?? ''),
        'label' => (string) ($departure['date_label'] ?? ''),
        'available_slots' => $departureSlots,
        'max_travelers' => $departureMaxTravelers,
        'adult_price' => $departureAdultPrice,
        'child_price' => round($departureAdultPrice * 0.85, 0),
        'infant_price' => round($departureAdultPrice * 0.25, 0),
    ];
}
$reviewSummary = $tour['review_summary'] ?? ['count' => 0, 'overall' => 0, 'destination' => 0, 'transport' => 0, 'value' => 0];

$reviewAverage = ($reviewSummary['overall'] + $reviewSummary['destination'] + $reviewSummary['transport'] + $reviewSummary['value']) / 4;
$reviews = $tour['reviews'] ?? [];
$reviewPages = array_chunk($reviews, 3);
$relatedTours = $relatedTours ?? [];
$googleEnabled = config(\Config\SocialAuth::class)->googleEnabled;
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);

$durationLabel = ($tour['duration']['days'] ?? 0) . ' ' . $t('tour.duration.days') . ' ' . ($tour['duration']['nights'] ?? 0) . ' ' . $t('tour.duration.nights');
$reviewLabel = match (true) {
    $reviewSummary['overall'] >= 4.5 => $t('tour.reviewLabel.excellent'),
    $reviewSummary['overall'] >= 4.0 => $t('tour.reviewLabel.veryGood'),
    $reviewSummary['overall'] >= 3.5 => $t('tour.reviewLabel.good'),
    $reviewSummary['overall'] >= 3.0 => $t('tour.reviewLabel.average'),
    $reviewSummary['overall'] >= 2.0 => $t('tour.reviewLabel.poor'),
    $reviewSummary['overall'] > 0 => $t('tour.reviewLabel.terrible'),
    default => $t('tour.reviewLabel.none'),
};
$reviewMetrics = [
    'overall' => $t('tour.reviewMetric.overall'),
    'destination' => $t('tour.reviewMetric.destination'),
    'transport' => $t('tour.reviewMetric.transport'),
    'value' => $t('tour.reviewMetric.value'),
];
$enquiryLabels = [
    'title' => $t('tour.enquiry.title'),
    'intro' => $t('tour.enquiry.intro'),
    'tour' => $t('tour.enquiry.tour'),
    'name' => $t('tour.enquiry.name'),
    'email' => $t('tour.enquiry.email'),
    'phone' => $t('tour.enquiry.phone'),
    'date' => $t('tour.enquiry.date'),
    'travelers' => $t('tour.enquiry.travelers'),
    'message' => $t('tour.enquiry.message'),
    'agree' => $t('tour.enquiry.agree'),
    'submit' => $t('tour.enquiry.submit'),
];
?>

<div class="breadcrumb-section two">
    <div class="home2-banner-slider">
        <div class="banner-bg">
            <img class="tour-detail-hero-img" src="<?= esc($tour['image']) ?>" alt="<?= esc($tour['title']) ?>" loading="eager" fetchpriority="high" decoding="async" width="1920" height="760">
        </div>
    </div>
    <div class="banner-content-wrap">
        <div class="container">
            <div class="banner-content">
                <h1><?= esc($tour['title']) ?></h1>
                <div class="batch">
                    <span><?= esc($durationLabel) ?><?= $departureLabel !== '' ? ' | ' . esc($t('tour.booking.departurePrefix')) . ' ' . esc($departureLabel) : '' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal rating-modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"><button type="button" class="close-btn" data-bs-dismiss="modal"
                aria-label="<?= esc(lang('Frontend.common.close', [], $locale)) ?>"><i class="bi bi-x-lg"></i></button>
            <div class="modal-body">
                <h4 class="modal-title" id="ratingModalLabel"><?= esc($t('tour.review.title')) ?></h4>
                <form class="review-form-wrapper" data-tour-review-form method="post" action="<?= localized_url('tour/reviews') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tour_id" value="<?= esc($tour['id'] ?? 0) ?>">
                    <ul class="star-rating-list">
                        <?php foreach ($reviewMetrics as $metricKey => $metricLabel): ?>
                            <li>
                                <span><?= esc($metricLabel) ?></span>
                                <div class="rating-container" data-rating-input="<?= esc($metricKey) ?>">
                                    <input type="hidden" name="rating_<?= esc($metricKey) ?>" value="0">
                                    <?php for ($star = 1; $star <= 5; $star++): ?>
                                        <button type="button" class="rating-star-btn" data-value="<?= esc($star) ?>" aria-label="<?= esc($metricLabel . ' ' . $star . ' sao') ?>">
                                            <i class="bi bi-star star-icon"></i>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="row g-4 mb-50">
                        <div class="col-lg-12">
                            <div class="form-inner"><label><?= esc($t('tour.review.content')) ?></label><textarea name="content"
                                    placeholder="<?= esc($t('tour.review.contentPlaceholder')) ?>"></textarea></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner"><label><?= esc($t('tour.review.email')) ?></label><input type="email" name="reviewer_email"
                                    placeholder="<?= esc($t('tour.review.email')) ?>"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner"><label><?= esc($t('tour.review.name')) ?></label><input type="text" name="reviewer_name" placeholder="<?= esc($t('tour.review.name')) ?>">
                            </div>
                        </div>
                        <div class="col-md-12 d-none" data-review-message></div>
                        <div class="col-md-12 d-none" data-review-errors></div>
                        <div class="col-md-12">
                            <p class="mb-0"><?= esc($t('tour.review.note')) ?></p>
                        </div>
                    </div>
                    <div class="form-inner"><button type="submit" class="primary-btn1 black-bg"><span><?= esc($t('tour.review.submit')) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span><span><?= esc($t('tour.review.submit')) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span></button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal booking-modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"><button type="button" class="close-btn" data-bs-dismiss="modal"
                aria-label="<?= esc(lang('Frontend.common.close', [], $locale)) ?>"><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M2.00247 0.500545C1.79016 0.505525 1.58918 0.582706 1.4362 0.735547L0.694403 1.479C0.345704 1.82743 0.389689 2.43243 0.79164 2.83493L3.00694 5.05341L0.79164 7.27092C0.389689 7.67328 0.345566 8.27842 0.694403 8.62753L1.4362 9.37044C1.7849 9.71872 2.38879 9.67543 2.7913 9.27293L5.00659 7.05473L7.22189 9.27293C7.62467 9.67543 8.22898 9.71872 8.57699 9.37044L9.31989 8.62753C9.6679 8.27856 9.62461 7.67342 9.22182 7.27092L7.00653 5.05341L9.22182 2.83493C9.62461 2.43243 9.6679 1.82743 9.31989 1.479L8.57699 0.735547C8.22898 0.386433 7.62467 0.430557 7.22189 0.833614L5.00659 3.05126L2.7913 0.833753C2.56515 0.606635 2.27482 0.493906 2.00247 0.500545Z">
                    </path>
                </svg></button>
            <div class="modal-header">
                <h4><?= esc($t('tour.booking.summaryTitle')) ?></h4>
                <p>
                    <?= esc($t('tour.booking.summaryDesc')) ?>
                </p>
                    
            </div>
            <div class="modal-body">
                <form action="<?= localized_url('booking/proceed') ?>" method="post" data-booking-proceed-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="tour_id" value="<?= esc((string) ($tour['id'] ?? 0)) ?>">
                    <input type="hidden" name="tour_title" value="<?= esc($tour['title']) ?>">
                    <input type="hidden" name="tour_image" value="<?= esc($tour['image']) ?>">
                    <input type="hidden" name="tour_link" value="<?= esc(current_url()) ?>">
                    <input type="hidden" name="departure_date" value="<?= esc((string) ($firstDeparture['date'] ?? '')) ?>" data-booking-departure-date-hidden>
                    <input type="hidden" name="departure_label" value="<?= esc($departureLabel) ?>" data-booking-departure-label-hidden>
                    <input type="hidden" name="duration_label" value="<?= esc($durationLabel) ?>">
                    <input type="hidden" name="adult_price" value="<?= esc((string) $adultPrice) ?>" data-booking-price-hidden="adult">
                    <input type="hidden" name="child_price" value="<?= esc((string) $childPrice) ?>" data-booking-price-hidden="child">
                    <input type="hidden" name="infant_price" value="<?= esc((string) $infantPrice) ?>" data-booking-price-hidden="infant">
                    <input type="hidden" name="max_travelers" value="<?= esc((string) $maxTravelers) ?>" data-booking-max-travelers-hidden>
                    <input type="hidden" name="adult_quantity" value="1" data-booking-quantity-hidden="adult">
                    <input type="hidden" name="child_quantity" value="0" data-booking-quantity-hidden="child">
                    <input type="hidden" name="infant_quantity" value="0" data-booking-quantity-hidden="infant">
                    <input type="hidden" name="grand_total" value="<?= esc((string) $adultPrice) ?>" data-booking-grand-total-hidden>
                    <?php if ($departureOptions !== []): ?>
                    <div class="tour-departure-picker"
                        data-departure-selector
                        data-departure-prefix="<?= esc($t('tour.booking.departurePrefix'), 'attr') ?>"
                        data-departures="<?= esc(json_encode($departureOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr') ?>">
                        <button type="button" class="tour-departure-trigger" data-departure-toggle aria-expanded="false">
                            <span class="tour-departure-trigger-copy"><?= esc($t('tour.booking.departureSelect')) ?></span>
                            <strong data-departure-current-label><?= esc($departureLabel) ?></strong>
                            <span data-departure-current-price><?= esc(number_format((float) ($departureOptions[0]['adult_price'] ?? $adultPrice), 0, ',', '.') . 'đ') ?></span>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="tour-departure-menu" data-departure-menu>
                            <?php foreach ($departureOptions as $index => $departureOption): ?>
                                <?php $timestamp = strtotime((string) $departureOption['date']); ?>
                                <button type="button" class="tour-departure-option<?= $index === 0 ? ' is-active' : '' ?>" data-departure-option data-departure-date="<?= esc((string) $departureOption['date']) ?>">
                                    <strong><?= esc($timestamp ? date('d/m', $timestamp) : (string) $departureOption['label']) ?></strong>
                                    <span><?= esc($timestamp ? date('Y', $timestamp) : '') ?></span>
                                    <em><?= esc(number_format((float) $departureOption['adult_price'], 0, ',', '.') . 'đ') ?></em>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <div class="tour-departure-meta" data-departure-meta>
                            <?= esc($t('tour.booking.departureSlots', [(string) ($departureOptions[0]['max_travelers'] ?? $maxTravelers)])) ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-warning"><?= esc($t('tour.booking.noDepartures')) ?></div>
                    <?php endif; ?>
                <div class="package-list">
                    <div class="accordion accordion-flush" id="accordionFlushPackage">
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <div class="accordion-button" role="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-package-collapseOne" aria-expanded="false"
                                    aria-controls="flush-package-collapseOne">
                                    <div class="batch"><span><?= esc($t('tour.booking.details')) ?></span></div>
                                    <div class="title-area"><span class="check"></span>
                                        <h6><?= esc($tour['title']) ?></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-collapse collapse show"
                                aria-labelledby="flush-package-headingOne" data-bs-parent="#accordionFlushPackage">
                                <div class="accordion-body">
                                    <div class="tour-info-and-calculate-area">
                                        <p data-booking-tour-info><?= esc($durationLabel) ?><?= $departureLabel !== '' ? ' | ' . esc($t('tour.booking.departurePrefix')) . ' ' . esc($departureLabel) : '' ?></p>
                                    </div>
                                    <div class="additional-service-area" data-max-travelers="<?= esc($maxTravelers) ?>" data-base-max-travelers="<?= esc($maxTravelers) ?>" data-duration-label="<?= esc($durationLabel, 'attr') ?>" data-departure-prefix="<?= esc($t('tour.booking.departurePrefix'), 'attr') ?>">
                                        <h6><?= esc($t('tour.booking.travelersTitle')) ?> <sub data-booking-travelers-max-label>(<?= esc($t('tour.booking.travelersMax', [$maxTravelers])) ?>)</sub></h6>
                                        <ul class="service-list booking-service-list">
                                            <li class="booking-service-item" data-service-type="adult" data-unit-price="<?= $adultPrice ?>" data-min="1">
                                                <div class="service-info-wrap">
                                                    <div class="service-info">
                                                        <h6><?= esc($t('tour.booking.adult')) ?></h6>
                                                        <p data-booking-price-label="adult"><?= esc(number_format($adultPrice, 0, ',', '.')) ?></p>
                                                    </div>
                                                </div>
                                                <div class="pricing-and-count-area">
                                                    <div class="quantity-counter"><a data-type="adult" class="quantity__minus"><i class="bi bi-dash"></i></a><input type="text" class="quantity__input" name="adult_service_quantity" value="1" data-min="1"><a data-type="adult" class="quantity__plus"><i class="bi bi-plus"></i></a></div>
                                                </div>
                                            </li>
                                            <li class="booking-service-item" data-service-type="child" data-unit-price="<?= $childPrice ?>" data-min="0">
                                                <div class="service-info-wrap">
                                                    <div class="service-info">
                                                        <h6><?= esc($t('tour.booking.child')) ?></h6>
                                                        <p data-booking-price-label="child"><?= esc(number_format($childPrice, 0, ',', '.')) ?></p>
                                                    </div>
                                                </div>
                                                <div class="pricing-and-count-area">
                                                    <div class="quantity-counter"><a data-type="child" class="quantity__minus"><i class="bi bi-dash"></i></a><input type="text" class="quantity__input" name="child_service_quantity" value="0" data-min="0"><a data-type="child" class="quantity__plus"><i class="bi bi-plus"></i></a></div>
                                                </div>
                                            </li>
                                            <li class="booking-service-item" data-service-type="infant" data-unit-price="<?= $infantPrice ?>" data-min="0">
                                                <div class="service-info-wrap">
                                                    <div class="service-info">
                                                        <h6><?= esc($t('tour.booking.infant')) ?></h6>
                                                        <p data-booking-price-label="infant"><?= esc(number_format($infantPrice, 0, ',', '.')) ?>₫</p>
                                                    </div>
                                                </div>
                                                <div class="pricing-and-count-area">
                                                    <div class="quantity-counter"><a data-type="infant" class="quantity__minus"><i class="bi bi-dash"></i></a><input type="text" class="quantity__input" name="infant_service_quantity" value="0" data-min="0"><a data-type="infant" class="quantity__plus"><i class="bi bi-plus"></i></a></div>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="booking-total-area">
                                            <span class="booking-total-label"><?= esc($t('tour.booking.total')) ?> </span>
                                            <strong class="booking-grand-total"><?= esc(number_format($adultPrice, 0, ',', '.')) ?></strong>
                                        </div>
                                    </div>
                                    <div class="btn-area">
                                        <button class="primary-btn1 two" type="submit" <?= $hasBookableDepartures ? '' : 'disabled' ?>>
                                            <span>
                                                <?= esc($t('tour.booking.bookNow')) ?>
                                                <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                                    </path>
                                                </svg>
                                            </span>
                                            <span>
                                                <?= esc($t('tour.booking.bookNow')) ?>
                                                <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                                    </path>
                                                </svg>
                                            </span>
                                        </button>
                                        <div class="alert alert-danger d-none mt-3" data-booking-proceed-error></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal rating-modal fade" id="proceedBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content"><button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="<?= esc(lang('Frontend.common.close', [], $locale)) ?>"><i class="bi bi-x-lg"></i></button>
            <div class="modal-body">
                <div class="row g-0 proceed-booking-modal">
                    <div class="col-lg-6 proceed-booking-col">
                        <div class="modal-login-form-wrapper h-100">
                            <h6><?= esc($t('tour.account.hasAccount')) ?></h6>
                            <form action="<?= \App\Data\LocalizedPathCatalog::url('auth.login', $locale) ?>" method="post" data-booking-login-form>
                                <?= csrf_field() ?>
                                <input type="hidden" name="return_to" value="<?= esc(current_url()) ?>">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="form-inner">
                                            <label><?= esc($t('tour.account.usernameOrEmail')) ?></label>
                                            <input type="text" name="identity" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-inner">
                                            <label><?= esc($t('tour.account.password')) ?></label>
                                            <input type="password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-danger d-none" data-booking-login-error></div>
                                    </div>
                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <button type="submit" class="primary-btn1 two"><span><?= esc($t('auth.login')) ?></span><span><?= esc($t('auth.login')) ?></span></button>
                                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.forgotPassword', $locale) ?>"><?= esc($t('tour.account.forgotPassword')) ?></a>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 proceed-booking-col">
                        <div class="modal-login-form-wrapper h-100 text-center">
                            <h6><?= esc($t('tour.account.noAccount')) ?></h6>
                            <p><?= esc($t('tour.account.registerDesc')) ?></p>
                            <div class="d-grid gap-3">
                                <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.register', $locale) . '?return_to=' . rawurlencode(current_url()) ?>" class="primary-btn1 two w-100"><span><?= esc($t('tour.account.register')) ?></span><span><?= esc($t('tour.account.register')) ?></span></a>
                                <div class="proceed-booking-divider"><?= esc($t('tour.account.or')) ?></div>
                                <a href="<?= \App\Data\LocalizedPathCatalog::url('booking.guest', $locale) ?>" class="primary-btn1 two w-100"><span><?= esc($t('tour.account.continueGuest')) ?></span><span><?= esc($t('tour.account.continueGuest')) ?></span></a>
                            <?php if ($googleEnabled): ?>
                                            <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.google', $locale) . '?return_to=' . rawurlencode(current_url()) ?>" class="primary-btn transparent booking-google-btn w-100">
                                                <span>
                                                    <img class="booking-google-icon" src="<?= esc(base_url('assets/images/google-2025.png')) ?>" alt="Google Icon" loading="lazy" decoding="async" width="20" height="20"><?= esc(lang('Frontend.common.signInWithGoogle', [], $locale)) ?>
                                                </span>
                                            </a>
                                    <?php endif; ?>
                            </div>
                                                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal enquiry-modal fade" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content"><button type="button" class="close-btn" data-bs-dismiss="modal"
                aria-label="<?= esc(lang('Frontend.common.close', [], $locale)) ?>"><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M2.00247 0.500545C1.79016 0.505525 1.58918 0.582706 1.4362 0.735547L0.694403 1.479C0.345704 1.82743 0.389689 2.43243 0.79164 2.83493L3.00694 5.05341L0.79164 7.27092C0.389689 7.67328 0.345566 8.27842 0.694403 8.62753L1.4362 9.37044C1.7849 9.71872 2.38879 9.67543 2.7913 9.27293L5.00659 7.05473L7.22189 9.27293C7.62467 9.67543 8.22898 9.71872 8.57699 9.37044L9.31989 8.62753C9.6679 8.27856 9.62461 7.67342 9.22182 7.27092L7.00653 5.05341L9.22182 2.83493C9.62461 2.43243 9.6679 1.82743 9.31989 1.479L8.57699 0.735547C8.22898 0.386433 7.62467 0.430557 7.22189 0.833614L5.00659 3.05126L2.7913 0.833753C2.56515 0.606635 2.27482 0.493906 2.00247 0.500545Z">
                    </path>
                </svg></button>
            <div class="modal-body">
                <h4 class="modal-title" id="enquiryModalLabel"><?= esc($enquiryLabels['title']) ?></h4>
                <p class="mb-4"><?= esc($enquiryLabels['intro']) ?></p>
                <form class="enquiry-form-wrapper" data-tour-enquiry-form method="post" action="<?= localized_url('tour/enquiry') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tour_id" value="<?= esc((string) ($tour['id'] ?? 0)) ?>">
                    <input type="hidden" name="tour_title" value="<?= esc($tour['title']) ?>">
                    <input type="hidden" name="tour_link" value="<?= esc(current_url()) ?>">
                    <div class="row g-4 mb-40">
                        <div class="col-md-12">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['tour']) ?></label>
                                <input type="text" value="<?= esc($tour['title']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['name']) ?></label>
                                <input type="text" name="full_name" placeholder="<?= esc($enquiryLabels['name']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['email']) ?></label>
                                <input type="email" name="email" placeholder="<?= esc($enquiryLabels['email']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['phone']) ?></label>
                                <input type="text" name="phone" placeholder="<?= esc($enquiryLabels['phone']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['date']) ?></label>
                                <div class="date-field-area">
                                    <input type="date" name="travel_date" min="<?= esc(date('Y-m-d')) ?>">
                                    <svg class="calender-icon" width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                        <g>
                                            <path d="M12.1953 1.09375H10.9375V0.4375C10.9375 0.195891 10.7416 0 10.5 0C10.2584 0 10.0625 0.195891 10.0625 0.4375V1.09375H3.9375V0.4375C3.9375 0.195891 3.74164 0 3.5 0C3.25836 0 3.0625 0.195891 3.0625 0.4375V1.09375H1.80469C0.809566 1.09375 0 1.90332 0 2.89844V12.1953C0 13.1904 0.809566 14 1.80469 14H12.1953C13.1904 14 14 13.1904 14 12.1953V2.89844C14 1.90332 13.1904 1.09375 12.1953 1.09375ZM13.125 12.1953C13.125 12.7088 12.7088 13.125 12.1953 13.125H1.80469C1.29123 13.125 0.875 12.7088 0.875 12.1953V4.94922C0.875 4.91296 0.889404 4.87818 0.915044 4.85254C0.940684 4.8269 0.975459 4.8125 1.01172 4.8125H12.9883C13.0245 4.8125 13.0593 4.8269 13.085 4.85254C13.1106 4.87818 13.125 4.91296 13.125 4.94922V12.1953Z"></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['travelers']) ?></label>
                                <input type="text" name="travelers" placeholder="<?= esc($t('tour.enquiry.travelersPlaceholder')) ?>">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-inner">
                                <label><?= esc($enquiryLabels['message']) ?></label>
                                <textarea name="message" placeholder="<?= esc($enquiryLabels['message']) ?>"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-inner2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tourEnquiryConsent" checked disabled>
                                    <label class="form-check-label" for="tourEnquiryConsent"><?= esc($enquiryLabels['agree']) ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 d-none" data-enquiry-message></div>
                        <div class="col-md-12 d-none" data-enquiry-errors></div>
                    </div>
                    <div class="form-inner"><button type="submit" class="primary-btn1 black-bg"><span><?= esc($enquiryLabels['submit']) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span><span><?= esc($enquiryLabels['submit']) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span></button></div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="package-details-page pt-100 mb-100">
    <div class="container">
        <div class="row g-lg-4 gy-5 justify-content-between">
            <div class="col-xl-7 col-lg-8">
                <div class="package-details-warpper">
                    <div class="package-info-wrap mb-60">
                        <h4><?= esc($t('tour.overview.title')) ?></h4>
                        <p><?= tour_detail_html($tour['description']) ?></p>
                        <ul class="package-info-list">
                            <li><svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M8.49943 13.5002H4.99992C4.86733 13.5002 4.74018 13.5529 4.64642 13.6467C4.55267 13.7404 4.5 13.8676 4.5 14.0002V18.9994C4.5 19.132 4.55267 19.2592 4.64642 19.3529C4.74018 19.4467 4.86733 19.4993 4.99992 19.4993H8.49943C8.63202 19.4993 8.75918 19.4467 8.85293 19.3529C8.94669 19.2592 8.99936 19.132 8.99936 18.9994V14.0002C8.99936 13.8676 8.94669 13.7404 8.85293 13.6467C8.75918 13.5529 8.63202 13.5002 8.49943 13.5002ZM7.99951 18.4995H5.49984V14.5001H7.99951V18.4995ZM16.9982 13.5002H13.4987C13.3661 13.5002 13.2389 13.5529 13.1452 13.6467C13.0514 13.7404 12.9987 13.8676 12.9987 14.0002V18.9994C12.9987 19.132 13.0514 19.2592 13.1452 19.3529C13.2389 19.4467 13.3661 19.4993 13.4987 19.4993H16.9982C17.1308 19.4993 17.2579 19.4467 17.3517 19.3529C17.4454 19.2592 17.4981 19.132 17.4981 18.9994V14.0002C17.4981 13.8676 17.4454 13.7404 17.3517 13.6467C17.2579 13.5529 17.1308 13.5002 16.9982 13.5002ZM16.4982 18.4995H13.9986V14.5001H16.4982V18.4995ZM16.9982 21.499H13.4987C13.3661 21.499 13.2389 21.5517 13.1452 21.6455C13.0514 21.7392 12.9987 21.8664 12.9987 21.999V26.9982C12.9987 27.1308 13.0514 27.258 13.1452 27.3517C13.2389 27.4455 13.3661 27.4982 13.4987 27.4982H16.9982C17.1308 27.4982 17.2579 27.4455 17.3517 27.3517C17.4454 27.258 17.4981 27.1308 17.4981 26.9982V21.999C17.4981 21.8664 17.4454 21.7392 17.3516 21.6455C17.2579 21.5517 17.1307 21.4991 16.9982 21.499ZM16.4982 26.4983H13.9986V22.4989H16.4982V26.4983ZM24.997 13.5002H21.4975C21.3649 13.5002 21.2378 13.5529 21.144 13.6467C21.0503 13.7404 20.9976 13.8676 20.9976 14.0002V18.9994C20.9976 19.132 21.0503 19.2592 21.144 19.3529C21.2378 19.4467 21.3649 19.4993 21.4975 19.4993H24.997C25.1296 19.4993 25.2568 19.4467 25.3505 19.3529C25.4443 19.2592 25.497 19.132 25.497 18.9994V14.0002C25.497 13.8676 25.4443 13.7404 25.3505 13.6467C25.2568 13.5529 25.1296 13.5002 24.997 13.5002ZM24.4971 18.4995H21.9975V14.5001H24.4971V18.4995ZM8.49943 21.499H4.99992C4.86733 21.499 4.74018 21.5517 4.64642 21.6455C4.55267 21.7392 4.5 21.8664 4.5 21.999V26.9982C4.5 27.1308 4.55267 27.258 4.64642 27.3517C4.74018 27.4455 4.86733 27.4982 4.99992 27.4982H8.49943C8.63202 27.4982 8.75918 27.4455 8.85293 27.3517C8.94669 27.258 8.99936 27.1308 8.99936 26.9982V21.999C8.99934 21.8664 8.94666 21.7392 8.85291 21.6455C8.75916 21.5517 8.63202 21.4991 8.49943 21.499ZM7.99951 26.4983H5.49984V22.4989H7.99951V26.4983ZM24.997 21.499H21.4975C21.3649 21.499 21.2378 21.5517 21.144 21.6455C21.0503 21.7392 20.9976 21.8664 20.9976 21.999V26.9982C20.9976 27.1308 21.0503 27.258 21.144 27.3517C21.2378 27.4455 21.3649 27.4982 21.4975 27.4982H24.997C25.1296 27.4982 25.2568 27.4455 25.3505 27.3517C25.4443 27.258 25.497 27.1308 25.497 26.9982V21.999C25.4969 21.8664 25.4443 21.7392 25.3505 21.6455C25.2568 21.5517 25.1296 21.4991 24.997 21.499ZM24.4971 26.4983H21.9975V22.4989H24.4971V26.4983Z">
                                        </path>
                                        <path
                                            d="M27.996 28.998V12.0004H28.4959C28.6285 12.0004 28.7556 11.9477 28.8494 11.854C28.9431 11.7602 28.9958 11.6331 28.9958 11.5005V9.00088C28.9958 8.93523 28.9829 8.87022 28.9578 8.80957C28.9326 8.74891 28.8958 8.6938 28.8494 8.64738C28.803 8.60096 28.7479 8.56413 28.6872 8.53901C28.6266 8.51389 28.5615 8.50096 28.4959 8.50096H27.996V6.50127C27.996 6.36868 27.9433 6.24152 27.8496 6.14777C27.7558 6.05402 27.6286 6.00135 27.4961 6.00135H26.4962V0.502089C26.4962 0.369501 26.4435 0.242344 26.3498 0.14859C26.256 0.0548369 26.1289 0.00216675 25.9963 0.00216675H3.99943C3.86685 0.00216675 3.73969 0.0548369 3.64594 0.14859C3.55218 0.242344 3.49951 0.369501 3.49951 0.502089V6.00129H2.49967C2.36708 6.00129 2.23992 6.05396 2.14617 6.14771C2.05242 6.24147 1.99975 6.36862 1.99975 6.50121V8.5009H1.49982C1.36724 8.5009 1.24008 8.55357 1.14633 8.64732C1.05257 8.74107 0.999902 8.86823 0.999902 9.00082V11.5004C0.999902 11.633 1.05257 11.7602 1.14633 11.8539C1.24008 11.9477 1.36724 12.0003 1.49982 12.0003H1.99975V28.9979H0V29.9977H30V28.9979H27.996V28.998ZM4.49936 1.00201H25.4963V6.00129H4.49936V1.00201ZM2.99959 7.00113H26.9961V8.5009H2.99959V7.00113ZM26.9961 28.998H2.99959V12.0004H26.9961V28.998H26.9961ZM2.49967 11.0006H1.99975V9.5008H27.996V11.0006H2.49967Z">
                                        </path>
                                        <path
                                            d="M14.2307 2.71242C14.1512 2.50922 14.0395 2.33373 13.8955 2.18601C13.7515 2.0383 13.5783 1.9224 13.3758 1.83844C13.1733 1.75447 12.9492 1.71246 12.7032 1.71246C12.4572 1.71246 12.2326 1.75406 12.0294 1.83732C11.8262 1.92058 11.6522 2.03642 11.5075 2.1849C11.3628 2.33338 11.2507 2.50922 11.1712 2.71248C11.0917 2.91568 11.052 3.13652 11.052 3.375C11.052 3.61347 11.0917 3.83431 11.1712 4.03752C11.2507 4.24072 11.3628 4.41656 11.5075 4.5651C11.6522 4.71357 11.8262 4.82941 12.0294 4.91267C12.2326 4.99594 12.4572 5.03754 12.7032 5.03754C12.9492 5.03754 13.1733 4.99594 13.3758 4.91267C13.5783 4.82947 13.7515 4.71357 13.8955 4.5651C14.0395 4.41662 14.1512 4.24078 14.2307 4.03752C14.3102 3.83431 14.35 3.61347 14.35 3.375C14.35 3.13646 14.3102 2.91562 14.2307 2.71242ZM13.6581 3.85523C13.6109 3.99697 13.543 4.11697 13.4545 4.21517C13.3661 4.31344 13.2584 4.38879 13.1317 4.44129C13.0049 4.49379 12.8621 4.52004 12.7031 4.52004C12.5442 4.52004 12.4009 4.49379 12.2735 4.44129C12.146 4.38879 12.0376 4.31344 11.9484 4.21517C11.8591 4.11691 11.7905 3.99697 11.7425 3.85523C11.6946 3.71349 11.6705 3.55342 11.6705 3.37494C11.6705 3.19646 11.6946 3.03639 11.7425 2.89465C11.7905 2.75291 11.8591 2.63256 11.9484 2.53359C12.0376 2.43463 12.146 2.35887 12.2735 2.30637C12.401 2.25387 12.5442 2.22762 12.7031 2.22762C12.8621 2.22762 13.0049 2.25387 13.1317 2.30637C13.2584 2.35887 13.366 2.43463 13.4545 2.53359C13.543 2.63256 13.6109 2.75291 13.6581 2.89465C13.7054 3.03639 13.729 3.19646 13.729 3.37494C13.729 3.55342 13.7054 3.71349 13.6581 3.85523ZM9.96305 1.74844V3.14771H8.43779V1.74844H7.82812V5.00144H8.43779V3.57967H9.96305V5.00144H10.5727V1.74844H9.96305ZM20.5096 4.50199V1.74844H19.9044V5.00144H21.8121V4.50199H20.5096ZM14.3769 1.74844V2.2456H15.3601V5.00144H15.9652V2.2456H16.9438V1.74844H14.3769ZM19.3802 2.22984V1.74844H17.3285V5.00144H19.3802V4.51775H17.9382V3.59765H19.0743V3.13195H17.9382V2.22984H19.3802Z">
                                        </path>
                                    </g>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.overview.hotel')) ?></span><strong><?= esc($t('tour.overview.hotelValue')) ?></strong></div>
                            </li>
                            <li><svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 7.5C13.5166 7.5 12.0666 7.93987 10.8332 8.76398C9.59986 9.58809 8.63856 10.7594 8.07091 12.1299C7.50325 13.5003 7.35472 15.0083 7.64411 16.4632C7.9335 17.918 8.64781 19.2544 9.6967 20.3033C10.7456 21.3522 12.082 22.0665 13.5368 22.3559C14.9917 22.6453 16.4997 22.4968 17.8701 21.9291C19.2406 21.3614 20.4119 20.4001 21.236 19.1668C22.0601 17.9334 22.5 16.4834 22.5 15C22.4977 13.0116 21.7068 11.1052 20.3008 9.6992C18.8948 8.29317 16.9884 7.50226 15 7.5ZM15 21.5625C13.7021 21.5625 12.4333 21.1776 11.3541 20.4565C10.2749 19.7354 9.43374 18.7105 8.93704 17.5114C8.44034 16.3122 8.31038 14.9927 8.5636 13.7197C8.81682 12.4467 9.44183 11.2774 10.3596 10.3596C11.2774 9.44183 12.4467 8.81681 13.7197 8.5636C14.9927 8.31038 16.3122 8.44034 17.5114 8.93704C18.7105 9.43374 19.7354 10.2749 20.4565 11.3541C21.1776 12.4333 21.5625 13.7021 21.5625 15C21.5605 16.7399 20.8685 18.4079 19.6382 19.6382C18.4079 20.8685 16.7399 21.5605 15 21.5625Z">
                                    </path>
                                    <path
                                        d="M28.5937 2.34375H27.6562C26.5377 2.34501 25.4654 2.78988 24.6745 3.58078C23.8836 4.37168 23.4387 5.444 23.4375 6.5625V7.55859C22.4473 6.43566 21.2428 5.52171 19.8948 4.87032C18.5468 4.21892 17.0823 3.84319 15.5872 3.76515C14.0921 3.68711 12.5964 3.90833 11.1879 4.41585C9.77944 4.92337 8.48642 5.70696 7.3847 6.7207L7.13398 2.78262C7.12506 2.65931 7.06779 2.54452 6.97464 2.46324C6.88149 2.38195 6.76 2.34076 6.63662 2.34863C6.51324 2.35649 6.39797 2.41278 6.3159 2.50524C6.23382 2.59769 6.1916 2.71883 6.19841 2.84227L6.49607 7.5174C6.28015 7.66934 5.66638 7.89756 4.68745 7.95504V2.8125C4.68745 2.68818 4.63807 2.56895 4.55016 2.48104C4.46225 2.39314 4.34302 2.34375 4.2187 2.34375C4.09438 2.34375 3.97515 2.39314 3.88725 2.48104C3.79934 2.56895 3.74995 2.68818 3.74995 2.8125V7.95498C2.82693 7.89979 2.18275 7.68961 1.9414 7.51594L2.239 2.84232C2.24581 2.71888 2.20358 2.59775 2.12151 2.50529C2.03944 2.41284 1.92416 2.35655 1.80078 2.34869C1.67741 2.34082 1.55592 2.38201 1.46277 2.4633C1.36962 2.54458 1.31235 2.65937 1.30343 2.78268L0.941262 8.4699C0.909135 9.01667 1.08368 9.55555 1.43029 9.97965C1.77689 10.4037 2.27023 10.6821 2.81245 10.7595V15.4688C2.68813 15.4688 2.5689 15.5181 2.481 15.606C2.39309 15.694 2.3437 15.8132 2.3437 15.9375V24.8438C2.3437 25.341 2.54125 25.8179 2.89288 26.1696C3.24451 26.5212 3.72142 26.7188 4.2187 26.7188C4.71598 26.7188 5.1929 26.5212 5.54453 26.1696C5.89616 25.8179 6.0937 25.341 6.0937 24.8438V21.8718C7.28797 23.4181 8.86423 24.6269 10.6674 25.3793C12.4706 26.1316 14.4385 26.4016 16.3777 26.1627C18.3168 25.9237 20.1604 25.1841 21.727 24.0165C23.2936 22.849 24.5293 21.2937 25.3125 19.5037V24.8438C25.3125 25.341 25.51 25.8179 25.8616 26.1696C26.2133 26.5212 26.6902 26.7188 27.1875 26.7188C27.6847 26.7188 28.1616 26.5212 28.5133 26.1696C28.8649 25.8179 29.0625 25.341 29.0625 24.8438V2.8125C29.0625 2.68818 29.0131 2.56895 28.9252 2.48104C28.8373 2.39314 28.718 2.34375 28.5937 2.34375ZM5.1562 24.8438C5.1562 25.0924 5.05743 25.3308 4.88162 25.5067C4.7058 25.6825 4.46734 25.7812 4.2187 25.7812C3.97006 25.7812 3.73161 25.6825 3.55579 25.5067C3.37998 25.3308 3.2812 25.0924 3.2812 24.8438V16.4062H5.1562V24.8438ZM3.74995 15.4688V10.7812H4.68745V15.4688H3.74995ZM5.31669 9.84375H3.12071C2.95143 9.84376 2.78391 9.80926 2.6284 9.74236C2.47289 9.67546 2.33266 9.57755 2.21626 9.45463C2.09986 9.3317 2.00974 9.18634 1.95141 9.02742C1.89308 8.8685 1.86777 8.69935 1.877 8.53031C2.40259 8.74652 3.17573 8.90625 4.2187 8.90625C5.07529 8.90625 5.9355 8.78643 6.56029 8.53254C6.56926 8.7014 6.54375 8.87031 6.4853 9.02899C6.42686 9.18766 6.3367 9.33277 6.22034 9.45546C6.10398 9.57815 5.96384 9.67586 5.80848 9.74261C5.65312 9.80937 5.48579 9.84378 5.31669 9.84375ZM21.989 22.5831C20.855 23.6282 19.501 24.4057 18.0267 24.8583C16.5525 25.3109 14.9955 25.4272 13.4703 25.1984C11.9452 24.9697 10.4908 24.4018 9.2142 23.5366C7.93757 22.6714 6.87124 21.531 6.0937 20.1991V15.9375C6.0937 15.8132 6.04432 15.694 5.95641 15.606C5.8685 15.5181 5.74927 15.4688 5.62495 15.4688V10.7595C6.16757 10.682 6.66122 10.4034 7.00785 9.97878C7.35448 9.55419 7.52872 9.01475 7.49597 8.46762L7.46368 7.96131C8.51072 6.84001 9.79479 5.96649 11.2223 5.40442C12.6497 4.84236 14.1846 4.60592 15.7151 4.71233C17.2455 4.81875 18.733 5.26534 20.0689 6.01956C21.4049 6.77377 22.5557 7.8166 23.4375 9.07201V15.9375C23.4375 16.0618 23.4868 16.181 23.5747 16.269C23.6627 16.3569 23.7819 16.4062 23.9062 16.4062H25.2169C24.8931 18.7783 23.7515 20.9629 21.989 22.5831ZM28.125 24.8438C28.125 25.0924 28.0262 25.3308 27.8504 25.5067C27.6746 25.6825 27.4361 25.7812 27.1875 25.7812C26.9388 25.7812 26.7004 25.6825 26.5245 25.5067C26.3487 25.3308 26.25 25.0924 26.25 24.8438V16.4062H28.125V24.8438ZM28.125 15.4688H24.375V6.5625C24.3759 5.69256 24.7219 4.85853 25.3371 4.24339C25.9522 3.62824 26.7863 3.28223 27.6562 3.28125H28.125V15.4688Z">
                                    </path>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.overview.meals')) ?></span><strong><?= esc($t('tour.overview.mealsValue')) ?></strong></div>
                            </li>
                            <li><svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M19.6914 2.34375C19.368 2.34375 19.1055 2.60625 19.1055 2.92969C19.1055 3.25313 19.368 3.51562 19.6914 3.51562C20.0148 3.51562 20.2773 3.25313 20.2773 2.92969C20.2773 2.60625 20.0148 2.34375 19.6914 2.34375ZM10.3164 2.34375C9.99297 2.34375 9.73047 2.60625 9.73047 2.92969C9.73047 3.25313 9.99297 3.51562 10.3164 3.51562C10.6398 3.51562 10.9023 3.25313 10.9023 2.92969C10.9023 2.60625 10.6398 2.34375 10.3164 2.34375Z">
                                    </path>
                                    <path
                                        d="M27.1907 29.0089L23.3134 24.9582C24.8798 24.9021 26.137 23.6111 26.137 22.0312V4.64473C26.1361 3.69546 25.8069 2.77573 25.2052 2.04152C24.6038 1.30969 23.7613 0.805371 22.8333 0.621797C20.7491 0.208741 18.6295 0.000483935 16.5048 0L13.4093 0C11.3137 0.000427757 9.22333 0.207365 7.16836 0.617812C5.25791 1.00002 3.87135 2.69168 3.87135 4.64004V22.0312C3.87135 23.6244 5.14986 24.9237 6.73465 24.9592L2.81361 29.0064C2.7335 29.089 2.67952 29.1934 2.65839 29.3066C2.63726 29.4197 2.64994 29.5366 2.69483 29.6426C2.73972 29.7486 2.81483 29.839 2.9108 29.9026C3.00677 29.9661 3.11933 30 3.23443 30H7.42389C7.51649 30 7.60777 29.9781 7.69025 29.936C7.77272 29.8939 7.84405 29.8328 7.89838 29.7578L9.67576 27.3047H20.3847L22.1621 29.7579C22.2164 29.8329 22.2878 29.8939 22.3702 29.936C22.4527 29.978 22.544 30 22.6366 30H26.7674C26.8822 30 26.9945 29.9663 27.0903 29.903C27.1861 29.8397 27.2612 29.7497 27.3063 29.6442C27.3513 29.5386 27.3644 29.4221 27.3439 29.3092C27.3233 29.1962 27.27 29.0918 27.1907 29.0089ZM5.04322 22.0312V4.64004C5.04322 3.24826 6.03363 2.03994 7.39811 1.76689C9.37741 1.37164 11.3908 1.17233 13.4092 1.17188H16.5047C18.5481 1.17188 20.6005 1.3735 22.6054 1.77123C23.2702 1.90356 23.8688 2.26184 24.2995 2.78525C24.7292 3.30969 24.9644 3.96663 24.965 4.64467V22.0312C24.965 23.0005 24.1765 23.7891 23.2072 23.7891H6.80098C5.83178 23.7891 5.04322 23.0005 5.04322 22.0312ZM7.12488 28.8281H4.61795L8.36461 24.9609H9.92684L7.12488 28.8281ZM10.5248 26.1328L11.3739 24.9609H18.6866L19.5356 26.1328H10.5248ZM22.9356 28.8281L20.1337 24.9609H21.6938L25.3955 28.8281H22.9356Z">
                                    </path>
                                    <path
                                        d="M23.207 4.6875H6.80078C6.47723 4.6875 6.21484 4.94988 6.21484 5.27344V15C6.21484 15.3236 6.47723 15.5859 6.80078 15.5859H23.207C23.5306 15.5859 23.793 15.3236 23.793 15V5.27344C23.793 4.94988 23.5306 4.6875 23.207 4.6875ZM14.418 14.4141H7.38672V5.85938H14.418V14.4141ZM22.6211 14.4141H15.5898V5.85938H22.6211V14.4141ZM17.3477 2.34375H12.6602C12.3366 2.34375 12.0742 2.60613 12.0742 2.92969C12.0742 3.25324 12.3366 3.51562 12.6602 3.51562H17.3477C17.6712 3.51562 17.9336 3.25324 17.9336 2.92969C17.9336 2.60613 17.6712 2.34375 17.3477 2.34375ZM21.4492 17.3438C20.1569 17.3438 19.1055 18.3952 19.1055 19.6875C19.1055 20.9798 20.1569 22.0312 21.4492 22.0312C22.7416 22.0312 23.793 20.9798 23.793 19.6875C23.793 18.3952 22.7416 17.3438 21.4492 17.3438ZM21.4492 20.8594C20.803 20.8594 20.2773 20.3337 20.2773 19.6875C20.2773 19.0413 20.803 18.5156 21.4492 18.5156C22.0954 18.5156 22.6211 19.0413 22.6211 19.6875C22.6211 20.3337 22.0954 20.8594 21.4492 20.8594ZM8.55859 17.3438C7.26625 17.3438 6.21484 18.3952 6.21484 19.6875C6.21484 20.9798 7.26631 22.0312 8.55859 22.0312C9.85094 22.0312 10.9023 20.9798 10.9023 19.6875C10.9023 18.3952 9.85094 17.3438 8.55859 17.3438ZM8.55859 20.8594C7.91242 20.8594 7.38672 20.3337 7.38672 19.6875C7.38672 19.0413 7.91242 18.5156 8.55859 18.5156C9.20477 18.5156 9.73047 19.0413 9.73047 19.6875C9.73047 20.3337 9.20477 20.8594 8.55859 20.8594ZM17.3477 17.9297H12.6602C12.3366 17.9297 12.0742 18.1921 12.0742 18.5156C12.0742 18.8392 12.3366 19.1016 12.6602 19.1016H17.3477C17.6712 19.1016 17.9336 18.8392 17.9336 18.5156C17.9336 18.1921 17.6713 17.9297 17.3477 17.9297ZM17.3477 20.2734H12.6602C12.3366 20.2734 12.0742 20.5358 12.0742 20.8594C12.0742 21.1829 12.3366 21.4453 12.6602 21.4453H17.3477C17.6712 21.4453 17.9336 21.1829 17.9336 20.8594C17.9336 20.5358 17.6713 20.2734 17.3477 20.2734Z">
                                    </path>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.overview.transport')) ?></span><strong><?= esc($t('tour.overview.transportValue')) ?></strong></div>
                            </li>
                            <li><svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15.0013 14.4272C12.1553 14.4272 9.83984 12.1117 9.83984 9.26572C9.83984 6.41973 12.1553 4.10426 15.0013 4.10426C17.8476 4.10426 20.1628 6.41973 20.1628 9.26572C20.1628 12.1117 17.8473 14.4272 15.0013 14.4272ZM15.0013 5.34386C12.8386 5.34386 11.0794 7.1034 11.0794 9.26578C11.0794 11.4282 12.8389 13.1877 15.0013 13.1877C17.1637 13.1877 18.9232 11.4282 18.9232 9.26578C18.9232 7.1034 17.1637 5.34386 15.0013 5.34386Z">
                                    </path>
                                    <path
                                        d="M14.9995 25.8957C11.8192 25.8957 8.82259 24.667 6.56136 22.4362C6.50199 22.3776 6.45506 22.3076 6.42338 22.2305C6.3917 22.1533 6.37593 22.0706 6.377 21.9872C6.39342 20.6736 6.69743 19.4152 7.28065 18.2472C8.7517 15.3008 11.7092 13.4706 14.9996 13.4706C19.6974 13.4706 23.5672 17.2912 23.6258 21.9872C23.6269 22.0706 23.6111 22.1534 23.5793 22.2306C23.5476 22.3077 23.5006 22.3777 23.4411 22.4362C21.1802 24.6673 18.1823 25.8957 14.9995 25.8957ZM7.62427 21.7389C9.62799 23.6221 12.2363 24.6561 14.9995 24.6561C17.7649 24.6561 20.3748 23.6224 22.3785 21.7392C22.1904 17.8396 18.9341 14.7098 14.9995 14.7098C12.182 14.7098 9.64904 16.2772 8.38965 18.8006C7.92947 19.7222 7.67262 20.7092 7.62427 21.7389ZM24.4612 13.2562C22.5809 13.2562 21.0509 11.7262 21.0509 9.84586C21.0509 7.96547 22.5809 6.43591 24.4612 6.43591C26.3416 6.43591 27.8715 7.96583 27.8715 9.84621C27.8715 11.7266 26.3419 13.2562 24.4612 13.2562ZM24.4612 7.67544C23.2645 7.67544 22.2905 8.64938 22.2905 9.84615C22.2905 11.0429 23.2645 12.0169 24.4612 12.0169C25.658 12.0169 26.632 11.0429 26.632 9.84615C26.632 8.64938 25.6583 7.67544 24.4612 7.67544Z">
                                    </path>
                                    <path
                                        d="M24.4585 20.3029C23.7492 20.3029 23.0476 20.2054 22.3733 20.0126C22.0439 19.9186 21.8533 19.5756 21.9475 19.2465C22.0417 18.9171 22.3839 18.7259 22.7136 18.8207C23.2772 18.9815 23.8645 19.0631 24.4585 19.0631C26.0607 19.0631 27.5738 18.478 28.7486 17.4101C28.584 15.192 26.7123 13.4293 24.4585 13.4293C22.8192 13.4293 21.3458 14.3417 20.6132 15.8102C20.5754 15.8849 20.2541 16.6583 19.7003 16.3695C19.1022 16.0574 19.4545 15.3555 19.5047 15.2551C20.4481 13.3648 22.3464 12.1897 24.4585 12.1897C27.4753 12.1897 29.9612 14.6425 29.9996 17.6577C30.0006 17.741 29.9848 17.8237 29.9531 17.9008C29.9215 17.9779 29.8746 18.0478 29.8152 18.1064C28.3819 19.5229 26.4793 20.3029 24.4585 20.3029ZM5.53726 13.2562C3.65688 13.2562 2.12695 11.7262 2.12695 9.84586C2.12695 7.96547 3.65688 6.43591 5.53726 6.43591C7.41764 6.43591 8.94757 7.96583 8.94757 9.84621C8.94757 11.7266 7.41764 13.2562 5.53726 13.2562ZM5.53726 7.67544C4.34019 7.67544 3.36655 8.64938 3.36655 9.84615C3.36655 11.0429 4.34049 12.0169 5.53726 12.0169C6.73403 12.0169 7.70797 11.0429 7.70797 9.84615C7.70797 8.64938 6.73403 7.67544 5.53726 7.67544Z">
                                    </path>
                                    <path
                                        d="M5.53766 20.3029C3.51568 20.3029 1.61419 19.5226 0.183813 18.1062C0.124633 18.0477 0.0778577 17.9778 0.0462837 17.9008C0.0147097 17.8238 -0.00101469 17.7413 5.07368e-05 17.6581C0.0384684 14.6432 2.52283 12.1902 5.53766 12.1902C7.6517 12.1902 9.55159 13.3652 10.4958 15.2571C10.5444 15.3541 10.8184 16.139 10.3067 16.3637C9.8013 16.5856 9.42509 15.8871 9.38733 15.8115C8.65383 14.342 7.17909 13.4297 5.53796 13.4297C3.28601 13.4297 1.41556 15.1923 1.2513 17.4108C2.42357 18.4783 3.93518 19.0634 5.53796 19.0634C6.13634 19.0634 6.7248 18.9807 7.28697 18.8183C7.61578 18.7225 7.9594 18.9125 8.05455 19.2413C8.1497 19.5701 7.96035 19.9137 7.63154 20.0089C6.95727 20.2037 6.25261 20.3029 5.53766 20.3029Z">
                                    </path>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.overview.group')) ?></span><strong><?= esc($t('tour.overview.groupValue')) ?></strong></div>
                            </li>
                            <li><svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M24.3721 15.7246C24.3721 11.9517 21.313 8.89258 17.54 8.89258C13.7673 8.89282 10.709 11.9518 10.709 15.7246C10.7092 19.4972 13.7674 22.5554 17.54 22.5557C21.3129 22.5557 24.3718 19.4974 24.3721 15.7246ZM25.3721 15.7246C25.3718 20.0497 21.8651 23.5557 17.54 23.5557C13.2151 23.5554 9.70922 20.0495 9.70898 15.7246C9.70898 11.3995 13.215 7.89282 17.54 7.89258C21.8653 7.89258 25.3721 11.3994 25.3721 15.7246Z">
                                        </path>
                                        <path
                                            d="M21.1289 15.7246C21.1289 13.7689 20.6859 12.0225 19.9951 10.7832C19.2951 9.5275 18.4074 8.89259 17.542 8.89258C16.6767 8.89258 15.7889 9.52747 15.0889 10.7832C14.3981 12.0225 13.9551 13.7689 13.9551 15.7246C13.9551 17.6801 14.3981 19.4259 15.0889 20.665C15.7889 21.9208 16.6766 22.5557 17.542 22.5557C18.4074 22.5557 19.2951 21.9207 19.9951 20.665C20.6859 19.4259 21.1288 17.6801 21.1289 15.7246ZM22.1289 15.7246C22.1288 17.8176 21.6573 19.7373 20.8691 21.1514C20.09 22.549 18.9337 23.5557 17.542 23.5557C16.1503 23.5557 14.9949 22.549 14.2158 21.1514C13.4276 19.7372 12.9551 17.8178 12.9551 15.7246C12.9551 13.6312 13.4275 11.7111 14.2158 10.2969C14.9949 8.8992 16.1503 7.89258 17.542 7.89258C18.9338 7.89259 20.09 8.8992 20.8691 10.2969C21.6574 11.7111 22.1289 13.6314 22.1289 15.7246Z">
                                        </path>
                                        <path
                                            d="M29.0596 23.1318V8.31543C29.0595 6.95356 27.9556 5.84961 26.5938 5.84961H8.4873C7.12549 5.8497 6.02154 6.95369 6.02148 8.31543V23.1318C6.02148 24.4936 7.1254 25.5976 8.4873 25.5977H11.249C11.525 25.5977 11.7488 25.8218 11.749 26.0977C11.749 26.3738 11.5251 26.5976 11.249 26.5977H8.4873C6.57313 26.5976 5.02148 25.0459 5.02148 23.1318V8.31543C5.02154 6.40139 6.57322 4.8497 8.4873 4.84961H26.5938C28.5079 4.84961 30.0595 6.40128 30.0596 8.31543V23.1318C30.0596 25.046 28.5079 26.5977 26.5938 26.5977H24.0312C23.7631 26.5977 23.4969 26.6509 23.249 26.7539C23.0016 26.8569 22.7769 27.0081 22.5879 27.1982L20.2354 29.5654C19.1837 30.6233 17.3783 29.8781 17.3779 28.3867V27.2061C17.3779 26.8706 17.1059 26.5978 16.7705 26.5977H13.2998C13.0237 26.5977 12.7998 26.3738 12.7998 26.0977C12.8 25.8217 13.0238 25.5977 13.2998 25.5977H16.7705C17.6581 25.5978 18.3779 26.3183 18.3779 27.2061V28.3867C18.3783 28.9858 19.103 29.2847 19.5254 28.8604L21.8789 26.4932C22.1609 26.2096 22.4961 25.9846 22.8652 25.8311C23.2346 25.6775 23.6312 25.5977 24.0312 25.5977H26.5938C27.9556 25.5977 29.0596 24.4937 29.0596 23.1318ZM17.041 22.7764V19.9395H11.3203C11.0442 19.9395 10.8203 19.7156 10.8203 19.4395C10.8205 19.1635 11.0443 18.9395 11.3203 18.9395H17.041V16.2236H10.4316C10.1555 16.2236 9.93164 15.9998 9.93164 15.7236C9.93178 15.4476 10.1556 15.2236 10.4316 15.2236H17.041V12.5078H11.3203C11.0442 12.5078 10.8203 12.284 10.8203 12.0078C10.8204 11.7317 11.0442 11.5078 11.3203 11.5078H17.041V8.59668C17.041 8.32054 17.2649 8.09668 17.541 8.09668C17.817 8.0969 18.041 8.32067 18.041 8.59668V11.5078H23.7129C23.989 11.5078 24.2128 11.7317 24.2129 12.0078C24.2129 12.284 23.989 12.5078 23.7129 12.5078H18.041V15.2236H24.6309C24.9069 15.2237 25.1307 15.4476 25.1309 15.7236C25.1309 15.9998 24.907 16.2236 24.6309 16.2236H18.041V18.9395H23.7129C23.9889 18.9395 24.2127 19.1635 24.2129 19.4395C24.2129 19.7156 23.989 19.9395 23.7129 19.9395H18.041V22.7764C18.0409 23.0523 17.8169 23.2761 17.541 23.2764C17.2649 23.2764 17.0411 23.0524 17.041 22.7764Z">
                                        </path>
                                        <path
                                            d="M5.02246 8.31543C5.02257 6.40133 6.57418 4.84963 8.48828 4.84961H22.2539V3.19238C22.2537 2.02591 21.3675 1.06658 20.2314 0.951172L20.001 0.939453H12.6289C12.3529 0.939294 12.1289 0.715498 12.1289 0.439453C12.1289 0.163409 12.3529 -0.0603882 12.6289 -0.0605469H20.001L20.168 -0.0566406C21.887 0.0302574 23.2537 1.45181 23.2539 3.19238V5.34961C23.2539 5.62575 23.03 5.84961 22.7539 5.84961H8.48828C7.12644 5.84963 6.02257 6.95363 6.02246 8.31543V19.7002C6.02246 19.9763 5.79855 20.2001 5.52246 20.2002H3.19238C1.45188 20.2 0.0303517 18.8332 -0.0566406 17.1143L-0.0605469 16.9473V3.19238C-0.0603539 1.39594 1.39594 -0.0603539 3.19238 -0.0605469H10.5781C10.8543 -0.0605469 11.0781 0.163311 11.0781 0.439453C11.0781 0.715596 10.8543 0.939453 10.5781 0.939453H3.19238C1.94823 0.939646 0.939646 1.94823 0.939453 3.19238V16.9473L0.951172 17.1777C1.06667 18.3137 2.02597 19.2 3.19238 19.2002H5.02246V8.31543Z">
                                        </path>
                                    </g>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.overview.guide')) ?></span><strong><?= esc($t('tour.overview.guideValue')) ?></strong></div>
                            </li>
                            <li><svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.55227 10.4163C6.94457 10.4159 6.36185 10.1745 5.93203 9.74487C5.50221 9.31529 5.26043 8.7327 5.25977 8.12501C5.25977 6.86251 6.28852 5.83376 7.55227 5.83376C8.81477 5.83376 9.84352 6.86126 9.84352 8.12501C9.84352 9.38751 8.81602 10.4163 7.55227 10.4163ZM7.55227 6.97876C7.25752 6.99252 6.97941 7.1193 6.77569 7.33276C6.57197 7.54621 6.45831 7.82994 6.45831 8.12501C6.45831 8.42007 6.57197 8.7038 6.77569 8.91725C6.97941 9.13071 7.25752 9.25749 7.55227 9.27126C7.84701 9.25749 8.12512 9.13071 8.32884 8.91725C8.53256 8.7038 8.64622 8.42007 8.64622 8.12501C8.64622 7.82994 8.53256 7.54621 8.32884 7.33276C8.12512 7.1193 7.84701 6.99252 7.55227 6.97876ZM24.166 7.26501H12.7085C12.5567 7.26501 12.4111 7.20469 12.3037 7.09732C12.1963 6.98996 12.136 6.84434 12.136 6.69251C12.136 6.54067 12.1963 6.39505 12.3037 6.28769C12.4111 6.18032 12.5567 6.12001 12.7085 6.12001H24.166C24.3179 6.12001 24.4635 6.18032 24.5708 6.28769C24.6782 6.39505 24.7385 6.54067 24.7385 6.69251C24.7385 6.84434 24.6782 6.98996 24.5708 7.09732C24.4635 7.20469 24.3179 7.26501 24.166 7.26501ZM19.5835 10.13H12.7085C12.5567 10.13 12.4111 10.0697 12.3037 9.96232C12.1963 9.85496 12.136 9.70934 12.136 9.55751C12.136 9.40567 12.1963 9.26005 12.3037 9.15269C12.4111 9.04532 12.5567 8.98501 12.7085 8.98501H19.5835C19.7354 8.98501 19.881 9.04532 19.9883 9.15269C20.0957 9.26005 20.156 9.40567 20.156 9.55751C20.156 9.70934 20.0957 9.85496 19.9883 9.96232C19.881 10.0697 19.7354 10.13 19.5835 10.13ZM7.55227 17.2913C6.94457 17.2909 6.36185 17.0495 5.93203 16.6199C5.50221 16.1903 5.26043 15.6077 5.25977 15C5.25977 13.7375 6.28852 12.7088 7.55227 12.7088C8.81477 12.7088 9.84352 13.7363 9.84352 15C9.84352 16.2625 8.81602 17.2913 7.55227 17.2913ZM7.55227 13.8538C7.25752 13.8675 6.97941 13.9943 6.77569 14.2078C6.57197 14.4212 6.45831 14.7049 6.45831 15C6.45831 15.2951 6.57197 15.5788 6.77569 15.7923C6.97941 16.0057 7.25752 16.1325 7.55227 16.1463C7.84701 16.1325 8.12512 16.0057 8.32884 15.7923C8.53256 15.5788 8.64622 15.2951 8.64622 15C8.64622 14.7049 8.53256 14.4212 8.32884 14.2078C8.12512 13.9943 7.84701 13.8675 7.55227 13.8538ZM24.166 14.1413H12.7085C12.5565 14.1413 12.4107 14.0809 12.3033 13.9734C12.1958 13.8659 12.1354 13.7201 12.1354 13.5681C12.1354 13.4161 12.1958 13.2704 12.3033 13.1629C12.4107 13.0554 12.5565 12.995 12.7085 12.995H24.166C24.3179 12.995 24.4635 13.0553 24.5708 13.1627C24.6782 13.2701 24.7385 13.4157 24.7385 13.5675C24.7385 13.7193 24.6782 13.865 24.5708 13.9723C24.4635 14.0797 24.3179 14.1413 24.166 14.1413ZM19.5835 17.005H12.7085C12.5567 17.005 12.4111 16.9447 12.3037 16.8373C12.1963 16.73 12.136 16.5843 12.136 16.4325C12.136 16.2807 12.1963 16.1351 12.3037 16.0277C12.4111 15.9203 12.5567 15.86 12.7085 15.86H19.5835C19.7354 15.86 19.881 15.9203 19.9883 16.0277C20.0957 16.1351 20.156 16.2807 20.156 16.4325C20.156 16.5843 20.0957 16.73 19.9883 16.8373C19.881 16.9447 19.7354 17.005 19.5835 17.005ZM7.55227 24.1663C6.94457 24.1659 6.36185 23.9245 5.93203 23.4949C5.50221 23.0653 5.26043 22.4827 5.25977 21.875C5.25977 20.6125 6.28852 19.5838 7.55227 19.5838C8.81477 19.5838 9.84352 20.6113 9.84352 21.875C9.84352 23.1375 8.81602 24.1663 7.55227 24.1663ZM7.55227 20.7288C7.25752 20.7425 6.97941 20.8693 6.77569 21.0828C6.57197 21.2962 6.45831 21.5799 6.45831 21.875C6.45831 22.1701 6.57197 22.4538 6.77569 22.6673C6.97941 22.8807 7.25752 23.0075 7.55227 23.0213C7.84701 23.0075 8.12512 22.8807 8.32884 22.6673C8.53256 22.4538 8.64622 22.1701 8.64622 21.875C8.64622 21.5799 8.53256 21.2962 8.32884 21.0828C8.12512 20.8693 7.84701 20.7425 7.55227 20.7288ZM24.166 21.0163H12.7085C12.5565 21.0163 12.4107 20.9559 12.3033 20.8484C12.1958 20.7409 12.1354 20.5951 12.1354 20.4431C12.1354 20.2911 12.1958 20.1454 12.3033 20.0379C12.4107 19.9304 12.5565 19.87 12.7085 19.87H24.166C24.3179 19.87 24.4635 19.9303 24.5708 20.0377C24.6782 20.1451 24.7385 20.2907 24.7385 20.4425C24.7385 20.5943 24.6782 20.74 24.5708 20.8473C24.4635 20.9547 24.3179 21.0163 24.166 21.0163ZM19.5835 23.88H12.7085C12.5567 23.88 12.4111 23.8197 12.3037 23.7123C12.1963 23.605 12.136 23.4593 12.136 23.3075C12.136 23.1557 12.1963 23.0101 12.3037 22.9027C12.4111 22.7953 12.5567 22.735 12.7085 22.735H19.5835C19.7354 22.735 19.881 22.7953 19.9883 22.9027C20.0957 23.0101 20.156 23.1557 20.156 23.3075C20.156 23.4593 20.0957 23.605 19.9883 23.7123C19.881 23.8197 19.7354 23.88 19.5835 23.88Z">
                                    </path>
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M21.4162 2.39625H8.58375C7.28125 2.39625 6.3725 2.39625 5.66625 2.455C4.9725 2.51125 4.575 2.6175 4.2725 2.77C3.62556 3.09959 3.09959 3.62556 2.77 4.2725C2.61625 4.575 2.51125 4.9725 2.455 5.66625C2.39625 6.3725 2.39625 7.28125 2.39625 8.58375V21.4162C2.39625 22.7187 2.39625 23.6262 2.455 24.3337C2.51125 25.0275 2.6175 25.425 2.77 25.7275C3.09959 26.3744 3.62556 26.9004 4.2725 27.23C4.575 27.3837 4.9725 27.4887 5.66625 27.545C6.3725 27.6037 7.28125 27.6037 8.58375 27.6037H21.4162C22.7187 27.6037 23.6262 27.6037 24.3337 27.545C25.0275 27.4887 25.425 27.3825 25.7275 27.23C26.3744 26.9004 26.9004 26.3744 27.23 25.7275C27.3837 25.425 27.4887 25.0275 27.545 24.3337C27.6037 23.6262 27.6037 22.7187 27.6037 21.4162V8.58375C27.6037 7.28125 27.6037 6.3725 27.545 5.66625C27.4887 4.9725 27.3825 4.575 27.23 4.2725C26.9004 3.62556 26.3744 3.09959 25.7275 2.77C25.425 2.61625 25.0275 2.51125 24.3337 2.455C23.6262 2.39625 22.7187 2.39625 21.4162 2.39625ZM1.75 3.7525C1.25 4.7325 1.25 6.01625 1.25 8.58375V21.4162C1.25 23.9837 1.25 25.2662 1.75 26.2475C2.18875 27.11 2.89 27.81 3.7525 28.25C4.7325 28.75 6.01625 28.75 8.58375 28.75H21.4162C23.9837 28.75 25.2662 28.75 26.2475 28.25C27.1096 27.8105 27.8105 27.1096 28.25 26.2475C28.75 25.2675 28.75 23.9837 28.75 21.4162V8.58375C28.75 6.01625 28.75 4.73375 28.25 3.7525C27.8106 2.89036 27.1096 2.1894 26.2475 1.75C25.2675 1.25 23.9837 1.25 21.4162 1.25H8.58375C6.01625 1.25 4.73375 1.25 3.7525 1.75C2.89 2.18875 2.19 2.89 1.75 3.7525Z">
                                    </path>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.overview.type')) ?></span><strong><?= esc($t('tour.overview.typeValue')) ?></strong></div>
                            </li>
                        </ul>
                    </div>
                    <?php if ($gallery !== []): ?>
                    <div class="location-slider-wrap mb-60">
                        <h4><?= esc($t('tour.gallery.title')) ?></h4>
                        <div class="location-slider-area">
                            <div class="swiper package-dt-location-slider">
                                <div class="swiper-wrapper">
                                    <?php foreach ($gallery as $item): ?>
                                        <div class="swiper-slide">
                                            <div class="location-card">
                                                <div class="location-img">
                                                    <img src="<?= esc($item['url']) ?>" alt="<?= esc($item['alt_text'] ?: $tour['title']) ?>" loading="lazy" decoding="async" width="640" height="420">
                                                </div>
                                                <?php if (! empty($item['alt_text'])): ?>
                                                    <div class="location-content">
                                                        <h6><?= esc($item['alt_text']) ?></h6>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="slider-btn-grp two">
                                <div class="slider-btn location-slider-prev"><svg width="12" height="14"
                                        viewBox="0 0 12 14" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.3125 0.704152C10.4758 0.323658 10.9172 0.147472 11.2979 0.310597C11.6784 0.473872 11.8545 0.915329 11.6914 1.29595C10.8482 3.26297 9.18494 4.61712 7.42871 5.59282C6.36908 6.1815 5.24241 6.64833 4.18848 7.03618C5.31592 7.51881 6.52685 8.12012 7.6416 8.79693C8.54322 9.34436 9.39912 9.95095 10.1025 10.5958C10.7986 11.2338 11.3891 11.9489 11.6982 12.7217C11.852 13.1063 11.6648 13.5425 11.2803 13.6963C10.8957 13.85 10.4595 13.6629 10.3057 13.2784C10.1148 12.8013 9.70522 12.2662 9.08887 11.7012C8.47993 11.1431 7.71047 10.5931 6.8623 10.0782C5.16463 9.04752 3.21635 8.19586 1.76465 7.71196L-0.370117 7.00005L1.76465 6.28814C3.27361 5.78515 5.08312 5.18062 6.7002 4.28228C8.31881 3.38305 9.6556 2.23687 10.3125 0.704152Z">
                                        </path>
                                    </svg></div>
                                <div class="slider-btn location-slider-next"><svg width="12" height="14"
                                        viewBox="0 0 12 14" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M0.719771 13.6962C1.10432 13.85 1.54057 13.6628 1.69438 13.2783C1.88527 12.8012 2.29482 12.2661 2.91118 11.7011C3.52012 11.1429 4.28957 10.593 5.13774 10.0781C6.83541 9.04741 8.78369 8.19576 10.2354 7.71186L12.3702 6.99995L10.2354 6.28803C8.72643 5.78505 6.91691 5.18052 5.29985 4.28218C3.68124 3.38295 2.34442 2.23677 1.68754 0.70405C1.52426 0.323573 1.0828 0.147379 0.702193 0.310495C0.321714 0.473783 0.145522 0.915242 0.308638 1.29585C1.15178 3.26288 2.81511 4.61702 4.57133 5.59272C5.63078 6.1813 6.75681 6.64924 7.81059 7.03706C6.68348 7.5196 5.4728 8.12025 4.35844 8.79682C3.45684 9.34426 2.60092 9.95086 1.89751 10.5957C1.20147 11.2337 0.610934 11.9488 0.301802 12.7216C0.148089 13.1062 0.33524 13.5424 0.719771 13.6962Z">
                                        </path>
                                    </svg></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php if (! empty($tour['itinerary_days'])): ?>

                    <div class="tour-itinerary-area mb-60">
                        <div class="itinerary-title">
                            <h4><?= esc($t('tour.itinerary.title')) ?></h4><a href="#" class="expand-btn"><?= esc($t('tour.itinerary.expand')) ?></a>
                        </div>
                        <ul class="itinerary-list">
                            <li class="single-itinerary">
                                <div class="tour-plan-wrap">
                                    <div class="accordion accordion-flush" id="accordionTourPlan">
                                        <?php foreach ($tour['itinerary_days'] as $index => $day): ?>
                                            <?php $collapseId = 'flush-collapseTourPlan-' . ($index + 1); ?>
                                            <div class="accordion-item">
                                                <div class="accordion-header" id="flush-headingTourPlan-<?= $index + 1 ?>">
                                                    <div class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" role="button" data-bs-toggle="collapse"
                                                    data-bs-target="#<?= esc($collapseId) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                                                    aria-controls="<?= esc($collapseId) ?>">
                                                        <h6><span>
                                                            <svg width="14" height="14" viewBox="0 0 14 14"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path
                                                                    d="M7 14C7 14 12.25 9.02475 12.25 5.25C12.25 3.85761 11.6969 2.52226 10.7123 1.53769C9.72774 0.553123 8.39239 0 7 0C5.60761 0 4.27226 0.553123 3.28769 1.53769C2.30312 2.52226 1.75 3.85761 1.75 5.25C1.75 9.02475 7 14 7 14ZM7 7.875C6.30381 7.875 5.63613 7.59844 5.14384 7.10616C4.65156 6.61387 4.375 5.94619 4.375 5.25C4.375 4.55381 4.65156 3.88613 5.14384 3.39384C5.63613 2.90156 6.30381 2.625 7 2.625C7.69619 2.625 8.36387 2.90156 8.85616 3.39384C9.34844 3.88613 9.625 4.55381 9.625 5.25C9.625 5.94619 9.34844 6.61387 8.85616 7.10616C8.36387 7.59844 7.69619 7.875 7 7.875Z">
                                                                </path>
                                                            </svg>
                                                            <?= esc(lang('Frontend.tour.itinerary.day', [(string) ($day['day_number'] ?? '')], $locale)) ?></span><?= esc($day['title'] ?? '') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div id="<?= esc($collapseId) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="flush-headingTourPlan-<?= $index + 1 ?>">
                                                    <div class="accordion-body">
                                                        <?= tour_detail_html($day['description'] ?? '') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <!-- <div class="map-area mb-60">
                        <h4><?= esc($t('tour.highlights.title')) ?></h4>
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3257.313598043175!2d2.291906376866813!3d48.858373600707175!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e2964e34e2d%3A0x8ddca9ee380ef7e0!2sTh%C3%A1p%20Eiffel!5e1!3m2!1svi!2s!4v1775463086452!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div> -->
                    <div class="feature-list-area mb-60">
                        <h4><?= esc($t('tour.details.title')) ?></h4>
                        <div class="row gy-md-5 gy-4 justify-content-between">
                            <div class="col-lg-5 col-md-6">
                                <div class="single-feature-list">
                                    <h5><?= esc($t('tour.details.includes')) ?></h5>
                                    <ul class="items-list two">
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem1')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem2')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem3')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem4')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem5')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem6')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem7')) ?></li>
                                        <li><svg width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                                </path>
                                                <path
                                                    d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                                </path>
                                            </svg><?= esc($t('tour.details.includeItem8')) ?></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-6">
                                <div class="single-feature-list">
                                    <h5><?= esc($t('tour.details.excludes')) ?></h5>
                                    <ul class="items-list two">
                                        <li><svg class="exclude" width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                    <path
                                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15C11.866 15 15 11.866 15 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z">
                                                    </path>
                                                    <path
                                                        d="M6.00165 5.00036C5.8601 5.00368 5.72612 5.05514 5.62413 5.15703L5.1296 5.65267C4.89714 5.88495 4.92646 6.28828 5.19443 6.55662L6.67129 8.03561L5.19443 9.51394C4.92646 9.78219 4.89704 10.1856 5.1296 10.4184L5.62413 10.9136C5.8566 11.1458 6.2592 11.117 6.52753 10.8486L8.0044 9.36982L9.48126 10.8486C9.74978 11.117 10.1527 11.1458 10.3847 10.9136L10.8799 10.4184C11.1119 10.1857 11.0831 9.78228 10.8145 9.51394L9.33769 8.03561L10.8145 6.55662C11.0831 6.28828 11.1119 5.88495 10.8799 5.65267L10.3847 5.15703C10.1527 4.92429 9.74978 4.9537 9.48126 5.22241L8.0044 6.70084L6.52753 5.2225C6.37677 5.07109 6.18321 4.99594 6.00165 5.00036Z">
                                                    </path>
                                                </g>
                                            </svg><?= esc($t('tour.details.excludeItem1')) ?></li>
                                        <li><svg class="exclude" width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                    <path
                                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15C11.866 15 15 11.866 15 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z">
                                                    </path>
                                                    <path
                                                        d="M6.00165 5.00036C5.8601 5.00368 5.72612 5.05514 5.62413 5.15703L5.1296 5.65267C4.89714 5.88495 4.92646 6.28828 5.19443 6.55662L6.67129 8.03561L5.19443 9.51394C4.92646 9.78219 4.89704 10.1856 5.1296 10.4184L5.62413 10.9136C5.8566 11.1458 6.2592 11.117 6.52753 10.8486L8.0044 9.36982L9.48126 10.8486C9.74978 11.117 10.1527 11.1458 10.3847 10.9136L10.8799 10.4184C11.1119 10.1857 11.0831 9.78228 10.8145 9.51394L9.33769 8.03561L10.8145 6.55662C11.0831 6.28828 11.1119 5.88495 10.8799 5.65267L10.3847 5.15703C10.1527 4.92429 9.74978 4.9537 9.48126 5.22241L8.0044 6.70084L6.52753 5.2225C6.37677 5.07109 6.18321 4.99594 6.00165 5.00036Z">
                                                    </path>
                                                </g>
                                            </svg><?= esc($t('tour.details.excludeItem2')) ?></li>
                                        <li><svg class="exclude" width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                    <path
                                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15C11.866 15 15 11.866 15 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z">
                                                    </path>
                                                    <path
                                                        d="M6.00165 5.00036C5.8601 5.00368 5.72612 5.05514 5.62413 5.15703L5.1296 5.65267C4.89714 5.88495 4.92646 6.28828 5.19443 6.55662L6.67129 8.03561L5.19443 9.51394C4.92646 9.78219 4.89704 10.1856 5.1296 10.4184L5.62413 10.9136C5.8566 11.1458 6.2592 11.117 6.52753 10.8486L8.0044 9.36982L9.48126 10.8486C9.74978 11.117 10.1527 11.1458 10.3847 10.9136L10.8799 10.4184C11.1119 10.1857 11.0831 9.78228 10.8145 9.51394L9.33769 8.03561L10.8145 6.55662C11.0831 6.28828 11.1119 5.88495 10.8799 5.65267L10.3847 5.15703C10.1527 4.92429 9.74978 4.9537 9.48126 5.22241L8.0044 6.70084L6.52753 5.2225C6.37677 5.07109 6.18321 4.99594 6.00165 5.00036Z">
                                                    </path>
                                                </g>
                                            </svg><?= esc($t('tour.details.excludeItem3')) ?></li>
                                        <li><svg class="exclude" width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                    <path
                                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15C11.866 15 15 11.866 15 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z">
                                                    </path>
                                                    <path
                                                        d="M6.00165 5.00036C5.8601 5.00368 5.72612 5.05514 5.62413 5.15703L5.1296 5.65267C4.89714 5.88495 4.92646 6.28828 5.19443 6.55662L6.67129 8.03561L5.19443 9.51394C4.92646 9.78219 4.89704 10.1856 5.1296 10.4184L5.62413 10.9136C5.8566 11.1458 6.2592 11.117 6.52753 10.8486L8.0044 9.36982L9.48126 10.8486C9.74978 11.117 10.1527 11.1458 10.3847 10.9136L10.8799 10.4184C11.1119 10.1857 11.0831 9.78228 10.8145 9.51394L9.33769 8.03561L10.8145 6.55662C11.0831 6.28828 11.1119 5.88495 10.8799 5.65267L10.3847 5.15703C10.1527 4.92429 9.74978 4.9537 9.48126 5.22241L8.0044 6.70084L6.52753 5.2225C6.37677 5.07109 6.18321 4.99594 6.00165 5.00036Z">
                                                    </path>
                                                </g>
                                            </svg><?= esc($t('tour.details.excludeItem4')) ?></li>
                                        <li><svg class="exclude" width="16" height="16" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g>
                                                    <path
                                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15C11.866 15 15 11.866 15 8ZM16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z">
                                                    </path>
                                                    <path
                                                        d="M6.00165 5.00036C5.8601 5.00368 5.72612 5.05514 5.62413 5.15703L5.1296 5.65267C4.89714 5.88495 4.92646 6.28828 5.19443 6.55662L6.67129 8.03561L5.19443 9.51394C4.92646 9.78219 4.89704 10.1856 5.1296 10.4184L5.62413 10.9136C5.8566 11.1458 6.2592 11.117 6.52753 10.8486L8.0044 9.36982L9.48126 10.8486C9.74978 11.117 10.1527 11.1458 10.3847 10.9136L10.8799 10.4184C11.1119 10.1857 11.0831 9.78228 10.8145 9.51394L9.33769 8.03561L10.8145 6.55662C11.0831 6.28828 11.1119 5.88495 10.8799 5.65267L10.3847 5.15703C10.1527 4.92429 9.74978 4.9537 9.48126 5.22241L8.0044 6.70084L6.52753 5.2225C6.37677 5.07109 6.18321 4.99594 6.00165 5.00036Z">
                                                    </path>
                                                </g>
                                            </svg><?= esc($t('tour.details.excludeItem5')) ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <a href="../assets/company-desk.pdf/" download="company-desk.pdf"
                        class="download-area mb-60"><img alt="" loading="lazy" width="1111" height="220"
                            decoding="async" data-nimg="1" style="color:transparent"
                            src="assets/images/downloadtour.webp">
                        </a> -->
                    <div class="additional-info mb-60">
                        <h4><?= esc($t('tour.details.notes')) ?></h4>
                        <ul class="items-list two">
                            <li><svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                    </path>
                                    <path
                                        d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                    </path>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.details.noteLabel1')) ?></span> <?= esc($t('tour.details.noteText1')) ?></div>
                            </li>
                            <li><svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                    </path>
                                    <path
                                        d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                    </path>
                                </svg>
                                <div class="content"><span><?= esc($t('tour.details.noteLabel2')) ?></span> <?= esc($t('tour.details.noteText2')) ?></div>
                            </li>
                            <li><svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                    </path>
                                    <path
                                        d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                    </path>
                                </svg>
                                <div class="content"><?= esc($t('tour.details.noteText3')) ?></div>
                            </li>
                            <li><svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 8C15 4.13401 11.866 1 8 1C4.13401 1 1 4.13401 1 8C1 11.866 4.13401 15 8 15V16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8C16 12.4183 12.4183 16 8 16V15C11.866 15 15 11.866 15 8Z">
                                    </path>
                                    <path
                                        d="M11.6947 6.45795L7.24644 10.9086C7.17556 10.9771 7.08572 11.0126 6.99596 11.0126C6.9494 11.0127 6.90328 11.0035 6.86027 10.9857C6.81727 10.9678 6.77822 10.9416 6.7454 10.9086L4.3038 8.46699C4.16436 8.32987 4.16436 8.10539 4.3038 7.96595L5.16652 7.10083C5.29892 6.96851 5.53524 6.96851 5.66764 7.10083L6.99596 8.42915L10.3309 5.09179C10.3638 5.05887 10.4028 5.03274 10.4457 5.01489C10.4887 4.99705 10.5347 4.98784 10.5812 4.98779C10.6757 4.98779 10.7656 5.02563 10.8317 5.09179L11.6944 5.95699C11.8341 6.09643 11.8341 6.32091 11.6947 6.45795Z">
                                    </path>
                                </svg>
                                <div class="content"><?= esc($t('tour.details.noteText4')) ?></div>
                            </li>
                        </ul>
                    </div>
                    <?php if (! empty($tour['faqs'])): ?>
                    <div class="faq-area mb-60">
                        <h4><?= esc($t('tour.faq.title')) ?></h4>
                        <div class="faq-wrap">
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                <?php foreach ($tour['faqs'] as $index => $faq): ?>
                                    <?php $faqId = 'tour-faq-' . ($index + 1); ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="<?= esc($faqId) ?>-heading">
                                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($faqId) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= esc($faqId) ?>">
                                                <?= esc($faq['question'] ?? '') ?>
                                            </button>
                                        </h2>
                                        <div id="<?= esc($faqId) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="<?= esc($faqId) ?>-heading" data-bs-parent="#tourFaq">
                                            <div class="accordion-body"><?= tour_detail_html($faq['answer'] ?? '') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="customer-rating-area">
                        <h4><?= esc($t('tour.reviews.title')) ?></h4>
                        <div class="rating-wrapper">
                            <div class="rating-area">
                                <span><?= esc($reviewLabel) ?></span>
                                <ul><?= render_review_stars((float) $reviewSummary['overall']) ?></ul>
                                <p><?= lang('Frontend.tour.review.summary', [esc(number_format((float) $reviewAverage, 1)), esc((string) $reviewSummary['count'])], $locale) ?></p>
                                <button class="primary-btn1 two" data-bs-toggle="modal" data-bs-target="#ratingModal"><span><?= esc($t('tour.review.write')) ?></span><span><?= esc($t('tour.review.write')) ?></span></button>
                            </div>
                            <ul class="progress-list">
                                <?php foreach ($reviewMetrics as $metricKey => $metricLabel): ?>
                                    <?php $metricValue = (float) ($reviewSummary[$metricKey] ?? 0); ?>
                                    <li class="single-progress"><span><?= esc($metricLabel) ?></span>
                                        <div class="rating-progress-bar-wrap">
                                            <div class="rating-progress-bar">
                                                <div class="rating-progress-bar-per" style="width: <?= esc((string) ($metricValue * 20)) ?>%;" data-per="<?= esc((string) ($metricValue * 20)) ?>"></div>
                                            </div><span class="data-per"><?= esc(number_format($metricValue, 1)) ?>/5</span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="comment-area">
                            <?php if ($reviews !== []): ?>
                                <?php foreach ($reviewPages as $pageIndex => $reviewPage): ?>
                                    <ul class="comment review-page<?= $pageIndex === 0 ? '' : ' d-none' ?>" data-review-page="<?= esc((string) ($pageIndex + 1)) ?>">
                                        <?php foreach ($reviewPage as $review): ?>
                                            <li>
                                                <div class="single-comment-area">
                                                    <div class="author-img"><img alt="<?= esc(lang('Frontend.tour.review.avatarAlt', [$review['reviewer_name']], $locale)) ?>" loading="lazy" width="550" height="220" decoding="async" data-nimg="1" style="color:transparent" src="https://ui-avatars.com/api/?name=<?= esc(urlencode($review['reviewer_name'])) ?>"></div>
                                                    <div class="comment-content">
                                                        <div class="author-name-deg">
                                                            <h6><?= esc($review['reviewer_name']) ?>,</h6><span><?= esc($review['created_label']) ?></span>
                                                        </div>
                                                        <p><?= esc($review['content']) ?></p>
                                                        <ul class="review-item-list">
                                                            <li><span><?= esc($t('tour.reviewMetric.overall')) ?></span><ul class="star-list"><?= render_review_stars((float) $review['rating_overall']) ?></ul></li>
                                                            <li><span><?= esc($t('tour.reviewMetric.destination')) ?></span><ul class="star-list"><?= render_review_stars((float) $review['rating_destination']) ?></ul></li>
                                                            <li><span><?= esc($t('tour.reviewMetric.transport')) ?></span><ul class="star-list"><?= render_review_stars((float) $review['rating_transport']) ?></ul></li>
                                                            <li><span><?= esc($t('tour.reviewMetric.value')) ?></span><ul class="star-list"><?= render_review_stars((float) $review['rating_value']) ?></ul></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endforeach; ?>
                                <?php if (count($reviewPages) > 1): ?>
                                    <div class="pagination-area review-pagination-area" data-review-pagination>
                                        <ul class="paginations">
                                            <?php foreach ($reviewPages as $pageIndex => $reviewPage): ?>
                                                <li class="page-item<?= $pageIndex === 0 ? ' active' : '' ?>">
                                                    <a href="#" data-review-page-trigger="<?= esc((string) ($pageIndex + 1)) ?>"><?= esc((string) ($pageIndex + 1)) ?></a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p><?= esc($t('tour.review.empty')) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="package-details-sidebar">
                    <div class="pricing-and-booking-area mb-40">
                        <div class="price-area">
                            <h6><?= esc($t('tour.sidebar.price')) ?></h6>
                            <span><?= esc(number_format($adultPrice, 0, ',', '.') . 'đ') ?><sub><?= esc($t('tour.booking.perPerson')) ?></sub>
                            </span> 
                        <!-- <br><del>125,900,000 đ</del><sub>/người</sub> -->
                        </div>
                        <ul>
                            <li><svg width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="13" height="13" rx="6.5"></rect>
                                    <path
                                        d="M11.0419 5.31317L6.17665 10.1811C6.09912 10.256 6.00086 10.2948 5.90268 10.2948C5.85176 10.2949 5.80132 10.2849 5.75428 10.2654C5.70724 10.2458 5.66454 10.2172 5.62863 10.1811L2.95813 7.51056C2.80562 7.36059 2.80562 7.11506 2.95813 6.96255L3.90173 6.01632C4.04655 5.8716 4.30502 5.8716 4.44983 6.01632L5.90268 7.46917L9.5503 3.81894C9.58623 3.78292 9.6289 3.75434 9.67587 3.73483C9.72285 3.71531 9.77321 3.70524 9.82408 3.70519C9.92742 3.70519 10.0257 3.74657 10.098 3.81894L11.0416 4.76525C11.1944 4.91776 11.1944 5.16329 11.0419 5.31317Z">
                                    </path>
                                </svg><?= esc($t('tour.sidebar.refund')) ?></li>
                            <li><svg width="14" height="14" viewBox="0 0 14 14" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="0.5" y="0.5" width="13" height="13" rx="6.5"></rect>
                                    <path
                                        d="M11.0419 5.31317L6.17665 10.1811C6.09912 10.256 6.00086 10.2948 5.90268 10.2948C5.85176 10.2949 5.80132 10.2849 5.75428 10.2654C5.70724 10.2458 5.66454 10.2172 5.62863 10.1811L2.95813 7.51056C2.80562 7.36059 2.80562 7.11506 2.95813 6.96255L3.90173 6.01632C4.04655 5.8716 4.30502 5.8716 4.44983 6.01632L5.90268 7.46917L9.5503 3.81894C9.58623 3.78292 9.6289 3.75434 9.67587 3.73483C9.72285 3.71531 9.77321 3.70524 9.82408 3.70519C9.92742 3.70519 10.0257 3.74657 10.098 3.81894L11.0416 4.76525C11.1944 4.91776 11.1944 5.16329 11.0419 5.31317Z">
                                    </path>
                                </svg><?= esc($t('tour.sidebar.support')) ?></li>
                        </ul><button class="primary-btn1 mb-10" <?= $hasBookableDepartures ? 'data-bs-toggle="modal" data-bs-target="#bookingModal"' : 'disabled' ?>><span><?= esc($hasBookableDepartures ? $t('tour.sidebar.checkAvailability') : $t('tour.booking.noDeparturesShort')) ?><svg width="10" height="10"
                                    viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span><span><?= esc($hasBookableDepartures ? $t('tour.sidebar.checkAvailability') : $t('tour.booking.noDeparturesShort')) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span></button><button class="primary-btn1 transparent" data-bs-toggle="modal"
                            data-bs-target="#enquiryModal"><span><?= esc($t('tour.sidebar.consult')) ?><svg width="10" height="10"
                                    viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span><span><?= esc($t('tour.sidebar.consult')) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span></button><span><svg width="14" height="14" viewBox="0 0 14 14"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M7 0C3.13423 0 0 3.13423 0 7C0 10.8662 3.13423 14 7 14C10.8662 14 14 10.8666 14 7C14 3.13423 10.8662 0 7 0ZM7 12.6875C3.85877 12.6875 1.31252 10.1412 1.31252 7C1.31252 3.85877 3.85877 1.31252 7 1.31252C10.1412 1.31252 12.6875 3.85877 12.6875 7C12.6875 10.1412 10.1412 12.6875 7 12.6875ZM7.00044 3.06992C6.49908 3.06992 6.11973 3.33157 6.11973 3.75418V7.63042C6.11973 8.05347 6.49903 8.31423 7.00044 8.31423C7.48956 8.31423 7.88115 8.04256 7.88115 7.63042V3.75418C7.8811 3.3416 7.48956 3.06992 7.00044 3.06992ZM7.00044 9.1875C6.51875 9.1875 6.12673 9.57952 6.12673 10.0616C6.12673 10.5428 6.51875 10.9349 7.00044 10.9349C7.48212 10.9349 7.87371 10.5428 7.87371 10.0616C7.87366 9.57948 7.48212 9.1875 7.00044 9.1875Z">
                                </path>
                            </svg><?= esc($t('tour.sidebar.limitedOffer')) ?></span>
                    </div>
                    <div class="customize-package-banner-wrap">
                        <h2><span><?= esc($t('tour.sidebar.customHeading1')) ?></span> <?= esc($t('tour.sidebar.customHeading2')) ?></h2>
                        <ul>
                            <li><svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="9" cy="9" r="8.5"></circle>
                                    <path
                                        d="M13.6193 7.0722L8.05903 12.6355C7.97043 12.7211 7.85813 12.7655 7.74593 12.7655C7.68772 12.7656 7.63008 12.7541 7.57632 12.7318C7.52256 12.7095 7.47376 12.6768 7.43272 12.6355L4.38073 9.5835C4.20642 9.4121 4.20642 9.1315 4.38073 8.9572L5.45912 7.8758C5.62462 7.7104 5.92002 7.7104 6.08552 7.8758L7.74593 9.5362L11.9146 5.3645C11.9557 5.32334 12.0045 5.29068 12.0581 5.26837C12.1118 5.24606 12.1694 5.23455 12.2275 5.2345C12.3456 5.2345 12.4579 5.2818 12.5406 5.3645L13.619 6.446C13.7936 6.6203 13.7936 6.9009 13.6193 7.0722Z">
                                    </path>
                                </svg><?= esc($t('tour.sidebar.customItem1')) ?></li>
                            <li><svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="9" cy="9" r="8.5"></circle>
                                    <path
                                        d="M13.6193 7.0722L8.05903 12.6355C7.97043 12.7211 7.85813 12.7655 7.74593 12.7655C7.68772 12.7656 7.63008 12.7541 7.57632 12.7318C7.52256 12.7095 7.47376 12.6768 7.43272 12.6355L4.38073 9.5835C4.20642 9.4121 4.20642 9.1315 4.38073 8.9572L5.45912 7.8758C5.62462 7.7104 5.92002 7.7104 6.08552 7.8758L7.74593 9.5362L11.9146 5.3645C11.9557 5.32334 12.0045 5.29068 12.0581 5.26837C12.1118 5.24606 12.1694 5.23455 12.2275 5.2345C12.3456 5.2345 12.4579 5.2818 12.5406 5.3645L13.619 6.446C13.7936 6.6203 13.7936 6.9009 13.6193 7.0722Z">
                                    </path>
                                </svg><?= esc($t('tour.sidebar.customItem2')) ?></li>
                        </ul>
                        <a class="primary-btn1 two black-bg" href="<?= esc(\App\Data\LocalizedPathCatalog::url('contact', $locale)) ?>"><span><?= esc($t('tour.sidebar.customCta')) ?><svg
                                    width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span><span><?= esc($t('tour.sidebar.customCta')) ?><svg width="10" height="10" viewBox="0 0 10 10"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z">
                                    </path>
                                </svg></span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if (! empty($relatedTours)): ?>
<div class="relevant-package-section pt-100 mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50">
            <div class="col-xl-6 col-lg-8">
                <div class="section-title text-center">
                    <h2><?= esc($t('tour.related.title')) ?></h2>
                    <p><?= esc($t('tour.related.desc')) ?></p>
                </div>
            </div>
        </div>
        <div class="row mb-40">
            <div class="col-lg-12">
                <div class="swiper home1-trip-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($relatedTours as $relatedTour): ?>
                            <div class="swiper-slide">
                                <?= view('components/tour-card', ['tour' => $relatedTour]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 d-flex justify-content-center">
                <div class="swiper-pagination2 paginations"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.TOUR_DETAIL_I18N = <?= json_encode([
    'bookingProceedFailed' => lang('Frontend.checkout.paypalCreateFailed', [], $locale),
    'bookingProceedFailedNow' => lang('Frontend.checkout.paypalConnectFailed', [], $locale),
    'loginFailed' => lang('Frontend.tour.review.sendFailed', [], $locale),
    'loginFailedNow' => lang('Frontend.tour.review.sendFailedNow', [], $locale),
    'reviewFailed' => lang('Frontend.tour.review.sendFailed', [], $locale),
    'reviewSent' => lang('Frontend.tour.review.sentClient', [], $locale),
    'reviewFailedNow' => lang('Frontend.tour.review.sendFailedNow', [], $locale),
    'enquiryFailed' => lang('Frontend.tour.enquiry.sendFailed', [], $locale),
    'enquirySent' => lang('Frontend.tour.enquiry.success', [], $locale),
    'selectDeparture' => lang('Frontend.tour.booking.departureSelect', [], $locale),
    'travelersMax' => lang('Frontend.tour.booking.travelersMax', ['{0}'], $locale),
    'departureSlots' => lang('Frontend.tour.booking.departureSlots', ['{0}'], $locale),
    'currencySuffix' => 'đ',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= base_url('assets/js/tour-detail.js?v=' . (@filemtime(FCPATH . 'assets/js/tour-detail.js') ?: time())) ?>"></script>
<?= $this->endSection() ?>
