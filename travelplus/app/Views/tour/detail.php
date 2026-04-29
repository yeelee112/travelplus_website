<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
if (! function_exists('tour_detail_html')) {
    function tour_detail_html(?string $html): string
    {
        return trim(strip_tags((string) $html, '<p><br><strong><em><u><ul><ol><li>'));
    }
}

$locale = service('request')->getLocale();
$tourTitle = (string) ($tour['title'] ?? '');
$tourImage = (string) ($tour['image'] ?? base_url('assets/images/avt-tour-01.jpg'));
$tourContinent = (string) ($tour['continent'] ?? '');
$gallery = array_values(array_filter($tour['media'] ?? [], static fn(array $item): bool => ($item['type'] ?? '') === 'gallery'));
$departures = $tour['departures'] ?? [];
$firstDeparture = $departures[0] ?? null;
$adultPrice = (float) ($firstDeparture['price'] ?? $tour['price']['amount'] ?? 0);
$adultPrice = $adultPrice > 0 ? $adultPrice : (float) ($tour['price']['amount'] ?? 0);
$adultPriceLabel = number_format($adultPrice, 0, ',', '.');
$childPrice = $adultPrice * 0.85;
$infantPrice = $adultPrice * 0.25;
$maxTravelers = max(1, (int) ($tour['max_travelers'] ?? 15));
$durationLabel = (string) ($tour['duration']['label'] ?? (($tour['duration']['days'] ?? 0) . ' Ngày ' . ($tour['duration']['nights'] ?? 0) . ' Đêm'));
$departureLabel = (string) ($firstDeparture['date_label'] ?? $tour['departure'] ?? '');
?>

<div class="breadcrumb-section two">
    <div class="home2-banner-slider">

                <div class="banner-bg" style="background-image: url('<?= esc($tourImage) ?>')"></div>

    </div>
    <div class="banner-content-wrap">
        <div class="container">
            <div class="banner-content">
                <h1><?= esc($tourTitle) ?></h1>
                <div class="batch">
                    <span><?= esc($durationLabel) ?><?= $departureLabel !== '' ? ' | Khởi hành: ' . esc($departureLabel) : '' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layouts/breadcrumb', ['breadcrumbs' => $breadcrumbs ?? []]) ?>

