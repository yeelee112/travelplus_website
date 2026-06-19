<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authUser = is_array($authUser ?? null) ? $authUser : null;
$booking = is_array($pendingBooking ?? null) ? $pendingBooking : [];
$locale = service('request')->getLocale();
$checkoutMode = (string) ($checkoutMode ?? 'guest');
$adultQuantity = max(0, (int) ($booking['adult_quantity'] ?? 0));
$childQuantity = max(0, (int) ($booking['child_quantity'] ?? 0));
$infantQuantity = max(0, (int) ($booking['infant_quantity'] ?? 0));
$travelerCount = $adultQuantity + $childQuantity + $infantQuantity;
$grandTotal = (float) ($booking['grand_total'] ?? 0);
$subtotalAmount = (float) ($booking['subtotal_vnd'] ?? $grandTotal);
$discountAmount = (float) ($booking['discount_amount_vnd'] ?? 0);
$couponCode = trim((string) ($booking['coupon_code'] ?? ''));
$couponName = trim((string) ($booking['coupon_name'] ?? ''));
$singleRoomRequested = ! empty($booking['single_room_requested']);
$singleRoomSupplementAmount = max(0, (float) ($booking['single_room_supplement_vnd'] ?? 0));
$baseTourSubtotalAmount = max(0, (float) ($booking['coupon_eligible_subtotal_vnd'] ?? ($subtotalAmount - $singleRoomSupplementAmount)));
$depositRate = 0.10;
$depositAmount = $grandTotal * $depositRate;
$checkoutNotice = trim((string) ($checkoutNotice ?? ''));
$checkoutError = trim((string) ($checkoutError ?? ''));
$checkoutRetry = !empty($checkoutRetry);
$formatCurrency = static fn(float $amount): string => number_format($amount, 0, ',', '.') . ' VND';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
$travelerParts = [];

if ($adultQuantity > 0) {
    $travelerParts[] = $adultQuantity . ' ' . $t('tour.booking.adult');
}

if ($childQuantity > 0) {
    $travelerParts[] = $childQuantity . ' ' . $t('tour.booking.child');
}

if ($infantQuantity > 0) {
    $travelerParts[] = $infantQuantity . ' ' . $t('tour.booking.infant');
}

$travelerSummary = $travelerParts !== [] ? implode(', ', $travelerParts) : '-';
$durationLabelDisplay = trim((string) ($booking['duration_label'] ?? '-'));
if ($locale !== 'en' && $durationLabelDisplay !== '' && $durationLabelDisplay !== '-') {
    $durationLabelDisplay = strtr($durationLabelDisplay, [
        'Ngay' => 'Ngày',
        'Dem' => 'Đêm',
    ]);
}
$couponUi = $locale === 'en'
    ? [
        'placeholder' => 'Enter coupon code',
        'remove' => 'Remove',
        'applied' => 'Applied code',
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'current' => 'Current coupon',
        'none' => 'No coupon applied',
        'hint' => 'Enter a code and the final amount will update immediately.',
    ]
    : [
        'placeholder' => 'Nhập mã khuyến mãi',
        'remove' => 'Bỏ mã',
        'applied' => 'Đã áp dụng mã',
        'subtotal' => 'Tạm tính',
        'discount' => 'Giảm giá',
        'current' => 'Mã đang áp dụng',
        'none' => 'Chưa áp dụng mã',
        'hint' => 'Nhập mã để hệ thống cập nhật ngay số tiền cần thanh toán.',
    ];
$singleRoomLabel = $locale === 'en' ? 'Single room supplement' : 'Phụ thu phòng đơn';
$tourPriceLabel = $locale === 'en' ? 'Tour price' : 'Giá tour';
$singleRoomValueLabel = $locale === 'en'
    ? ($singleRoomRequested ? 'Requested' : 'Not requested')
    : ($singleRoomRequested ? 'Có yêu cầu' : 'Không yêu cầu');
?>
<style>
.checkout-payment-options.is-highlighted {
    outline: 2px solid #22c1f1;
    outline-offset: 10px;
    border-radius: 18px;
    transition: outline-color 0.25s ease;
}

