<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authUser = is_array($authUser ?? null) ? $authUser : null;
$booking = is_array($pendingBooking ?? null) ? $pendingBooking : [];
$checkoutMode = (string) ($checkoutMode ?? 'guest');
$adultQuantity = max(0, (int) ($booking['adult_quantity'] ?? 0));
$childQuantity = max(0, (int) ($booking['child_quantity'] ?? 0));
$infantQuantity = max(0, (int) ($booking['infant_quantity'] ?? 0));
$travelerCount = $adultQuantity + $childQuantity + $infantQuantity;
$grandTotal = (float) ($booking['grand_total'] ?? 0);
$depositRate = 0.10;
$depositAmount = $grandTotal * $depositRate;
$formatCurrency = static fn(float $amount): string => number_format($amount, 0, ',', '.') . ' VND';
?>
<div class="container pt-100 pb-100 checkout-stepper-page" data-checkout-stepper>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="package-details-warpper">
                <div class="section-title mb-30">
                    <h2>Checkout</h2>
                </div>

                <?php if ($authUser !== null): ?>
                    <div class="alert alert-success">
                        Signed in as <strong><?= esc($authUser['full_name'] ?: $authUser['email']) ?></strong>
                        (<a href="<?= localized_url('auth/logout') ?>">Logout</a>)
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">
                        Checkout mode: <strong><?= esc($checkoutMode === 'member' ? 'Member' : 'Guest') ?></strong>.
                    </div>
                <?php endif; ?>

                <div class="checkout-stepper-header mb-40">
                    <button type="button" class="checkout-stepper-tab is-active" data-step-target="1">
                        <span class="step-number">1</span>
                        <span class="step-label">Nhập thông tin</span>
                    </button>
                    <span class="step-line"></span>
                    <button type="button" class="checkout-stepper-tab" data-step-target="2">
                        <span class="step-number">2</span>
                        <span class="step-label">Thanh toán</span>
                    </button>
                    <span class="step-line"></span>
                    <button type="button" class="checkout-stepper-tab" data-step-target="3">
                        <span class="step-number">3</span>
                        <span class="step-label">Hoàn tất</span>
                    </button>
                </div>

                <form class="checkout-stepper-form" data-checkout-form novalidate>
                    <div class="checkout-stepper-pane is-active" data-step-pane="1">
                        <div class="contact-form-wrap">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-full-name">Họ và tên</label>
                                        <input
                                            type="text"
                                            id="checkout-full-name"
                                            name="full_name"
                                            value="<?= esc((string) ($authUser['full_name'] ?? '')) ?>"
                                            placeholder="Tên khách hàng"
                                            required
                                            data-summary-field="full_name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-email">Email</label>
                                        <input
                                            type="email"
                                            id="checkout-email"
                                            name="email"
                                            value="<?= esc((string) ($authUser['email'] ?? '')) ?>"
                                            placeholder="Email"
                                            required
                                            data-summary-field="email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-phone">Số điện thoại</label>
                                        <input
                                            type="text"
                                            id="checkout-phone"
                                            name="phone"
                                            placeholder="Số điện thoại"
                                            required
                                            data-summary-field="phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-inner">
                                        <label for="checkout-note">Ghi chú</label>
                                        <input
                                            type="text"
                                            id="checkout-note"
                                            name="note"
                                            placeholder="Yêu cầu thêm nếu có"
                                            data-summary-field="note">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1" data-step-next="2">Tiếp tục thanh toán</button>
                        </div>
                    </div>

                    <div class="checkout-stepper-pane" data-step-pane="2">
                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="checkout-stepper-card">
                                    <h5 class="checkout-card-title">Thông tin khách hàng</h5>
                                    <div class="checkout-info-grid">
                                        <div class="checkout-info-item">
                                            <span>Họ và tên</span>
                                            <strong data-summary-output="full_name">-</strong>
                                        </div>
                                        <div class="checkout-info-item">
                                            <span>Email</span>
                                            <strong data-summary-output="email">-</strong>
                                        </div>
                                        <div class="checkout-info-item">
                                            <span>Số điện thoại</span>
                                            <strong data-summary-output="phone">-</strong>
                                        </div>
                                        <div class="checkout-info-item">
                                            <span>Ghi chú</span>
                                            <strong data-summary-output="note">-</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="checkout-stepper-card">
                                    <div class="checkout-card-head">
                                        <h5 class="checkout-card-title">Tùy chọn thanh toán</h5>
                                        <button type="button" class="checkout-text-btn" data-price-breakdown-toggle>View Price Breakdown</button>
                                    </div>

                                    <div class="checkout-price-breakdown" data-price-breakdown hidden>
                                        <div class="price-breakdown-row">
                                            <span>Người lớn x <?= esc((string) $adultQuantity) ?></span>
                                            <strong><?= esc($formatCurrency((float) ($booking['adult_price'] ?? 0) * $adultQuantity)) ?></strong>
                                        </div>
                                        <div class="price-breakdown-row">
                                            <span>Trẻ em x <?= esc((string) $childQuantity) ?></span>
                                            <strong><?= esc($formatCurrency((float) ($booking['child_price'] ?? 0) * $childQuantity)) ?></strong>
                                        </div>
                                        <div class="price-breakdown-row">
                                            <span>Em bé x <?= esc((string) $infantQuantity) ?></span>
                                            <strong><?= esc($formatCurrency((float) ($booking['infant_price'] ?? 0) * $infantQuantity)) ?></strong>
                                        </div>
                                    </div>

                                    <div class="checkout-coupon-row">
                                        <label for="checkout-coupon">Coupon Code</label>
                                        <div class="checkout-coupon-input">
                                            <button type="button" class="checkout-text-btn" data-coupon-placeholder>Apply</button>
                                            <input type="text" id="checkout-coupon" placeholder="Coming soon">
                                        </div>
                                    </div>

                                    <div class="checkout-price-row">
                                        <span>Total Price</span>
                                        <strong><?= esc($formatCurrency($grandTotal)) ?></strong>
                                    </div>
                                    <div class="checkout-price-row">
                                        <span data-payment-plan-label>10% Deposit</span>
                                        <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                    </div>

                                    <div class="checkout-plan-options">
                                        <label class="checkout-plan-option">
                                            <input type="radio" name="payment_plan" value="full" data-payment-plan="full">
                                            <span>Thanh toán toàn bộ</span>
                                        </label>
                                        <label class="checkout-plan-option">
                                            <input type="radio" name="payment_plan" value="deposit" data-payment-plan="deposit" checked>
                                            <span>Đặt cọc 10%</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="checkout-stepper-card">
                                    <h5 class="checkout-card-title">Phương thức thanh toán</h5>
                                    <div class="checkout-payment-options">
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="paypal" checked>
                                            <span>PayPal</span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="momo">
                                            <span>MoMo</span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="zalopay">
                                            <span>ZaloPay</span>
                                        </label>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method" value="vietqr" data-vietqr-trigger>
                                            <span>VietQR</span>
                                        </label>
                                    </div>
                                    <div class="checkout-vietqr-box" data-vietqr-box hidden>
                                        <div class="checkout-vietqr-qr">QR</div>
                                        <div>
                                            <h6>VietQR</h6>
                                            <p>Hiển thị mã QR chuyển khoản tại đây khi ông nối cổng thanh toán thật.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="checkout-terms-wrap">
                                    <label class="checkout-terms-check">
                                        <input type="checkbox" name="agree_terms" data-agree-terms>
                                        <span>I agree with Terms of Service and Privacy Statement</span>
                                    </label>
                                    <p class="checkout-inline-error" data-step-error hidden></p>
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="checkout-stepper-card checkout-booking-summary">
                                    <h5 class="checkout-card-title"><?= esc((string) ($booking['tour_title'] ?? 'Tour booking')) ?></h5>
                                    <div class="checkout-summary-list">
                                        <div class="checkout-summary-item">
                                            <span>Travel Date</span>
                                            <strong><?= esc((string) ($booking['departure_label'] ?? '-')) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span>Period</span>
                                            <strong><?= esc((string) ($booking['duration_label'] ?? '-')) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span>Travelers</span>
                                            <strong><?= esc((string) $travelerCount) ?> người</strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span>Người lớn</span>
                                            <strong><?= esc((string) $adultQuantity) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span>Trẻ em</span>
                                            <strong><?= esc((string) $childQuantity) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item">
                                            <span>Em bé</span>
                                            <strong><?= esc((string) $infantQuantity) ?></strong>
                                        </div>
                                        <div class="checkout-summary-item total">
                                            <span>Số tiền cần thanh toán</span>
                                            <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1 transparent" data-step-prev="1">Quay lại</button>
                            <button type="button" class="primary-btn1" data-step-next="3">Hoàn tất</button>
                        </div>
                    </div>

                    <div class="checkout-stepper-pane" data-step-pane="3">
                        <div class="checkout-stepper-card">
                            <h5 class="checkout-card-title">Xác nhận thanh toán</h5>
                            <div class="checkout-finish-grid">
                                <div class="checkout-finish-item">
                                    <span>Khách hàng</span>
                                    <strong data-summary-output="full_name">-</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Email</span>
                                    <strong data-summary-output="email">-</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Số điện thoại</span>
                                    <strong data-summary-output="phone">-</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Tour</span>
                                    <strong><?= esc((string) ($booking['tour_title'] ?? '-')) ?></strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Phương thức</span>
                                    <strong data-payment-method-output>PayPal</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Gói thanh toán</span>
                                    <strong data-payment-plan-output>Đặt cọc 10%</strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Số tiền</span>
                                    <strong data-payment-amount><?= esc($formatCurrency($depositAmount)) ?></strong>
                                </div>
                                <div class="checkout-finish-item">
                                    <span>Ghi chú</span>
                                    <strong data-summary-output="note">-</strong>
                                </div>
                            </div>
                            <div class="checkout-finish-note">
                                Đây là bước hoàn tất UI. Khi ông nối cổng thanh toán thật, nút xác nhận cuối sẽ gọi backend tạo order và transaction.
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1 transparent" data-step-prev="2">Quay lại</button>
                            <a href="<?= esc((string) ($booking['tour_link'] ?? localized_url(''))) ?>" class="primary-btn1">Về trang tour</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="tour-sidebar">
                <div class="booking-form-wrap checkout-sidebar-card">
                    <div class="tour-date-wrap">
                        <h5>Thông tin booking</h5>
                        <div class="tour-date">
                            <span>Tên tour</span>
                            <h6><?= esc((string) ($booking['tour_title'] ?? '')) ?></h6>
                        </div>
                        <div class="tour-date">
                            <span>Khởi hành</span>
                            <h6><?= esc((string) ($booking['departure_label'] ?? '')) ?></h6>
                        </div>
                        <div class="tour-date">
                            <span>Thời lượng</span>
                            <h6><?= esc((string) ($booking['duration_label'] ?? '')) ?></h6>
                        </div>
                        <div class="tour-date">
                            <span>Người lớn</span>
                            <h6><?= esc((string) $adultQuantity) ?></h6>
                        </div>
                        <div class="tour-date">
                            <span>Trẻ em</span>
                            <h6><?= esc((string) $childQuantity) ?></h6>
                        </div>
                        <div class="tour-date">
                            <span>Em bé</span>
                            <h6><?= esc((string) $infantQuantity) ?></h6>
                        </div>
                        <div class="tour-date total">
                            <span>Tổng tiền</span>
                            <h6><?= esc($formatCurrency($grandTotal)) ?></h6>
                        </div>
                    </div>
                </div>
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
    const currency = new Intl.NumberFormat('vi-VN');
    const totals = {
        full: <?= json_encode($grandTotal) ?>,
        deposit: <?= json_encode($depositAmount) ?>
    };
    const planLabels = {
        full: 'Thanh toán toàn bộ',
        deposit: 'Đặt cọc 10%'
    };
    const planLineLabels = {
        full: 'Full Amount',
        deposit: '10% Deposit'
    };
    const paymentLabels = {
        paypal: 'PayPal',
        momo: 'MoMo',
        zalopay: 'ZaloPay',
        vietqr: 'VietQR'
    };

    const formatCurrency = function (amount) {
        return currency.format(amount) + ' VND';
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

    const updatePaymentMethod = function () {
        const selectedMethod = paymentMethodInputs.find(function (input) {
            return input.checked;
        });
        const method = selectedMethod ? selectedMethod.value : 'paypal';
        const label = paymentLabels[method] || method;

        paymentMethodOutputs.forEach(function (output) {
            output.textContent = label;
        });

        if (vietQrBox) {
            vietQrBox.hidden = method !== 'vietqr';
        }
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

        setError('Bạn cần đồng ý Terms of Service và Privacy Statement trước khi hoàn tất.');
        return false;
    };

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const targetStep = Number(tab.dataset.stepTarget);

            if (targetStep === 2 && !validateStepOne()) {
                return;
            }

            if (targetStep === 3) {
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
        button.addEventListener('click', function () {
            const nextStep = Number(button.dataset.stepNext);

            if (nextStep === 2 && !validateStepOne()) {
                return;
            }

            if (nextStep === 3) {
                if (!validateStepOne() || !validateStepTwo()) {
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

    summaryFields.forEach(function (field) {
        field.addEventListener('input', updateSummary);
    });

    paymentPlanInputs.forEach(function (input) {
        input.addEventListener('change', updatePaymentPlan);
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
            window.alert('Coupon code chưa được nối backend. Nên làm sau khi chốt pricing và rule giảm giá.');
        });
    }

    updateSummary();
    updatePaymentPlan();
    updatePaymentMethod();
    setStep(1);
});
</script>
<?= $this->endSection() ?>
