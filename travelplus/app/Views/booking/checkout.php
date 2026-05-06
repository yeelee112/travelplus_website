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
$travelerParts = [];

if ($adultQuantity > 0) {
    $travelerParts[] = $adultQuantity . ' người lớn';
}

if ($childQuantity > 0) {
    $travelerParts[] = $childQuantity . ' trẻ em';
}

if ($infantQuantity > 0) {
    $travelerParts[] = $infantQuantity . ' em bé';
}

$travelerSummary = $travelerCount . ' người';

if ($travelerParts !== []) {
    $travelerSummary .= ' (' . implode(', ', $travelerParts) . ')';
}
?>
<div class="container pt-100 pb-100 checkout-stepper-page" data-checkout-stepper>
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="package-details-warpper">
                <div class="section-title mb-30">
                    <h2>Checkout</h2>
                </div>

                <?php if ($checkoutNotice !== ''): ?>
                    <div class="alert alert-success"><?= esc($checkoutNotice) ?></div>
                <?php endif; ?>

                <?php if ($checkoutError !== ''): ?>
                    <div class="alert alert-danger"><?= esc($checkoutError) ?></div>
                <?php endif; ?>

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
                                        <span>Tổng tiền</span>
                                        <strong><?= esc($formatCurrency($grandTotal)) ?></strong>
                                    </div>
                                    <div class="checkout-price-row">
                                        <span data-payment-plan-label>Trả trước 10%</span>
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
                                    <div class="checkout-vietqr-box" data-vietqr-box data-vietqr-create-url="<?= esc(localized_url('booking/vietqr/generate')) ?>" data-vietqr-complete-url="<?= esc(localized_url('booking/vietqr/complete')) ?>" hidden>
                                        <div class="checkout-vietqr-qr">
                                            <img src="" alt="VietQR" data-vietqr-image hidden>
                                            <span data-vietqr-placeholder>QR</span>
                                        </div>
                                        <div>
                                            <h6>VietQR</h6>
                                            <p data-vietqr-message>Quét mã để chuyển khoản theo đúng số tiền và nội dung.</p>
                                            <div class="checkout-vietqr-meta">
                                                <div><strong>Số tiền:</strong> <span data-vietqr-amount>-</span></div>
                                                <div><strong>Nội dung:</strong> <span data-vietqr-add-info>-</span></div>
                                                <div><strong>Tài khoản:</strong> <span data-vietqr-account-name>-</span></div>
                                                <div><strong>Số tài khoản:</strong> <span data-vietqr-account-no>-</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="checkout-terms-wrap">
                                    <label class="checkout-terms-check">
                                        <input type="checkbox" name="agree_terms" data-agree-terms>
                                        <span>
                                            Tôi đồng ý với
                                            <a href="<?= esc(localized_url($locale === 'en' ? 'terms-of-service' : 'dieu-khoan-su-dung')) ?>" target="_blank" rel="noopener noreferrer">Điều khoản sử dụng</a>
                                            &
                                            <a href="<?= esc(localized_url($locale === 'en' ? 'privacy-statement' : 'chinh-sach-bao-mat')) ?>" target="_blank" rel="noopener noreferrer">Chính sách bảo mật</a>
                                        </span>
                                    </label>
                                    <p class="checkout-inline-error" data-step-error hidden></p>
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="checkout-stepper-card checkout-booking-summary">
                                    <img class="checkout-booking-image pb-10" src="<?= esc($booking['tour_image']) ?>" alt="Tour Image" >
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
                                            <strong><?= esc($travelerSummary) ?></strong>
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
                            <button type="button" class="primary-btn1" data-step-next="3" data-paypal-submit data-paypal-create-url="<?= esc(localized_url('booking/paypal/create-order')) ?>">Hoàn tất</button>
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
                            <div class="checkout-finish-note" data-step-three-note>
                                Với VietQR, sau khi chuyển khoản xong hãy bấm xác nhận để hệ thống ghi nhận booking và chuyển sang trang hoàn tất.
                            </div>
                        </div>

                        <div class="checkout-stepper-actions">
                            <button type="button" class="primary-btn1 transparent" data-step-prev="2">Quay lại</button>
                            <button type="button" class="primary-btn1" data-vietqr-complete>Xác nhận đã chuyển khoản</button>
                            <a href="<?= esc((string) ($booking['tour_link'] ?? localized_url(''))) ?>" class="primary-btn1 d-none" data-step-three-tour-link>Về trang tour</a>
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
    const defaultStepTwoLabel = paypalSubmitButton ? paypalSubmitButton.textContent.trim() : 'Tiếp tục';
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
            paypalSubmitButton.textContent = isPaypal ? 'Thanh toán với PayPal' : defaultStepTwoLabel;
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
                ? 'Với VietQR, sau khi chuyển khoản xong hãy bấm xác nhận để hệ thống ghi nhận booking và chuyển sang trang hoàn tất.'
                : 'Phương thức này chưa được nối thanh toán tự động. Có thể quay lại chọn PayPal hoặc VietQR để test flow hoàn tất.';
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

        setError('Bạn cần đồng ý Terms of Service và Privacy Statement trước khi hoàn tất.');
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
            setError('Hiện tại mới nối PayPal sandbox. Hãy chọn PayPal để test.');
            return;
        }

        if (!paypalSubmitButton) {
            setError('Không tìm thấy nút thanh toán PayPal.');
            return;
        }

        paypalSubmitButton.setAttribute('disabled', 'disabled');
        paypalSubmitButton.textContent = 'Đang chuyển sang PayPal...';

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
                throw new Error(payload.message || 'Không thể tạo giao dịch PayPal.');
            }

            window.location.href = payload.redirect;
        } catch (error) {
            setError(error.message || 'Không thể kết nối PayPal sandbox.');
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
            setError('Không tìm thấy cấu hình hoàn tất VietQR.');
            return;
        }

        if (vietQrCompleteButton) {
            vietQrCompleteButton.setAttribute('disabled', 'disabled');
            vietQrCompleteButton.textContent = 'Đang ghi nhận...';
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
                throw new Error(payload.message || 'Không thể hoàn tất booking VietQR.');
            }

            window.location.href = payload.redirect;
        } catch (error) {
            setError(error.message || 'Không thể hoàn tất booking VietQR.');

            if (vietQrCompleteButton) {
                vietQrCompleteButton.removeAttribute('disabled');
                vietQrCompleteButton.textContent = 'Xác nhận đã chuyển khoản';
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
