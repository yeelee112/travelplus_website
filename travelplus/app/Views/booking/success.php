<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$booking = is_array($booking ?? null) ? $booking : [];
$locale = service('request')->getLocale();
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
$formatCurrency = static fn(float $amount): string => number_format($amount, 0, ',', '.') . ' VND';
$adultQuantity = max(0, (int) ($booking['adult_quantity'] ?? 0));
$childQuantity = max(0, (int) ($booking['child_quantity'] ?? 0));
$infantQuantity = max(0, (int) ($booking['infant_quantity'] ?? 0));
$travelerCount = $adultQuantity + $childQuantity + $infantQuantity;
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

$travelerSummary = $travelerCount > 0
    ? implode(', ', $travelerParts)
    : '-';
$paymentStatus = strtolower((string) ($booking['payment_status'] ?? ''));
$paymentMethod = strtolower((string) ($booking['payment_method'] ?? ''));
$paymentMethodLabel = match ($paymentMethod) {
    'paypal' => 'PayPal',
    'vnpay' => 'VNPAY',
    'vnpay_card' => 'Credit / debit card (VNPAY)',
    'vietqr' => 'VietQR',
    'momo' => 'MoMo',
    'zalopay' => 'ZaloPay',
    default => strtoupper((string) ($booking['payment_method'] ?? '-')),
};
$paymentPlanLabel = (string) ($booking['payment_plan'] ?? 'deposit') === 'full'
    ? $t('bookingSuccess.planFull')
    : $t('bookingSuccess.planDeposit');
$successTitle = $paymentStatus === 'pending_transfer' ? $t('bookingSuccess.titlePending') : $t('bookingSuccess.titlePaid');
$successMessage = $paymentStatus === 'pending_transfer'
    ? $t('bookingSuccess.messagePending')
    : $t('bookingSuccess.messagePaid');
$amountLabel = $paymentStatus === 'pending_transfer' ? $t('bookingSuccess.amountPending') : $t('bookingSuccess.amountPaid');
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
                        <span><?= esc($t('bookingSuccess.bookingCode')) ?></span>
                        <strong><?= esc((string) ($booking['booking_code'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.customer')) ?></span>
                        <strong><?= esc((string) ($booking['customer_name'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.email')) ?></span>
                        <strong><?= esc((string) ($booking['customer_email'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.phone')) ?></span>
                        <strong><?= esc((string) ($booking['customer_phone'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.tour')) ?></span>
                        <strong><?= esc((string) ($booking['tour_title'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.departure')) ?></span>
                        <strong><?= esc((string) ($booking['departure_label'] ?? '-')) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.travelers')) ?></span>
                        <strong><?= esc($travelerSummary) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.paymentMethod')) ?></span>
                        <strong><?= esc($paymentMethodLabel) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($t('bookingSuccess.paymentPlan')) ?></span>
                        <strong><?= esc($paymentPlanLabel) ?></strong>
                    </div>
                    <div class="checkout-finish-item">
                        <span><?= esc($amountLabel) ?></span>
                        <strong><?= esc($formatCurrency((float) ($paymentStatus === 'pending_transfer' ? ($booking['amount_due_vnd'] ?? 0) : ($booking['amount_paid_vnd'] ?? 0)))) ?></strong>
                    </div>
                </div>

                <div class="checkout-finish-note">
                    <?= esc($t('bookingSuccess.note')) ?>
                </div>

                <div class="checkout-stepper-actions mt-4">
                    <a href="<?= esc((string) ($booking['tour_link'] ?? localized_url(''))) ?>" class="primary-btn1"><?= esc($t('bookingSuccess.backTour')) ?></a>
                    <a href="<?= esc(localized_url('')) ?>" class="primary-btn1 transparent"><?= esc($t('bookingSuccess.backHome')) ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