<div class="package-details-area pt-120 mb-120">
    <div class="container">
        <div class="row g-lg-4 gy-5">
            <div class="col-xl-8 col-lg-7">
                <div class="tour-overview-area mb-60">
                    <h4>Tổng quan về tour</h4>
                    <?php if (! empty($tour['short_description'])): ?>
                        <p><?= esc($tour['short_description']) ?></p>
                    <?php endif; ?>
                    <?php if (! empty($tour['overview'])): ?>
                        <div><?= tour_detail_html($tour['overview']) ?></div>
                    <?php elseif (! empty($tour['description'])): ?>
                        <div><?= tour_detail_html($tour['description']) ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($gallery !== []): ?>
                    <div class="location-slider-wrap mb-60">
                        <h4>Địa điểm khám phá</h4>
                        <div class="location-slider-area">
                            <div class="swiper package-dt-location-slider">
                                <div class="swiper-wrapper">
                                    <?php foreach ($gallery as $item): ?>
                                        <div class="swiper-slide">
                                            <div class="location-card">
                                                <div class="location-img">
                                                    <img src="<?= esc($item['url']) ?>" alt="<?= esc($item['alt_text'] ?: $tourTitle) ?>">
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
                                <div class="slider-btn location-slider-prev"><i class="bi bi-arrow-left"></i></div>
                                <div class="slider-btn location-slider-next"><i class="bi bi-arrow-right"></i></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (! empty($tour['itinerary_days'])): ?>
                    <div class="tour-itinerary-area mb-60">
                        <div class="itinerary-title">
                            <h4>Tour Itinerary</h4><a href="#" class="expand-btn">Expand All +</a>
                        </div>
                        <ul class="itinerary-list">
                            <li class="single-itinerary">
                                <div class="tour-plan-wrap">
                                    <div class="accordion accordion-flush" id="accordionTourPlan">
                                        <?php foreach ($tour['itinerary_days'] as $index => $day): ?>
                                            <?php $collapseId = 'flush-collapseTourPlan-' . ($index + 1); ?>
                                            <div class="accordion-item">
                                                <div class="accordion-header" id="flush-headingTourPlan-<?= $index + 1 ?>">
                                                    <div class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" role="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= esc($collapseId) ?>">
                                                        <h6><span>Ngày <?= esc($day['day_number']) ?>:</span><?= esc($day['title'] ?? '') ?></h6>
                                                    </div>
                                                </div>
                                                <div id="<?= esc($collapseId) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="flush-headingTourPlan-<?= $index + 1 ?>">
                                                    <div class="accordion-body">
                                                        <?php if (! empty($day['meals']) || ! empty($day['hotel_name']) || ! empty($day['transport_summary'])): ?>
                                                            <p class="mb-2">
                                                                <?= ! empty($day['meals']) ? '<strong>Meals:</strong> ' . esc($day['meals']) . ' ' : '' ?>
                                                                <?= ! empty($day['hotel_name']) ? '<strong>Hotel:</strong> ' . esc($day['hotel_name']) . ' ' : '' ?>
                                                                <?= ! empty($day['transport_summary']) ? '<strong>Transport:</strong> ' . esc($day['transport_summary']) : '' ?>
                                                            </p>
                                                        <?php endif; ?>
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

                <?php if (! empty($tour['faqs'])): ?>
                    <div class="faq-area mb-60">
                        <h4>Câu hỏi thường gặp</h4>
                        <div class="faq-wrap">
                            <div class="accordion accordion-flush" id="tourFaq">
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
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="package-sidebar">
                    <div class="package-widget">
                        <div class="price-area">
                            <h6>Price from</h6>
                            <span><?= esc($adultPriceLabel . ' VND') ?></span>
                        </div>
                        <ul class="tour-info-list mt-3">
                            <li><strong>Thời gian:</strong> <?= esc($durationLabel) ?></li>
                            <?php if ($departureLabel !== ''): ?>
                                <li><strong>Khởi hành:</strong> <?= esc($departureLabel) ?></li>
                            <?php endif; ?>
                            <li><strong>Khu vực:</strong> <?= esc($tourContinent) ?></li>
                        </ul>
                        <button class="primary-btn1 w-100 mt-4" type="button" data-bs-toggle="modal" data-bs-target="#bookingModal">
                            <span>Đặt tour</span><span>Đặt tour</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal booking-modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <div class="modal-header">
                <h4>Dates &amp; Availability</h4>
                <p><?= esc($tourTitle) ?></p>
            </div>
            <div class="modal-body">
                <div class="package-list">
                    <div class="accordion accordion-flush" id="accordionFlushPackage">
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <div class="accordion-button" role="button" data-bs-toggle="collapse" data-bs-target="#flush-package-collapseOne" aria-expanded="true">
                                    <div class="batch"><span>Chi tiết</span></div>
                                    <div class="title-area"><span class="check"></span><h6><?= esc($tourTitle) ?></h6></div>
                                    <span><?= esc($adultPriceLabel) ?>đ<sub>/người</sub></span>
                                </div>
                            </div>
                            <div id="flush-package-collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionFlushPackage">
                                <div class="accordion-body">
                                    <div class="tour-info-and-calculate-area">
                                        <p><?= esc($durationLabel) ?><?= $departureLabel !== '' ? ' | Khởi hành: ' . esc($departureLabel) : '' ?></p>
                                    </div>
                                    <div class="additional-service-area" data-max-travelers="<?= esc($maxTravelers) ?>">
                                        <h6>Số lượng người <sub>(Tối đa <?= esc($maxTravelers) ?> người)</sub></h6>
                                        <ul class="service-list booking-service-list">
                                            <li class="booking-service-item" data-service-type="adult" data-unit-price="<?= esc($adultPrice) ?>" data-min="1">
                                                <div class="service-info-wrap"><div class="service-info"><h6>Người lớn</h6><p><?= esc($adultPriceLabel) ?>đ</p></div></div>
                                                <div class="pricing-and-count-area"><div class="quantity-counter"><a data-type="adult" class="quantity__minus"><i class="bi bi-dash"></i></a><input type="text" class="quantity__input" name="adult_service_quantity" value="1" data-min="1"><a data-type="adult" class="quantity__plus"><i class="bi bi-plus"></i></a></div></div>
                                            </li>
                                            <li class="booking-service-item" data-service-type="child" data-unit-price="<?= esc($childPrice) ?>" data-min="0">
                                                <div class="service-info-wrap"><div class="service-info"><h6>Trẻ em (2 - 10 tuổi)</h6><p><?= esc(number_format($childPrice, 0, ',', '.')) ?>đ</p></div></div>
                                                <div class="pricing-and-count-area"><div class="quantity-counter"><a data-type="child" class="quantity__minus"><i class="bi bi-dash"></i></a><input type="text" class="quantity__input" name="child_service_quantity" value="0" data-min="0"><a data-type="child" class="quantity__plus"><i class="bi bi-plus"></i></a></div></div>
                                            </li>
                                            <li class="booking-service-item" data-service-type="infant" data-unit-price="<?= esc($infantPrice) ?>" data-min="0">
                                                <div class="service-info-wrap"><div class="service-info"><h6>Em bé (Dưới 2 tuổi)</h6><p><?= esc(number_format($infantPrice, 0, ',', '.')) ?>đ</p></div></div>
                                                <div class="pricing-and-count-area"><div class="quantity-counter"><a data-type="infant" class="quantity__minus"><i class="bi bi-dash"></i></a><input type="text" class="quantity__input" name="infant_service_quantity" value="0" data-min="0"><a data-type="infant" class="quantity__plus"><i class="bi bi-plus"></i></a></div></div>
                                            </li>
                                        </ul>
                                        <div class="booking-total-area">
                                            <span class="booking-total-label">Tổng giá tour: </span>
                                            <strong class="booking-grand-total"><?= esc($adultPriceLabel) ?> đ</strong>
                                        </div>
                                    </div>
                                    <div class="btn-area">
                                        <a class="primary-btn1 two" href="#"><span>Đặt ngay</span><span>Đặt ngay</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/tour-detail.js') ?>"></script>
<?= $this->endSection() ?>