@media (max-width: 767px) {
    .checkout-stepper-pane[data-step-pane="2"] .checkout-payment-option.is-selected::after {
        display: none;
    }

    .checkout-stepper-pane[data-step-pane="2"] .checkout-payment-option.is-selected {
        border-color: #0b3d91;
        background: #f2f8ff;
        box-shadow: inset 0 0 0 1px rgba(11, 61, 145, 0.2), 0 10px 22px rgba(11, 61, 145, 0.12);
    }
}
</style>
<div class="container pt-100 pb-100 checkout-stepper-page" data-checkout-stepper data-coupon-apply-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.applyCoupon', $locale)) ?>">
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="package-details-warpper">
                <div class="section-title mb-30">
                    <h2><?= esc($t('checkout.title')) ?></h2>
                </div>

                <?php if ($checkoutNotice !== ''): ?>
                    <div class="alert alert-success"><?= esc($checkoutNotice) ?></div>
                <?php endif; ?>

                <?php if ($checkoutError !== ''): ?>
                    <div class="alert alert-danger"><?= esc($checkoutError) ?></div>
                <?php endif; ?>
                <?php if ($checkoutError !== '' && $booking !== []): ?>
                    <div class="mb-4">
                        <p class="mb-2 text-muted"><?= esc($t('checkout.retryHelp')) ?></p>
                        <button type="button" class="primary-btn1 transparent" data-checkout-retry>
                            <span><?= esc($t('checkout.backToPaymentStep')) ?></span>
                            <span><?= esc($t('checkout.backToPaymentStep')) ?></span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($authUser !== null): ?>
                    <div class="alert alert-success">
                        <?= esc($t('checkout.signedInAs')) ?> <strong><?= esc($authUser['full_name'] ?: $authUser['email']) ?></strong>
                        <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.logout', $locale) ?>" class="checkout-inline-logout">
                            <?= csrf_field() ?>
                            <button type="submit"><?= esc($t('auth.logout')) ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        <?= esc($t('checkout.checkoutMode')) ?>: <strong><?= esc($checkoutMode === 'member' ? $t('checkout.member') : $t('checkout.guest')) ?></strong>.
                    </div>
                <?php endif; ?>

                <div class="checkout-stepper-header mb-40">
                    <button type="button" class="checkout-stepper-tab is-active" data-step-target="1">
                        <span class="step-number">1</span>
                        <span class="step-label"><?= esc($t('checkout.step1')) ?></span>
                    </button>
                    <span class="step-line"></span>
                    <button type="button" class="checkout-stepper-tab" data-step-target="2">
                        <span class="step-number">2</span>
                        <span class="step-label"><?= esc($t('checkout.step2')) ?></span>
                    </button>
                    <span class="step-line"></span>
                    <button type="button" class="checkout-stepper-tab" data-step-target="3">
                        <span class="step-number">3</span>
                        <span class="step-label"><?= esc($t('checkout.step3')) ?></span>
                    </button>
                </div>

                <form class="checkout-stepper-form" data-checkout-form novalidate>
                    <?= csrf_field() ?>
                    <div class="checkout-stepper-pane is-active" data-step-pane="1">
                        <div class="contact-form-wrap">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-full-name"><?= esc($t('checkout.fullName')) ?></label>
                                        <input
                                            type="text"
                                            id="checkout-full-name"
                                            name="full_name"
                                            value="<?= esc((string) ($authUser['full_name'] ?? '')) ?>"
                                            placeholder="<?= esc($t('checkout.customerNamePlaceholder')) ?>"
                                            required
                                            data-summary-field="full_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-email"><?= esc($t('checkout.email')) ?></label>
                                        <input
                                            type="email"
                                            id="checkout-email"
                                            name="email"
                                            value="<?= esc((string) ($authUser['email'] ?? '')) ?>"
                                            placeholder="<?= esc($t('checkout.emailPlaceholder')) ?>"
                                            required
                                            data-summary-field="email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-phone"><?= esc($t('checkout.phone')) ?></label>
                                        <input
                                            type="text"
                                            id="checkout-phone"
                                            name="phone"
                                            value="<?= esc((string) ($authUser['phone'] ?? '')) ?>"
                                            placeholder="<?= esc($t('checkout.phonePlaceholder')) ?>"
                                            required
                                            data-summary-field="phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-note"><?= esc($t('checkout.note')) ?></label>
                                        <input
                                            type="text"
                                            id="checkout-note"
                                            name="note"
                                            placeholder="<?= esc($t('checkout.notePlaceholder')) ?>"
                                            data-summary-field="note">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1" data-step-next="2"><?= esc($t('checkout.continuePayment')) ?></button>
                        </div>
                    </div>

                    <div class="checkout-stepper-pane" data-step-pane="2">
                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="checkout-stepper-card checkout-payment-card checkout-payment-card--minimal">
                                    <div class="checkout-card-head">
                                        <div>
                                            <h5 class="checkout-card-title mb-1"><?= esc($t('checkout.paymentOptions')) ?></h5>
                                        </div>
                                        <div class="checkout-card-head__actions">
                                            <button type="button" class="checkout-text-btn" data-price-breakdown-toggle><?= esc($t('checkout.viewPriceBreakdown')) ?></button>
                                            <button type="button" class="checkout-text-btn" data-step-prev="1">Chỉnh sửa thông tin</button>
                                        </div>
                                    </div>

                                    <div class="checkout-contact-strip">
                                        <div class="checkout-contact-item">
                                            <span><?= esc($t('checkout.fullName')) ?></span>
                                            <strong data-summary-output="full_name">-</strong>
                                        </div>
                                        <div class="checkout-contact-item">
                                            <span><?= esc($t('checkout.phone')) ?></span>
                                            <strong data-summary-output="phone">-</strong>
                                        </div>
                                        <div class="checkout-contact-item">
                                            <span><?= esc($t('checkout.email')) ?></span>
                                            <strong data-summary-output="email">-</strong>
                                        </div>
                                        <div class="checkout-contact-item">
                                            <span><?= esc($t('checkout.note')) ?></span>
                                            <strong data-summary-output="note">-</strong>
                                        </div>
                                    </div>

                                    <div class="checkout-price-breakdown" data-price-breakdown hidden>
                                        <div class="price-breakdown-row">
                                            <span><?= esc($t('checkout.priceAdult', [$adultQuantity])) ?></span>
                                            <strong><?= esc($formatCurrency((float) ($booking['adult_price'] ?? 0) * $adultQuantity)) ?></strong>
                                        </div>
                                        <div class="price-breakdown-row">
                                            <span><?= esc($t('checkout.priceChild', [$childQuantity])) ?></span>
                                            <strong><?= esc($formatCurrency((float) ($booking['child_price'] ?? 0) * $childQuantity)) ?></strong>
                                        </div>
                                        <div class="price-breakdown-row">
                                            <span><?= esc($t('checkout.priceInfant', [$infantQuantity])) ?></span>
                                            <strong><?= esc($formatCurrency((float) ($booking['infant_price'] ?? 0) * $infantQuantity)) ?></strong>
                                        </div>
                                        <?php if ($singleRoomSupplementAmount > 0): ?>
                                            <div class="price-breakdown-row">
                                                <span><?= esc($singleRoomLabel) ?></span>
                                                <strong data-single-room-amount><?= esc($formatCurrency($singleRoomSupplementAmount)) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="checkout-coupon-row">
                                        <label for="checkout-coupon"><?= esc($t('checkout.couponCode')) ?></label>
                                        <div class="checkout-coupon-input">
                                            <input type="text" id="checkout-coupon" value="<?= esc($couponCode, 'attr') ?>" placeholder="<?= esc($couponUi['placeholder'], 'attr') ?>"<?= $couponCode !== '' ? ' hidden' : '' ?>>
                                            <button type="button" class="checkout-text-btn" data-coupon-apply<?= $couponCode !== '' ? ' hidden' : '' ?>><?= esc($t('checkout.apply')) ?></button>
                                            <div class="checkout-coupon-chip<?= $couponCode === '' ? ' hidden' : '' ?>" data-coupon-chip>
                                                <span data-coupon-chip-text><?= esc($couponName !== '' ? $couponName . ' (' . $couponCode . ')' : $couponCode) ?></span>
                                                <button type="button" class="checkout-coupon-chip__remove" data-coupon-remove aria-label="<?= esc($couponUi['remove'], 'attr') ?>">×</button>
                                            </div>
                                        </div>
                                        <p class="small mb-0 checkout-coupon-feedback text-muted" data-coupon-message hidden></p>
                                    </div>

                                    <div class="checkout-summary-subpanel checkout-summary-subpanel--plans">
                                        <div class="checkout-section-subtitle">Hình thức thanh toán</div>
                                        <div class="checkout-plan-options">
                                            <label class="checkout-plan-option checkout-plan-option--inline">
                                                <input type="radio" name="payment_plan" value="full" data-payment-plan="full">
                                                <span class="checkout-plan-option__copy">
                                                    <strong><?= esc($t('checkout.payFull')) ?></strong>
                                                    <small>Thanh toán đủ một lần.</small>
                                                </span>
                                                <span class="checkout-plan-option__amount" data-plan-preview="full"><?= esc($formatCurrency($grandTotal)) ?></span>
                                            </label>
                                            <label class="checkout-plan-option checkout-plan-option--inline">
                                                <input type="radio" name="payment_plan" value="deposit" data-payment-plan="deposit" checked>
                                                <span class="checkout-plan-option__copy">
                                                    <strong><?= esc($t('checkout.payDeposit')) ?></strong>
                                                    <small>Giữ chỗ trước, thanh toán phần còn lại sau.</small>
                                                </span>
                                                <span class="checkout-plan-option__amount" data-plan-preview="deposit"><?= esc($formatCurrency($depositAmount)) ?></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="checkout-payment-divider"></div>

                                    <div class="checkout-methods-head">
                                        <h6 class="checkout-section-subtitle mb-0"><?= esc($t('checkout.paymentMethods')) ?></h6>
                                    </div>
                                    <div class="checkout-payment-options">
                                        <label class="checkout-payment-option is-selected">
                                            <input type="radio" name="payment_method" value="paypal" checked>
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/Paypal-Logo.png')) ?>" alt="" class="checkout-payment-logo" loading="lazy" decoding="async" width="96" height="40">
                                            </span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="vnpay">
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/VNPay-Logo.png')) ?>" alt="" class="checkout-payment-logo" loading="lazy" decoding="async" width="96" height="40">
                                            </span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="vietqr">
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/VietQR-Logo.png')) ?>" alt="" class="checkout-payment-logo" loading="lazy" decoding="async" width="96" height="40">
                                            </span>
                                        </label>
                                    </div>
                                    <div class="checkout-vietqr-box" data-vietqr-box data-vietqr-create-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.vietqrGenerate', $locale)) ?>" data-vietqr-complete-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.vietqrComplete', $locale)) ?>" hidden>
                                        <div class="checkout-vietqr-qr">
                                            <img src="" alt="VietQR" data-vietqr-image hidden loading="lazy" decoding="async" width="180" height="180">
                                            <span data-vietqr-placeholder>QR</span>
                                        </div>
                                        <div>
                                            <h6><?= esc($t('checkout.vietqrTitle')) ?></h6>
                                            <p data-vietqr-message style="font-size:14px;"><?= esc($t('checkout.vietqrHint')) ?></p>
                                            <div class="checkout-vietqr-meta">
                                                <div><strong><?= esc($t('checkout.vietqrAmount')) ?></strong> <span data-vietqr-amount>-</span></div>
                                                <div><strong><?= esc($t('checkout.vietqrContent')) ?></strong> <span data-vietqr-add-info>-</span></div>
                                                <div><strong><?= esc($t('checkout.vietqrAccountName')) ?></strong> <span data-vietqr-account-name>-</span></div>
                                                <div><strong><?= esc($t('checkout.vietqrAccountNo')) ?></strong> <span data-vietqr-account-no>-</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="checkout-vnpay-box" data-vnpay-box hidden>
                                        <div>
                                            <h6><?= esc($t('checkout.vnpayTitle')) ?></h6>
                                            <p><?= esc($t('checkout.vnpayHint')) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="checkout-terms-wrap">
                                    <label class="checkout-terms-check">
                                        <input type="checkbox" name="agree_terms" data-agree-terms>
                                        <span>
                                            <?= esc($t('checkout.termsAgreePrefix')) ?>
                                            <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('legal.terms', $locale)) ?>" target="_blank" rel="noopener noreferrer"><?= esc($t('checkout.termsOfService')) ?></a>
                                            <?= esc($t('checkout.and')) ?>
                                            <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('legal.privacy', $locale)) ?>" target="_blank" rel="noopener noreferrer"><?= esc($t('checkout.privacyStatement')) ?></a>
                                        </span>
                                    </label>
                                    <p class="checkout-inline-error" data-step-error hidden></p>
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="checkout-stepper-card checkout-booking-summary checkout-booking-summary--minimal">
                                    <img class="checkout-booking-image pb-10" src="<?= esc($booking['tour_image']) ?>" alt="<?= esc((string) ($booking['tour_title'] ?? 'Travel Plus tour')) ?>" loading="lazy" decoding="async" width="560" height="360">
                                    <h5 class="checkout-card-title"><?= esc((string) ($booking['tour_title'] ?? $t('checkout.bookingTitleFallback'))) ?></h5>
                                    <div class="checkout-summary-list">
                                        <div class="checkout-summary-group">
                                            <div class="checkout-summary-section-title">Tour</div>
                                            <div class="checkout-summary-meta-list checkout-summary-meta-list--plain">
                                                <div class="checkout-summary-meta-row">
                                                    <span><?= esc($t('checkout.travelDate')) ?></span>
                                                    <strong><?= esc((string) ($booking['departure_label'] ?? '-')) ?></strong>
                                                </div>
                                                <div class="checkout-summary-meta-row">
                                                    <span><?= esc($t('checkout.period')) ?></span>
                                                    <strong><?= esc($durationLabelDisplay) ?></strong>
                                                </div>
                                                <div class="checkout-summary-meta-row">
                                                    <span><?= esc($t('checkout.travelers')) ?></span>
                                                    <strong><?= esc($travelerSummary) ?></strong>
                                                </div>
                                                <?php if ($singleRoomSupplementAmount > 0): ?>
                                                    <div class="checkout-summary-meta-row">
                                                        <span><?= esc($singleRoomLabel) ?></span>
                                                        <strong><?= esc($singleRoomValueLabel) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="checkout-summary-group">
                                            <div class="checkout-summary-section-title">Thanh toán</div>
                                            <div class="checkout-summary-pricing">
                                                <div class="checkout-summary-price-row">
                                                    <span><?= esc($tourPriceLabel) ?></span>
                                                    <strong data-base-subtotal-amount><?= esc($formatCurrency($baseTourSubtotalAmount)) ?></strong>
                                                </div>
                                                <?php if ($singleRoomSupplementAmount > 0): ?>
                                                    <div class="checkout-summary-price-row" data-single-room-row>
                                                        <span><?= esc($singleRoomLabel) ?></span>
                                                        <strong data-single-room-amount><?= esc($formatCurrency($singleRoomSupplementAmount)) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="checkout-summary-price-row">
                                                    <span><?= esc($couponUi['discount']) ?></span>
                                                    <strong data-discount-amount>-<?= esc($formatCurrency($discountAmount)) ?></strong>
                                                </div>
                                                <div class="checkout-summary-price-row checkout-summary-price-row--grand">
                                                    <span><?= esc($t('checkout.total')) ?></span>
                                                    <strong data-grand-total><?= esc($formatCurrency($grandTotal)) ?></strong>
                                                </div>
                                                <div class="checkout-summary-price-row checkout-summary-price-row--due">
                                                    <span data-payment-plan-label><?= esc($t('checkout.depositLine')) ?></span>
                                                    <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="checkout-summary-cta mt-4">
                                        <button
                                            type="button"
                                            class="primary-btn1 w-100"
                                            data-pay-submit
                                            data-paypal-create-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.paypalCreateOrder', $locale)) ?>"
                                            data-vnpay-create-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.vnpayCreatePayment', $locale)) ?>">
                                            <span><?= esc($t('checkout.step3')) ?></span>
                                            <span><?= esc($t('checkout.step3')) ?></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1 transparent" data-step-prev="1"><?= esc($t('checkout.back')) ?></button>
                        </div>
                    </div>

                    <div class="checkout-stepper-pane" data-step-pane="3">
                        <div class="checkout-stepper-card">
                            <h5 class="checkout-card-title"><?= esc($t('checkout.step3')) ?></h5>
                            <div class="checkout-finish-grid">
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.fullName')) ?></span>
                                    <strong data-summary-output="full_name">-</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.email')) ?></span>
                                    <strong data-summary-output="email">-</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.phone')) ?></span>
                                    <strong data-summary-output="phone">-</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.tourLabel')) ?></span>
                                    <strong><?= esc((string) ($booking['tour_title'] ?? '-')) ?></strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.paymentMethods')) ?></span>
                                    <strong data-payment-method-output>PayPal</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.paymentOptions')) ?></span>
                                    <strong data-payment-plan-output><?= esc($t('checkout.payDeposit')) ?></strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($couponUi['current']) ?></span>
                                    <strong data-coupon-current-finish><?= esc($couponCode !== '' ? ($couponName !== '' ? $couponName . ' (' . $couponCode . ')' : $couponCode) : $couponUi['none']) ?></strong>
                                </div>
                                <?php if ($singleRoomSupplementAmount > 0): ?>
                                    <div class="checkout-finish-item" data-single-room-row>
                                        <span><?= esc($singleRoomLabel) ?></span>
                                        <strong data-single-room-amount><?= esc($formatCurrency($singleRoomSupplementAmount)) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.total')) ?></span>
                                    <strong data-grand-total><?= esc($formatCurrency($grandTotal)) ?></strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span data-payment-plan-label><?= esc($t('checkout.depositLine')) ?></span>
                                    <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span><?= esc($t('checkout.noteLabel')) ?></span>
                                    <strong data-summary-output="note">-</strong>
                                </div>
                            </div>
                            <div class="checkout-finish-note" data-step-three-note>
                                <?= esc($t('checkout.vietqrTransferNote')) ?>
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1 transparent" data-step-prev="2"><?= esc($t('checkout.back')) ?></button>
                            <button type="button" class="primary-btn1" data-vietqr-complete><?= esc($t('checkout.completeTransfer')) ?></button>
                            <a href="<?= esc((string) ($booking['tour_link'] ?? localized_url(''))) ?>" class="primary-btn1 d-none" data-step-three-tour-link><?= esc($t('checkout.backToTour')) ?></a>
                        </div>
                        <p class="mt-3 mb-0 text-muted d-none" data-vietqr-complete-note>
                            <?= esc($t('checkout.vietqrCompleteHint')) ?>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('[data-checkout-stepper]');

    if (!root) {
        return;
    }

    const panes = Array.from(root.querySelectorAll('[data-step-pane]'));
    const tabs = Array.from(root.querySelectorAll('[data-step-target]'));
    const nextButtons = Array.from(root.querySelectorAll('[data-step-next]'));
    const prevButtons = Array.from(root.querySelectorAll('[data-step-prev]'));
    const summaryFields = Array.from(root.querySelectorAll('[data-summary-field]'));
    const paymentPlanInputs = Array.from(root.querySelectorAll('[data-payment-plan]'));
    const paymentMethodInputs = Array.from(root.querySelectorAll('input[name="payment_method"]'));
    const paymentAmountOutputs = Array.from(root.querySelectorAll('[data-payment-amount]'));
    const paymentPlanOutputs = Array.from(root.querySelectorAll('[data-payment-plan-output]'));
    const paymentPlanLabels = Array.from(root.querySelectorAll('[data-payment-plan-label]'));
    const planPreviewFullOutputs = Array.from(root.querySelectorAll('[data-plan-preview="full"]'));
    const planPreviewDepositOutputs = Array.from(root.querySelectorAll('[data-plan-preview="deposit"]'));
    const paymentMethodOutputs = Array.from(root.querySelectorAll('[data-payment-method-output]'));
    const grandTotalOutputs = Array.from(root.querySelectorAll('[data-grand-total]'));
    const baseSubtotalOutputs = Array.from(root.querySelectorAll('[data-base-subtotal-amount]'));
    const singleRoomOutputs = Array.from(root.querySelectorAll('[data-single-room-amount]'));
    const discountOutputs = Array.from(root.querySelectorAll('[data-discount-amount]'));
    const singleRoomRows = Array.from(root.querySelectorAll('[data-single-room-row]'));
    const errorBox = root.querySelector('[data-step-error]');
    const termsCheckbox = root.querySelector('[data-agree-terms]');
    const bookingSummaryCard = root.querySelector('.checkout-booking-summary');
    const vietQrBox = root.querySelector('[data-vietqr-box]');
    const priceBreakdown = root.querySelector('[data-price-breakdown]');
    const breakdownToggle = root.querySelector('[data-price-breakdown-toggle]');
    const couponInput = root.querySelector('#checkout-coupon');
    const couponApplyButton = root.querySelector('[data-coupon-apply]');
    const couponChip = root.querySelector('[data-coupon-chip]');
    const couponChipText = root.querySelector('[data-coupon-chip-text]');
    const couponRemoveButton = root.querySelector('[data-coupon-remove]');
    const couponCurrentOutputs = Array.from(root.querySelectorAll('[data-coupon-current-summary], [data-coupon-current-finish]'));
    const couponMessage = root.querySelector('[data-coupon-message]');
    const couponApplyUrl = root.dataset.couponApplyUrl || '';
    const paySubmitButtons = Array.from(root.querySelectorAll('[data-pay-submit]'));
    const vietQrCreateUrl = vietQrBox ? vietQrBox.dataset.vietqrCreateUrl : '';
    const vietQrCompleteUrl = vietQrBox ? vietQrBox.dataset.vietqrCompleteUrl : '';
    const vnpayBox = root.querySelector('[data-vnpay-box]');
    const paymentMethodsCard = root.querySelector('.checkout-payment-options');
    const vnpayCreateUrl = paySubmitButtons[0] ? (paySubmitButtons[0].dataset.vnpayCreateUrl || '') : '';
    const vietQrCompleteButton = root.querySelector('[data-vietqr-complete]');
    const stepThreeNote = root.querySelector('[data-step-three-note]');
    const stepThreeTourLink = root.querySelector('[data-step-three-tour-link]');
    const vietQrCompleteNote = root.querySelector('[data-vietqr-complete-note]');
    const vietQrImage = root.querySelector('[data-vietqr-image]');
    const vietQrPlaceholder = root.querySelector('[data-vietqr-placeholder]');
    const vietQrMessage = root.querySelector('[data-vietqr-message]');
    const vietQrAmount = root.querySelector('[data-vietqr-amount]');
    const vietQrAddInfo = root.querySelector('[data-vietqr-add-info]');
    const vietQrAccountName = root.querySelector('[data-vietqr-account-name]');
    const vietQrAccountNo = root.querySelector('[data-vietqr-account-no]');
    const stepThreeTab = root.querySelector('[data-step-target="3"]');
    const stepThreePane = root.querySelector('[data-step-pane="3"]');
    const stepLines = Array.from(root.querySelectorAll('.checkout-stepper-header .step-line'));
    const stepThreeLine = stepLines.length > 1 ? stepLines[1] : null;
    const defaultStepTwoLabel = <?= json_encode($t('checkout.step3')) ?>;
    const currency = new Intl.NumberFormat('vi-VN');
    const csrfTokenName = window.CSRF_TOKEN_NAME || document.querySelector('meta[name="csrf-token-name"]')?.content || '';
    const csrfToken = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content || '';
    const pricingState = {
        baseSubtotal: <?= json_encode($baseTourSubtotalAmount) ?>,
        singleRoomSupplement: <?= json_encode($singleRoomSupplementAmount) ?>,
        subtotal: <?= json_encode($subtotalAmount) ?>,
        discount: <?= json_encode($discountAmount) ?>,
        grandTotal: <?= json_encode($grandTotal) ?>,
        depositAmount: <?= json_encode($depositAmount) ?>
    };
    const couponText = {
        appliedPrefix: <?= json_encode($couponUi['applied']) ?>,
        none: <?= json_encode($couponUi['none']) ?>,
        invalid: <?= json_encode($locale === 'en' ? 'Could not apply the coupon code. Please check the code or try another one.' : 'Không thể áp dụng mã khuyến mãi. Vui lòng kiểm tra lại mã hoặc thử mã khác.') ?>
    };
    const planLabels = {
        full: <?= json_encode($t('checkout.payFull')) ?>,
        deposit: <?= json_encode($t('checkout.payDeposit')) ?>
    };
    const planLineLabels = {
        full: <?= json_encode($t('checkout.payFull')) ?>,
        deposit: <?= json_encode($t('checkout.depositLine')) ?>
    };
    const paymentLabels = {
        paypal: 'PayPal',
        vnpay: <?= json_encode($t('checkout.vnpayLabel')) ?>,
        vietqr: 'VietQR'
    };
    let lastVietQrKey = '';

    const formatCurrency = function (amount) {
        return currency.format(amount) + ' VND';
    };

    const recalcPricingState = function () {
        pricingState.depositAmount = Math.round((Number(pricingState.grandTotal) || 0) * 0.10);
    };

    const clearError = function () {
        if (!errorBox) {
            return;
        }

        errorBox.hidden = true;
        errorBox.textContent = '';
    };

    const appendCsrf = function (formData) {
        if (csrfTokenName && csrfToken) {
            formData.append(csrfTokenName, csrfToken);
        }
    };

    const setPayButtonsState = function (disabled, label) {
        paySubmitButtons.forEach(function (button) {
            if (disabled) {
                button.setAttribute('disabled', 'disabled');
            } else {
                button.removeAttribute('disabled');
            }

            const spans = button.querySelectorAll('span');
            if (spans.length >= 2) {
                spans[0].textContent = label;
                spans[1].textContent = label;
                return;
            }

            button.textContent = label;
        });
    };

    const focusPaymentStep = function () {
        setStep(2);

        window.setTimeout(function () {
            if (paymentMethodsCard) {
                paymentMethodsCard.classList.add('is-highlighted');
                paymentMethodsCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

                window.setTimeout(function () {
                    paymentMethodsCard.classList.remove('is-highlighted');
                }, 1800);

                return;
            }

            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 80);
    };

    const setError = function (message) {
        if (!errorBox) {
            return;
        }

        errorBox.hidden = false;
        errorBox.textContent = message;
    };

    const setVietQrState = function (state) {
        if (!vietQrBox) {
            return;
        }

        if (state.image && vietQrImage) {
            vietQrImage.src = state.image;
            vietQrImage.hidden = false;
        } else if (vietQrImage) {
            vietQrImage.hidden = true;
            vietQrImage.removeAttribute('src');
        }

        if (vietQrPlaceholder) {
            vietQrPlaceholder.hidden = !!state.image;
            vietQrPlaceholder.textContent = state.placeholder || 'QR';
        }

        if (vietQrMessage) {
            vietQrMessage.textContent = state.message || '';
        }

        if (vietQrAmount) {
            vietQrAmount.textContent = state.amount || '-';
        }

        if (vietQrAddInfo) {
            vietQrAddInfo.textContent = state.addInfo || '-';
        }

        if (vietQrAccountName) {
            vietQrAccountName.textContent = state.accountName || '-';
        }

        if (vietQrAccountNo) {
            vietQrAccountNo.textContent = state.accountNo || '-';
        }
    };

    const setStep = function (step) {
        panes.forEach(function (pane) {
            pane.classList.toggle('is-active', pane.dataset.stepPane === String(step));
        });

        tabs.forEach(function (tab) {
            const tabStep = Number(tab.dataset.stepTarget);
            tab.classList.toggle('is-active', tabStep === step);
            tab.classList.toggle('is-complete', tabStep < step);
        });
    };

    const updateSummary = function () {
        summaryFields.forEach(function (field) {
            const key = field.dataset.summaryField;
            const value = field.value.trim() || '-';

            root.querySelectorAll('[data-summary-output="' + key + '"]').forEach(function (target) {
                target.textContent = value;
            });
        });
    };

    const updatePaymentPlan = function () {
        const selectedPlan = paymentPlanInputs.find(function (input) {
            return input.checked;
        });
        const plan = selectedPlan ? selectedPlan.value : 'deposit';
        const amount = plan === 'full'
            ? (Number(pricingState.grandTotal) || 0)
            : (Number(pricingState.depositAmount) || 0);

        paymentAmountOutputs.forEach(function (output) {
            output.textContent = formatCurrency(amount);
        });

        paymentPlanOutputs.forEach(function (output) {
            output.textContent = planLabels[plan] || '';
        });

        paymentPlanLabels.forEach(function (output) {
            output.textContent = planLineLabels[plan] || '';
        });
    };

    const renderPricing = function () {
        recalcPricingState();
        pricingState.baseSubtotal = Math.max(0, (Number(pricingState.subtotal) || 0) - (Number(pricingState.singleRoomSupplement) || 0));

        planPreviewFullOutputs.forEach(function (output) {
            output.textContent = formatCurrency(Number(pricingState.grandTotal) || 0);
        });

        planPreviewDepositOutputs.forEach(function (output) {
            output.textContent = formatCurrency(Number(pricingState.depositAmount) || 0);
        });

        grandTotalOutputs.forEach(function (output) {
            output.textContent = formatCurrency(Number(pricingState.grandTotal) || 0);
        });

        baseSubtotalOutputs.forEach(function (output) {
            output.textContent = formatCurrency(Number(pricingState.baseSubtotal) || 0);
        });

        singleRoomOutputs.forEach(function (output) {
            output.textContent = formatCurrency(Number(pricingState.singleRoomSupplement) || 0);
        });

        discountOutputs.forEach(function (output) {
            output.textContent = '-' + formatCurrency(Number(pricingState.discount) || 0);
        });

        singleRoomRows.forEach(function (row) {
            row.hidden = (Number(pricingState.singleRoomSupplement) || 0) <= 0;
        });

        updatePaymentPlan();
    };

    const setCouponFeedback = function (message, isError) {
        if (!couponMessage) {
            return;
        }

        const text = String(message || '').trim();
        couponMessage.hidden = text === '';
        couponMessage.textContent = text;
        couponMessage.classList.toggle('text-danger', text !== '' && !!isError);
        couponMessage.classList.toggle('text-success', text !== '' && !isError);
        couponMessage.classList.toggle('text-muted', text === '');
    };

    const setCouponButtonsState = function (disabled) {
        [couponApplyButton, couponRemoveButton].forEach(function (button) {
            if (!button) {
                return;
            }

            if (disabled) {
                button.setAttribute('disabled', 'disabled');
            } else {
                button.removeAttribute('disabled');
            }
        });

        if (!couponInput) {
            return;
        }

        if (disabled) {
            couponInput.setAttribute('disabled', 'disabled');
            return;
        }

        couponInput.removeAttribute('disabled');
    };

    const syncCouponPricing = function (payload) {
        pricingState.subtotal = Number(payload.subtotal || 0);
        pricingState.discount = Number(payload.discount_amount || 0);
        pricingState.grandTotal = Number(payload.grand_total || 0);
        pricingState.depositAmount = Number(payload.deposit_amount || 0);
        renderPricing();
    };

    const renderCouponCurrent = function (coupon) {
        const couponCode = coupon && coupon.code ? String(coupon.code) : '';
        const couponName = coupon && coupon.name ? String(coupon.name) : '';
        const label = couponCode === ''
            ? couponText.none
            : (couponName !== '' ? couponName + ' (' + couponCode + ')' : couponCode);

        if (couponChipText) {
            couponChipText.textContent = label;
        }

        if (couponChip) {
            couponChip.hidden = couponCode === '';
        }

        if (couponInput) {
            couponInput.hidden = couponCode !== '';
        }

        if (couponApplyButton) {
            couponApplyButton.hidden = couponCode !== '';
        }

        couponCurrentOutputs.forEach(function (output) {
            output.textContent = label;
        });
    };

    const submitCouponCode = async function (couponCode) {
        if (!couponApplyUrl || !couponInput) {
            return;
        }

        setCouponButtonsState(true);
        clearError();

        try {
            const formData = new FormData();
            appendCsrf(formData);
            formData.append('coupon_code', couponCode);

            const response = await fetch(couponApplyUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload.message || couponText.invalid);
            }

            syncCouponPricing(payload);

            const appliedCoupon = payload.coupon && typeof payload.coupon === 'object' ? payload.coupon : null;
            const appliedCode = appliedCoupon && appliedCoupon.code ? String(appliedCoupon.code) : '';
            const appliedName = appliedCoupon && appliedCoupon.name ? String(appliedCoupon.name) : '';

            couponInput.value = appliedCode;
            renderCouponCurrent(appliedCoupon);

            if (couponRemoveButton) {
                couponRemoveButton.hidden = appliedCode === '';
            }

            if (appliedCode === '') {
                setCouponFeedback(payload.message || '', false);
            } else {
                const appliedLabel = appliedName !== ''
                    ? appliedName + ' (' + appliedCode + ')'
                    : appliedCode;
                setCouponFeedback(payload.message || (couponText.appliedPrefix + ': ' + appliedLabel), false);
            }

            lastVietQrKey = '';

            const selectedMethod = paymentMethodInputs.find(function (input) {
                return input.checked;
            });

            if (selectedMethod && selectedMethod.value === 'vietqr') {
                await generateVietQr();
            }
        } catch (error) {
            setCouponFeedback(error.message || couponText.invalid, true);
        } finally {
            setCouponButtonsState(false);
        }
    };

    const generateVietQr = async function () {
        if (!vietQrBox || vietQrCreateUrl === '') {
            return;
        }

        const selectedMethod = paymentMethodInputs.find(function (input) {
            return input.checked;
        });
        const selectedPlan = paymentPlanInputs.find(function (input) {
            return input.checked;
        });

        if (!selectedMethod || selectedMethod.value !== 'vietqr') {
            return;
        }

        const plan = selectedPlan ? selectedPlan.value : 'deposit';
        const key = selectedMethod.value + ':' + plan;

        if (key === lastVietQrKey && vietQrImage && !vietQrImage.hidden) {
            return;
        }

        setVietQrState({
            image: '',
            placeholder: '...',
            message: 'Đang tạo mã VietQR...',
            amount: formatCurrency(plan === 'full' ? (Number(pricingState.grandTotal) || 0) : (Number(pricingState.depositAmount) || 0)),
            addInfo: '-',
            accountName: '-',
            accountNo: '-',
        });

        try {
            const formData = new FormData();
            appendCsrf(formData);
            formData.append('payment_method', 'vietqr');
            formData.append('payment_plan', plan);
            summaryFields.forEach(function (field) {
                formData.append(field.name, field.value.trim());
            });

            const response = await fetch(vietQrCreateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const payload = await response.json();

            if (!response.ok || !payload.ok || !payload.qr) {
                throw new Error(payload.message || 'Không thể tạo mã VietQR.');
            }

            lastVietQrKey = key;
            setVietQrState({
                image: payload.qr.image || '',
                placeholder: 'QR',
                message: 'Quét mã để chuyển khoản theo đúng số tiền và nội dung.',
                amount: formatCurrency(Number(payload.qr.amount || 0)),
                addInfo: payload.qr.add_info || '-',
                accountName: payload.qr.account_name || '-',
                accountNo: payload.qr.account_no || '-',
            });
        } catch (error) {
            lastVietQrKey = '';
            setVietQrState({
                image: '',
                placeholder: 'QR',
                message: error.message || 'Không thể tạo mã VietQR.',
                amount: formatCurrency(plan === 'full' ? (Number(pricingState.grandTotal) || 0) : (Number(pricingState.depositAmount) || 0)),
                addInfo: '-',
                accountName: '-',
                accountNo: '-',
            });
        }
    };

    const updatePaymentMethod = function () {
        const selectedMethod = paymentMethodInputs.find(function (input) {
            return input.checked;
        });
        const method = selectedMethod ? selectedMethod.value : 'paypal';
        const label = paymentLabels[method] || method;
        const isPaypal = method === 'paypal';
        const isVnpay = method === 'vnpay';
        const isVietQr = method === 'vietqr';
        const isRedirectGateway = isPaypal || isVnpay;

        paymentMethodOutputs.forEach(function (output) {
            output.textContent = label;
        });

        paymentMethodInputs.forEach(function (input) {
            const option = input.closest('.checkout-payment-option');

            if (! option) {
                return;
            }

            option.classList.toggle('is-selected', input.checked);
        });

        if (vietQrBox) {
            vietQrBox.hidden = method !== 'vietqr';
        }

        if (vnpayBox) {
            vnpayBox.hidden = !isVnpay;
        }

        setPayButtonsState(
            false,
            isPaypal
                ? <?= json_encode($t('checkout.payWithPaypal')) ?>
                : (isVnpay ? <?= json_encode($t('checkout.payWithVnpay')) ?> : defaultStepTwoLabel)
        );

        if (stepThreeTab) {
            stepThreeTab.classList.toggle('d-none', isRedirectGateway);
        }

        if (stepThreePane) {
            stepThreePane.classList.toggle('d-none', isRedirectGateway);
        }

        if (stepThreeLine) {
            stepThreeLine.classList.toggle('d-none', isRedirectGateway);
        }

        if (vietQrCompleteButton) {
            vietQrCompleteButton.classList.toggle('d-none', !isVietQr);
        }

        if (vietQrCompleteNote) {
            vietQrCompleteNote.classList.toggle('d-none', !isVietQr);
        }

        if (stepThreeTourLink) {
            stepThreeTourLink.classList.toggle('d-none', isVietQr);
        }

        if (stepThreeNote) {
            stepThreeNote.textContent = isVietQr
                ? <?= json_encode($t('checkout.vietqrTransferNote')) ?>
                : (isVnpay ? <?= json_encode($t('checkout.vnpayRedirectNote')) ?> : <?= json_encode($t('checkout.otherMethodNote')) ?>);
        }

        if (method === 'vietqr') {
            generateVietQr();
        } else {
            lastVietQrKey = '';
        }
    };

    const validateStepOne = function () {
        clearError();

        for (const field of summaryFields) {
            if (!field.hasAttribute('required')) {
                continue;
            }

            if (field.checkValidity()) {
                continue;
            }

            field.reportValidity();
            return false;
        }

        return true;
    };

    const validateStepTwo = function () {
        clearError();

        if (!termsCheckbox || termsCheckbox.checked) {
            return true;
        }

        setError(<?= json_encode($t('checkout.invalidTerms')) ?>);
        return false;
    };

    const startPayPalCheckout = async function () {
        clearError();

        if (!validateStepOne() || !validateStepTwo()) {
            return;
        }

        updateSummary();
        updatePaymentPlan();
        updatePaymentMethod();

        const selectedMethod = paymentMethodInputs.find(function (input) {
            return input.checked;
        });
        const selectedPlan = paymentPlanInputs.find(function (input) {
            return input.checked;
        });

        if (!selectedMethod || selectedMethod.value !== 'paypal') {
            setError(<?= json_encode($t('checkout.paypalOnly')) ?>);
            return;
        }

        if (paySubmitButtons.length === 0) {
            setError(<?= json_encode($t('checkout.paypalButtonMissing')) ?>);
            return;
        }

        setPayButtonsState(true, <?= json_encode($t('checkout.processingPaypal')) ?>);

        try {
            const formData = new FormData();
            appendCsrf(formData);
            formData.append('payment_method', selectedMethod.value);
            formData.append('payment_plan', selectedPlan ? selectedPlan.value : 'deposit');
            summaryFields.forEach(function (field) {
                formData.append(field.name, field.value.trim());
            });

            const response = await fetch(paySubmitButtons[0].dataset.paypalCreateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const payload = await response.json();

            if (!response.ok || !payload.ok || !payload.redirect) {
                throw new Error(payload.message || <?= json_encode($t('checkout.paypalCreateFailed')) ?>);
            }

            window.location.href = payload.redirect;
        } catch (error) {
            setError(error.message || <?= json_encode($t('checkout.paypalConnectFailed')) ?>);
            updatePaymentMethod();
        }
    };

    const startVnpayCheckout = async function () {
        clearError();

        if (!validateStepOne() || !validateStepTwo()) {
            return;
        }

        updateSummary();
        updatePaymentPlan();
        updatePaymentMethod();

        const selectedMethod = paymentMethodInputs.find(function (input) {
            return input.checked;
        });
        const selectedPlan = paymentPlanInputs.find(function (input) {
            return input.checked;
        });

        if (!selectedMethod || selectedMethod.value !== 'vnpay') {
            setError(<?= json_encode($t('checkout.vnpaySelectionInvalid')) ?>);
            return;
        }

        if (vnpayCreateUrl === '') {
            setError(<?= json_encode($t('checkout.vnpayConfigMissing')) ?>);
            return;
        }

        if (paySubmitButtons.length === 0) {
            setError(<?= json_encode($t('checkout.vnpayCreateFailed')) ?>);
            return;
        }

        setPayButtonsState(true, <?= json_encode($t('checkout.processingVnpay')) ?>);

        try {
            const formData = new FormData();
            appendCsrf(formData);
            formData.append('payment_method', selectedMethod.value);
            formData.append('payment_plan', selectedPlan ? selectedPlan.value : 'deposit');
            summaryFields.forEach(function (field) {
                formData.append(field.name, field.value.trim());
            });

            const response = await fetch(vnpayCreateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const payload = await response.json();

            if (!response.ok || !payload.ok || !payload.redirect) {
                throw new Error(payload.message || <?= json_encode($t('checkout.vnpayCreateFailed')) ?>);
            }

            window.location.href = payload.redirect;
        } catch (error) {
            setError(error.message || <?= json_encode($t('checkout.vnpayCreateFailed')) ?>);
            updatePaymentMethod();
        }
    };

    const completeVietQrCheckout = async function () {
        clearError();

        if (!validateStepOne() || !validateStepTwo()) {
            return;
        }

        if (vietQrCompleteUrl === '') {
            setError(<?= json_encode($t('checkout.vietqrConfigMissing')) ?>);
            return;
        }

        if (vietQrCompleteButton) {
            vietQrCompleteButton.setAttribute('disabled', 'disabled');
            vietQrCompleteButton.textContent = <?= json_encode($t('contact.submitting')) ?>;
        }

        try {
            const formData = new FormData();
            appendCsrf(formData);

            const response = await fetch(vietQrCompleteUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const payload = await response.json();

            if (!response.ok || !payload.ok || !payload.redirect) {
                throw new Error(payload.message || <?= json_encode($t('checkout.vietqrCompleteFailed')) ?>);
            }

            window.location.href = payload.redirect;
        } catch (error) {
            setError(error.message || <?= json_encode($t('checkout.vietqrCompleteFailed')) ?>);

            if (vietQrCompleteButton) {
                vietQrCompleteButton.removeAttribute('disabled');
                vietQrCompleteButton.textContent = <?= json_encode($t('checkout.completeTransfer')) ?>;
            }
        }
    };

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const targetStep = Number(tab.dataset.stepTarget);
            const selectedMethod = paymentMethodInputs.find(function (input) {
                return input.checked;
            });
            const method = selectedMethod ? selectedMethod.value : 'paypal';

            if (targetStep === 2 && !validateStepOne()) {
                return;
            }

            if (targetStep === 3) {
                if (method === 'paypal' || method === 'vnpay') {
                    return;
                }

                if (!validateStepOne() || !validateStepTwo()) {
                    return;
                }
            }

            updateSummary();
            updatePaymentPlan();
            updatePaymentMethod();
            setStep(targetStep);
        });
    });

    nextButtons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const nextStep = Number(button.dataset.stepNext);
            const selectedMethod = paymentMethodInputs.find(function (input) {
                return input.checked;
            });
            const method = selectedMethod ? selectedMethod.value : 'paypal';

            if (nextStep === 2 && !validateStepOne()) {
                return;
            }

            if (nextStep === 3) {
                if (!validateStepOne() || !validateStepTwo()) {
                    return;
                }

                if (method === 'paypal') {
                    await startPayPalCheckout();
                    return;
                }

                if (method === 'vnpay') {
                    await startVnpayCheckout();
                    return;
                }
            }

            updateSummary();
            updatePaymentPlan();
            updatePaymentMethod();
            setStep(nextStep);
        });
    });

    paySubmitButtons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const selectedMethod = paymentMethodInputs.find(function (input) {
                return input.checked;
            });
            const method = selectedMethod ? selectedMethod.value : 'paypal';

            if (!validateStepOne() || !validateStepTwo()) {
                return;
            }

            if (method === 'paypal') {
                await startPayPalCheckout();
                return;
            }

            if (method === 'vnpay') {
                await startVnpayCheckout();
                return;
            }

            updateSummary();
            updatePaymentPlan();
            updatePaymentMethod();
            setStep(3);
        });
    });

    prevButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            clearError();
            setStep(Number(button.dataset.stepPrev));
        });
    });

    if (vietQrCompleteButton) {
        vietQrCompleteButton.addEventListener('click', async function () {
            await completeVietQrCheckout();
        });
    }

    summaryFields.forEach(function (field) {
        field.addEventListener('input', updateSummary);
    });

    paymentPlanInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            updatePaymentPlan();
            lastVietQrKey = '';

            const selectedMethod = paymentMethodInputs.find(function (item) {
                return item.checked;
            });

            if (selectedMethod && selectedMethod.value === 'vietqr') {
                generateVietQr();
            }
        });
    });

    paymentMethodInputs.forEach(function (input) {
        input.addEventListener('change', updatePaymentMethod);
    });

    if (breakdownToggle && priceBreakdown) {
        breakdownToggle.addEventListener('click', function () {
            priceBreakdown.hidden = !priceBreakdown.hidden;
        });
    }

    if (couponApplyButton && couponInput) {
        couponApplyButton.addEventListener('click', async function () {
            await submitCouponCode(couponInput.value.trim());
        });
    }

    if (couponRemoveButton) {
        couponRemoveButton.addEventListener('click', async function () {
            await submitCouponCode('');
        });
    }

    if (couponInput) {
        couponInput.addEventListener('keydown', async function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            await submitCouponCode(couponInput.value.trim());
        });
    }

    if (termsCheckbox && bookingSummaryCard) {
        termsCheckbox.addEventListener('change', function () {
            if (!termsCheckbox.checked || !window.matchMedia('(max-width: 767px)').matches) {
                return;
            }

            window.setTimeout(function () {
                bookingSummaryCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 120);
        });
    }

    const retryButton = root.querySelector('[data-checkout-retry]');
    if (retryButton) {
        retryButton.addEventListener('click', function () {
            clearError();
            updateSummary();
            renderPricing();
            updatePaymentMethod();
            focusPaymentStep();
        });
    }

    updateSummary();
    renderPricing();
    renderCouponCurrent(<?= json_encode($couponCode !== '' ? ['code' => $couponCode, 'name' => $couponName] : null) ?>);
    updatePaymentMethod();
    setStep(<?= $checkoutRetry ? '2' : '1' ?>);

    if (<?= $checkoutRetry ? 'true' : 'false' ?>) {
        window.setTimeout(function () {
            focusPaymentStep();
        }, 120);
    }
});
</script>
<?= $this->endSection() ?>
