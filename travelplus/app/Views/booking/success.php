<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$booking = is_array($booking ?? null) ? $booking : [];
$formatCurrency = static fn(float $amount): string => number_format($amount, 0, ',', '.') . ' VND';
$travelerCount = (int) ($booking['adult_quantity'] ?? 0) + (int) ($booking['child_quantity'] ?? 0) + (int) ($booking['infant_quantity'] ?? 0);
$paymentStatus = strtolower((string) ($booking['payment_status'] ?? ''));
$paymentMethod = strtolower((string) ($booking['payment_method'] ?? ''));
$paymentMethodLabel = match ($paymentMethod) {
    'paypal' => 'PayPal',
    'vietqr' => 'VietQR',
    'momo' => 'MoMo',
    'zalopay' => 'ZaloPay',
    default => strtoupper((string) ($booking['payment_method'] ?? '-')),
};
$paymentPlanLabel = (string) ($booking['payment_plan'] ?? 'deposit') === 'full'
    ? 'Thanh toán toàn bộ'
    : 'Đặt cọc 10%';
$successTitle = $paymentStatus === 'pending_transfer' ? 'Đã ghi nhận booking' : 'Thanh toán thành công';
$successMessage = $paymentStatus === 'pending_transfer'
    ? 'Booking của bạn đã được ghi nhận. Hệ thống đang chờ đối soát chuyển khoản VietQR.'
    : 'Booking của bạn đã được ghi nhận và thanh toán thành công.';
$amountLabel = $paymentStatus === 'pending_transfer' ? 'Cần đối soát' : 'Đã thanh toán';
?>
<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="checkout-stepper-card">
                <div class="section-title mb-30">
                    <h2><?= esc($successTitle) ?></h2>
                    <p><?= esc($successMessage) ?></p>
                </div>

                <div class="checkout-finish-grid">
                    <div class="checkout-finish-item">
                        <span>Mã booking</span>
                        <strong><?= esc((string) ($booking['booking_code'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Khách hàng</span>
                        <strong><?= esc((string) ($booking['customer_name'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Email</span>
                        <strong><?= esc((string) ($booking['customer_email'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Số điện thoại</span>
                        <strong><?= esc((string) ($booking['customer_phone'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Tour</span>
                        <strong><?= esc((string) ($booking['tour_title'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Ngày khởi hành</span>
                        <strong><?= esc((string) ($booking['departure_label'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Số khách</span>
                        <strong><?= esc((string) $travelerCount) ?> người</strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Phương thức</span>
                        <strong><?= esc($paymentMethodLabel) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span>Gói thanh toán</span>
                        <strong><?= esc($paymentPlanLabel) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($amountLabel) ?></span>
                        <strong><?= esc($formatCurrency((float) ($paymentStatus === 'pending_transfer' ? ($booking['amount_due_vnd'] ?? 0) : ($booking['amount_paid_vnd'] ?? 0)))) ?></strong>
                    </div>
                </div>

                <div class="checkout-finish-note">
                    Mã booking này đã được lưu trong hệ thống. Có thể dùng để đối soát giao dịch và xử lý đơn sau thanh toán.
                     Quý khách có thể lưu lại đường link nay để xem lại thông tin booking hoặc liên hệ cho Travel Plus nếu cần hỗ trợ thêm.
                </div>

                <div class="checkout-stepper-actions mt-4">
                    <a href="<?= esc((string) ($booking['tour_link'] ?? localized_url(''))) ?>" class="primary-btn1">Về trang tour</a>
                    <a href="<?= esc(localized_url('')) ?>" class="primary-btn1 transparent">Về trang chủ</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
