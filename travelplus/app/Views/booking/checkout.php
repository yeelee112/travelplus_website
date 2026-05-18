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
$depositRate = 0.10;
$depositAmount = $grandTotal * $depositRate;
$checkoutNotice = trim((string) ($checkoutNotice ?? ''));
$checkoutError = trim((string) ($checkoutError ?? ''));
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

$travelerSummary = $travelerCount . ' ' . $t('checkout.travelers');

if ($travelerParts !== []) {
    $travelerSummary .= ' (' . implode(', ', $travelerParts) . ')';
}
?>
<div class="container pt-100 pb-100 checkout-stepper-page" data-checkout-stepper>
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

                <?php if ($authUser !== null): ?>
                    <div class="alert alert-success">
                        <?= esc($t('checkout.signedInAs')) ?> <strong><?= esc($authUser['full_name'] ?: $authUser['email']) ?></strong>
                        (<a href="<?= \App\Data\LocalizedPathCatalog::url('auth.logout', $locale) ?>"><?= esc($t('auth.logout')) ?></a>)
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
                                <div class="checkout-stepper-card">
                                    <h5 class="checkout-card-title"><?= esc($t('checkout.customerInfo')) ?></h5>
                                    <div class="checkout-info-grid">
                                        <div class="checkout-info-item">
                                            <span><?= esc($t('checkout.fullName')) ?></span>
                                            <strong data-summary-output="full_name">-</strong>
                                        </div>
                                        <div class="checkout-info-item">
                                            <span><?= esc($t('checkout.email')) ?></span>
                                            <strong data-summary-output="email">-</strong>
                                        </div>
                                        <div class="checkout-info-item">
                                            <span><?= esc($t('checkout.phone')) ?></span>
                                            <strong data-summary-output="phone">-</strong>
                                        </div>
                                        <div class="checkout-info-item">
                                            <span><?= esc($t('checkout.note')) ?></span>
                                            <strong data-summary-output="note">-</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="checkout-stepper-card">
                                    <div class="checkout-card-head">
                                        <h5 class="checkout-card-title"><?= esc($t('checkout.paymentOptions')) ?></h5>
                                        <button type="button" class="checkout-text-btn" data-price-breakdown-toggle><?= esc($t('checkout.viewPriceBreakdown')) ?></button>
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
                                    </div>

                                    <div class="checkout-coupon-row">
                                        <label for="checkout-coupon"><?= esc($t('checkout.couponCode')) ?></label>
                                        <div class="checkout-coupon-input">
                                            <button type="button" class="checkout-text-btn" data-coupon-placeholder><?= esc($t('checkout.apply')) ?></button>
                                            <input type="text" id="checkout-coupon" placeholder="<?= esc($t('checkout.comingSoon')) ?>">
                                        </div>
                                    </div>

                                    <div class="checkout-price-row">
                                        <span><?= esc($t('checkout.total')) ?></span>
                                        <strong><?= esc($formatCurrency($grandTotal)) ?></strong>
                                    </div>
                                    <div class="checkout-price-row">
                                        <span data-payment-plan-label><?= esc($t('checkout.depositLine')) ?></span>
                                        <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                    </div>

                                    <div class="checkout-plan-options">
                                        <label class="checkout-plan-option">
                                            <input type="radio" name="payment_plan" value="full" data-payment-plan="full">
                                            <span><?= esc($t('checkout.payFull')) ?></span>
                                        </label>
                                        <label class="checkout-plan-option">
                                            <input type="radio" name="payment_plan" value="deposit" data-payment-plan="deposit" checked>
                                            <span><?= esc($t('checkout.payDeposit')) ?></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="checkout-stepper-card">
                                    <h5 class="checkout-card-title"><?= esc($t('checkout.paymentMethods')) ?></h5>
                                    <div class="checkout-payment-options">
                                        <label class="checkout-payment-option is-selected">
                                            <input type="radio" name="payment_method" value="paypal" checked>
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/Paypal-Logo.png')) ?>" alt="" class="checkout-payment-logo">
                                            </span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="momo">
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/MOMO-Logo-App.png')) ?>" alt="" class="checkout-payment-logo">
                                            </span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="zalopay">
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/ZaloPay-Logo.png')) ?>" alt="" class="checkout-payment-logo">
                                            </span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="vietqr">
                                            <span class="checkout-payment-logo-wrap" aria-hidden="true">
                                                <img src="<?= esc(base_url('assets/images/payments/VietQR-Logo.png')) ?>" alt="" class="checkout-payment-logo">
                                            </span>
                                        </label>
                                    </div>
                                    <div class="checkout-vietqr-box" data-vietqr-box data-vietqr-create-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.vietqrGenerate', $locale)) ?>" data-vietqr-complete-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.vietqrComplete', $locale)) ?>" hidden>
                                        <div class="checkout-vietqr-qr">
                                            <img src="" alt="VietQR" data-vietqr-image hidden>
                                            <span data-vietqr-placeholder>QR</span>
                                        </div>
                                        <div>
                                            <h6><?= esc($t('checkout.vietqrTitle')) ?></h6>
                                            <p data-vietqr-message><?= esc($t('checkout.vietqrHint')) ?></p>
                                            <div class="checkout-vietqr-meta">
                                                <div><strong><?= esc($t('checkout.vietqrAmount')) ?></strong> <span data-vietqr-amount>-</span></div>
                                                <div><strong><?= esc($t('checkout.vietqrContent')) ?></strong> <span data-vietqr-add-info>-</span></div>
                                                <div><strong><?= esc($t('checkout.vietqrAccountName')) ?></strong> <span data-vietqr-account-name>-</span></div>
                                                <div><strong><?= esc($t('checkout.vietqrAccountNo')) ?></strong> <span data-vietqr-account-no>-</span></div>
                                            </div>
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
                                <div class="checkout-stepper-card checkout-booking-summary">
                                    <img class="checkout-booking-image pb-10" src="<?= esc($booking['tour_image']) ?>" alt="Tour Image" >
                                    <h5 class="checkout-card-title"><?= esc((string) ($booking['tour_title'] ?? $t('checkout.bookingTitleFallback'))) ?></h5>
                                    <div class="checkout-summary-list">
                                        <div class="checkout-summary-item">
                                            <span><?= esc($t('checkout.travelDate')) ?></span>
                                            <strong><?= esc((string) ($booking['departure_label'] ?? '-')) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span><?= esc($t('checkout.period')) ?></span>
                                            <strong><?= esc((string) ($booking['duration_label'] ?? '-')) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span><?= esc($t('checkout.travelers')) ?></span>
                                            <strong><?= esc($travelerSummary) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item total">
                                            <span><?= esc($t('checkout.total')) ?></span>
                                            <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1 transparent" data-step-prev="1"><?= esc($t('checkout.back')) ?></button>
                            <button type="button" class="primary-btn1" data-step-next="3" data-paypal-submit data-paypal-create-url="<?= esc(\App\Data\LocalizedPathCatalog::url('booking.paypalCreateOrder', $locale)) ?>"><?= esc($t('checkout.step3')) ?></button>
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
                                    <span><?= esc($t('checkout.total')) ?></span>
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
    const paymentMethodOutputs = Array.from(root.querySelectorAll('[data-payment-method-output]'));
    const errorBox = root.querySelector('[data-step-error]');
    const termsCheckbox = root.querySelector('[data-agree-terms]');
    const vietQrBox = root.querySelector('[data-vietqr-box]');
    const priceBreakdown = root.querySelector('[data-price-breakdown]');
    const breakdownToggle = root.querySelector('[data-price-breakdown-toggle]');
    const couponPlaceholder = root.querySelector('[data-coupon-placeholder]');
    const paypalSubmitButton = root.querySelector('[data-step-pane="2"] [data-paypal-submit]');
    const vietQrCreateUrl = vietQrBox ? vietQrBox.dataset.vietqrCreateUrl : '';
    const vietQrCompleteUrl = vietQrBox ? vietQrBox.dataset.vietqrCompleteUrl : '';
    const vietQrCompleteButton = root.querySelector('[data-vietqr-complete]');
    const stepThreeNote = root.querySelector('[data-step-three-note]');
    const stepThreeTourLink = root.querySelector('[data-step-three-tour-link]');
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
    const defaultStepTwoLabel = paypalSubmitButton ? paypalSubmitButton.textContent.trim() : <?= json_encode($t('checkout.continuePayment')) ?>;
    const currency = new Intl.NumberFormat('vi-VN');
    const totals = {
        full: <?= json_encode($grandTotal) ?>,
        deposit: <?= json_encode($depositAmount) ?>
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
        momo: 'MoMo',
        zalopay: 'ZaloPay',
        vietqr: 'VietQR'
    };
    let lastVietQrKey = '';

    const formatCurrency = function (amount) {
        return currency.format(amount) + ' VND';
    };

    const clearError = function () {
        if (!errorBox) {
            return;
        }

        errorBox.hidden = true;
        errorBox.textContent = '';
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
        const amount = totals[plan] || 0;

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
            amount: formatCurrency(totals[plan] || 0),
            addInfo: '-',
            accountName: '-',
            accountNo: '-',
        });

        try {
            const formData = new FormData();
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
                amount: formatCurrency(totals[plan] || 0),
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
        const isVietQr = method === 'vietqr';

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

        if (paypalSubmitButton) {
            paypalSubmitButton.textContent = isPaypal ? <?= json_encode($t('checkout.payWithPaypal')) ?> : defaultStepTwoLabel;
        }

        if (stepThreeTab) {
            stepThreeTab.classList.toggle('d-none', isPaypal);
        }

        if (stepThreePane) {
            stepThreePane.classList.toggle('d-none', isPaypal);
        }

        if (stepThreeLine) {
            stepThreeLine.classList.toggle('d-none', isPaypal);
        }

        if (vietQrCompleteButton) {
            vietQrCompleteButton.classList.toggle('d-none', !isVietQr);
        }

        if (stepThreeTourLink) {
            stepThreeTourLink.classList.toggle('d-none', isVietQr);
        }

        if (stepThreeNote) {
            stepThreeNote.textContent = isVietQr
                ? <?= json_encode($t('checkout.vietqrTransferNote')) ?>
                : <?= json_encode($t('checkout.otherMethodNote')) ?>;
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

        if (!paypalSubmitButton) {
            setError(<?= json_encode($t('checkout.paypalButtonMissing')) ?>);
            return;
        }

        paypalSubmitButton.setAttribute('disabled', 'disabled');
        paypalSubmitButton.textContent = <?= json_encode($t('checkout.processingPaypal')) ?>;

        try {
            const formData = new FormData();
            formData.append('payment_method', selectedMethod.value);
            formData.append('payment_plan', selectedPlan ? selectedPlan.value : 'deposit');
            summaryFields.forEach(function (field) {
                formData.append(field.name, field.value.trim());
            });

            const response = await fetch(paypalSubmitButton.dataset.paypalCreateUrl, {
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
            paypalSubmitButton.removeAttribute('disabled');
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
            const response = await fetch(vietQrCompleteUrl, {
                method: 'POST',
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
                if (method === 'paypal') {
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
            }

            updateSummary();
            updatePaymentPlan();
            updatePaymentMethod();
            setStep(nextStep);
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

    if (couponPlaceholder) {
        couponPlaceholder.addEventListener('click', function () {
            window.alert(<?= json_encode($t('checkout.couponAlert')) ?>);
        });
    }

    updateSummary();
    updatePaymentPlan();
    updatePaymentMethod();
    setStep(1);
});
</script>
<?= $this->endSection() ?>
