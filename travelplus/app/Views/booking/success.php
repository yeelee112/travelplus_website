<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

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

$travelerSummary = $travelerCount > 0 ? implode(', ', $travelerParts) : '-';
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
$isPendingTransfer = $paymentStatus === 'pending_transfer';
$isPaid = $paymentStatus === 'paid';
$isRejected = in_array($paymentStatus, ['cancelled', 'failed', 'rejected'], true);
$paymentPlanLabel = (string) ($booking['payment_plan'] ?? 'deposit') === 'full'
    ? $t('bookingSuccess.planFull')
    : $t('bookingSuccess.planDeposit');
$successTitle = match (true) {
    $isPaid => $t('bookingSuccess.titlePaid'),
    $isRejected => $locale === 'en' ? 'Booking not completed' : 'Booking chưa hoàn tất',
    default => $t('bookingSuccess.titlePending'),
};
$successMessage = match (true) {
    $isPaid => $t('bookingSuccess.messagePaid'),
    $isRejected => $locale === 'en'
        ? 'This booking was cancelled or the payment was not successful. Please contact Travel Plus if you need support.'
        : 'Booking này đã bị hủy hoặc thanh toán không thành công. Vui lòng liên hệ Travel Plus nếu cần kiểm tra lại.',
    $isPendingTransfer => $t('bookingSuccess.messagePending'),
    default => $locale === 'en'
        ? 'Your booking has been recorded. The system is waiting for payment confirmation.'
        : 'Booking của bạn đã được ghi nhận. Hệ thống đang chờ xác nhận thanh toán.',
};
$amountLabel = match (true) {
    $isPaid => $t('bookingSuccess.amountPaid'),
    $isRejected => $locale === 'en' ? 'Booking value' : 'Giá trị booking',
    default => $t('bookingSuccess.amountPending'),
};
$amountValue = $formatCurrency((float) ($isPaid ? ($booking['amount_paid_vnd'] ?? 0) : ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0)));
$bookingCode = (string) ($booking['booking_code'] ?? '-');
$tourTitle = (string) ($booking['tour_title'] ?? '-');
$departureLabel = (string) ($booking['departure_label'] ?? '-');
$departureFromLabel = trim((string) ($departureFrom ?? ''));
$departureFromLabel = $departureFromLabel !== '' ? $departureFromLabel : ($locale === 'en' ? 'To be confirmed' : 'Đang cập nhật');
$tourLink = (string) ($booking['tour_link'] ?? localized_url(''));
$homeLink = localized_url('');
$contactLink = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$createdAtLabel = app_datetime((string) ($booking['created_at'] ?? ''), 'd/m/Y H:i', '-');
$statusTone = match (true) {
    $isPaid => 'paid',
    $isRejected => 'rejected',
    default => 'pending',
};
$statusIcon = match ($statusTone) {
    'paid' => 'bi-check2-circle',
    'rejected' => 'bi-x-circle',
    default => 'bi-hourglass-split',
};
$statusLabel = match ($statusTone) {
    'paid' => $locale === 'en' ? 'Confirmed' : 'Đã xác nhận',
    'rejected' => $locale === 'en' ? 'Not completed' : 'Chưa hoàn tất',
    default => $isPendingTransfer
        ? ($locale === 'en' ? 'Awaiting reconciliation' : 'Chờ đối soát')
        : ($locale === 'en' ? 'Awaiting confirmation' : 'Chờ xác nhận'),
};
$nextSteps = match ($statusTone) {
    'paid' => $locale === 'en'
        ? ['Travel Plus has recorded your booking and payment.', 'Our team can contact you to confirm service details when needed.', 'Keep this page or booking code for later reference.']
        : ['Travel Plus đã ghi nhận booking và thanh toán của bạn.', 'Đội ngũ Travel Plus có thể liên hệ để xác nhận thêm thông tin dịch vụ khi cần.', 'Hãy lưu lại trang này hoặc mã booking để tra cứu sau.'],
    'rejected' => $locale === 'en'
        ? ['Review the payment or cancellation status.', 'Contact Travel Plus if you want to restore or create a new booking.', 'Use this booking code when requesting support.']
        : ['Kiểm tra lại trạng thái thanh toán hoặc hủy booking.', 'Liên hệ Travel Plus nếu bạn muốn khôi phục hoặc tạo booking mới.', 'Dùng mã booking này khi cần hỗ trợ.'],
    default => $isPendingTransfer
        ? ($locale === 'en'
            ? ['Keep the exact transfer content for reconciliation.', 'Travel Plus will verify the payment and contact you if more information is needed.', 'Use this booking code when requesting support.']
            : ['Giữ đúng nội dung chuyển khoản để Travel Plus đối soát.', 'Travel Plus sẽ kiểm tra thanh toán và liên hệ nếu cần thêm thông tin.', 'Dùng mã booking này khi cần hỗ trợ.'])
        : ($locale === 'en'
            ? ['Complete payment if you have not done so.', 'Travel Plus will update the booking status after confirmation.', 'Use this booking code when requesting support.']
            : ['Hoàn tất thanh toán nếu bạn chưa thực hiện.', 'Travel Plus sẽ cập nhật trạng thái booking sau khi xác nhận.', 'Dùng mã booking này khi cần hỗ trợ.']),
};
$tripItems = [
    ['icon' => 'bi-airplane', 'label' => $locale === 'en' ? 'Departure from' : 'Bay từ', 'value' => $departureFromLabel],
    ['icon' => 'bi-calendar-check', 'label' => $t('bookingSuccess.departure'), 'value' => $departureLabel],
    ['icon' => 'bi-people', 'label' => $t('bookingSuccess.travelers'), 'value' => $travelerSummary],
    ['icon' => 'bi-clock-history', 'label' => $locale === 'en' ? 'Created at' : 'Ngày tạo', 'value' => $createdAtLabel],
];
$customerItems = [
    ['label' => $t('bookingSuccess.customer'), 'value' => (string) ($booking['customer_name'] ?? '-')],
    ['label' => $t('bookingSuccess.email'), 'value' => (string) ($booking['customer_email'] ?? '-')],
    ['label' => $t('bookingSuccess.phone'), 'value' => (string) ($booking['customer_phone'] ?? '-')],
];
$paymentItems = [
    ['label' => $t('bookingSuccess.paymentMethod'), 'value' => $paymentMethodLabel],
    ['label' => $t('bookingSuccess.paymentPlan'), 'value' => $paymentPlanLabel],
    ['label' => $amountLabel, 'value' => $amountValue],
];
?>
<section class="travelplus-booking-success travelplus-booking-success--<?= esc($statusTone, 'attr') ?>">
    <div class="container">
        <div class="travelplus-booking-success__hero">
            <span class="travelplus-booking-success__icon">
                <i class="bi <?= esc($statusIcon, 'attr') ?>" aria-hidden="true"></i>
            </span>
            <div class="travelplus-booking-success__copy">
                <span><?= esc($statusLabel) ?></span>
                <h1><?= esc($successTitle) ?></h1>
                <p><?= esc($successMessage) ?></p>
            </div>
            <div class="travelplus-booking-success__code" aria-label="<?= esc($t('bookingSuccess.bookingCode'), 'attr') ?>">
                <span><?= esc($t('bookingSuccess.bookingCode')) ?></span>
                <strong><?= esc($bookingCode) ?></strong>
            </div>
        </div>

        <div class="travelplus-booking-success__layout">
            <article class="travelplus-booking-success__panel travelplus-booking-success__trip">
                <div class="travelplus-booking-success__panel-head">
                    <span><?= esc($locale === 'en' ? 'Trip summary' : 'Tóm tắt chuyến đi') ?></span>
                    <h2><?= esc($tourTitle) ?></h2>
                </div>
                <div class="travelplus-booking-success__info-grid">
                    <?php foreach ($tripItems as $item): ?>
                        <div class="travelplus-booking-success__info-item">
                            <i class="bi <?= esc($item['icon'], 'attr') ?>" aria-hidden="true"></i>
                            <span><?= esc($item['label']) ?></span>
                            <strong><?= esc($item['value']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <aside class="travelplus-booking-success__amount-card">
                <span><?= esc($amountLabel) ?></span>
                <strong><?= esc($amountValue) ?></strong>
                <p><?= esc($paymentPlanLabel) ?> · <?= esc($paymentMethodLabel) ?></p>
            </aside>
        </div>

        <div class="travelplus-booking-success__detail-grid">
            <section class="travelplus-booking-success__panel">
                <h2><?= esc($locale === 'en' ? 'Customer information' : 'Thông tin khách hàng') ?></h2>
                <dl class="travelplus-booking-success__list">
                    <?php foreach ($customerItems as $item): ?>
                        <div>
                            <dt><?= esc($item['label']) ?></dt>
                            <dd><?= esc($item['value']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </section>

            <section class="travelplus-booking-success__panel">
                <h2><?= esc($locale === 'en' ? 'Payment information' : 'Thông tin thanh toán') ?></h2>
                <dl class="travelplus-booking-success__list">
                    <?php foreach ($paymentItems as $item): ?>
                        <div>
                            <dt><?= esc($item['label']) ?></dt>
                            <dd><?= esc($item['value']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </section>
        </div>

        <section class="travelplus-booking-success__next">
            <div>
                <span><?= esc($locale === 'en' ? 'Next steps' : 'Bước tiếp theo') ?></span>
                <h2><?= esc($locale === 'en' ? 'What happens after booking?' : 'Sau khi đặt tour sẽ thế nào?') ?></h2>
            </div>
            <ol>
                <?php foreach ($nextSteps as $index => $step): ?>
                    <li>
                        <strong><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></strong>
                        <span><?= esc($step) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>

        <div class="travelplus-booking-success__note">
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <p><?= esc($t('bookingSuccess.note')) ?></p>
        </div>

        <div class="travelplus-booking-success__actions">
            <a href="<?= esc($tourLink) ?>" class="primary-btn1 two">
                <span><?= esc($t('bookingSuccess.backTour')) ?><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                <span><?= esc($t('bookingSuccess.backTour')) ?><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
            </a>
            <a href="<?= esc($contactLink) ?>" class="primary-btn1 two transparent">
                <span><?= esc($locale === 'en' ? 'Contact support' : 'Liên hệ hỗ trợ') ?><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                <span><?= esc($locale === 'en' ? 'Contact support' : 'Liên hệ hỗ trợ') ?><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
            </a>
            <a href="<?= esc($homeLink) ?>" class="travelplus-booking-success__home-link">
                <?= esc($t('bookingSuccess.backHome')) ?>
            </a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
