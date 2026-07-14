<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$bookings = is_array($bookings ?? null)
    ? array_values(array_filter($bookings, static fn($item): bool => is_array($item)))
    : [];
$booking = count($bookings) === 1 ? $bookings[0] : null;
$submitted = (bool) ($submitted ?? false);
$error = trim((string) ($error ?? ''));
$lookupAction = (string) ($lookupAction ?? \App\Data\LocalizedPathCatalog::url('booking.lookup', $locale));
$bookingCodeInput = (string) ($bookingCode ?? '');
$contactInput = (string) ($contact ?? '');
$contactLink = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$homeLink = localized_url('');
$formatCurrency = static fn(float $amount): string => number_format($amount, 0, ',', '.') . ' VND';
$formatDateTime = static function (?string $value): string {
    $timestamp = strtotime((string) $value);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
};
$labels = $locale === 'en'
    ? [
        'metaTitle' => 'Booking lookup | Travel Plus',
        'metaDesc' => 'Look up your Travel Plus booking status with your booking code and contact information.',
        'eyebrow' => 'Booking support',
        'title' => 'Look up your booking',
        'desc' => 'Enter a booking code, email, or phone number used at checkout to review payment status and trip details.',
        'code' => 'Booking code',
        'codePlaceholder' => 'Example: BK260714ABC123',
        'contact' => 'Email or phone number',
        'contactPlaceholder' => 'Email or phone used for booking',
        'submit' => 'Look up booking',
        'notSubmittedTitle' => 'Use any detail you remember',
        'notSubmittedDesc' => 'A booking code is fastest, but email or phone number can also find recent bookings linked to that contact.',
        'errorTitle' => 'Booking not found',
        'resultsTitle' => 'Matching bookings',
        'resultsDesc' => 'Choose the booking you want to review from the matching results below.',
        'status' => 'Status',
        'trip' => 'Trip summary',
        'payment' => 'Payment summary',
        'customer' => 'Customer details',
        'tour' => 'Tour',
        'departure' => 'Departure date',
        'duration' => 'Duration',
        'travelers' => 'Travelers',
        'createdAt' => 'Created at',
        'customerName' => 'Customer',
        'email' => 'Email',
        'phone' => 'Phone number',
        'method' => 'Payment method',
        'plan' => 'Payment plan',
        'amountDue' => 'Amount due',
        'amountPaid' => 'Amount paid',
        'total' => 'Booking total',
        'backTour' => 'Back to tour',
        'contactSupport' => 'Contact support',
        'backHome' => 'Back to home',
        'nextTitle' => 'What should you do next?',
        'note' => 'For convenience, this page can search by booking code, email, or phone number. Contact support if you need changes to customer information.',
        'adult' => 'adult',
        'child' => 'child',
        'infant' => 'infant',
        'planFull' => 'Pay in full',
        'planDeposit' => '10% deposit',
    ]
    : [
        'metaTitle' => 'Tra cứu booking | Travel Plus',
        'metaDesc' => 'Tra cứu trạng thái booking Travel Plus bằng mã booking và thông tin liên hệ.',
        'eyebrow' => 'Hỗ trợ booking',
        'title' => 'Tra cứu booking',
        'desc' => 'Nhập mã booking, email hoặc số điện thoại đã dùng khi thanh toán để xem trạng thái thanh toán và thông tin chuyến đi.',
        'code' => 'Mã booking',
        'codePlaceholder' => 'Ví dụ: BK260714ABC123',
        'contact' => 'Email hoặc số điện thoại',
        'contactPlaceholder' => 'Email hoặc số điện thoại đặt tour',
        'submit' => 'Tra cứu booking',
        'notSubmittedTitle' => 'Dùng thông tin bạn còn nhớ',
        'notSubmittedDesc' => 'Mã booking là nhanh nhất, nhưng email hoặc số điện thoại cũng có thể tìm các booking gần đây gắn với thông tin đó.',
        'errorTitle' => 'Không tìm thấy booking',
        'resultsTitle' => 'Booking phù hợp',
        'resultsDesc' => 'Chọn booking bạn muốn kiểm tra trong danh sách kết quả bên dưới.',
        'status' => 'Trạng thái',
        'trip' => 'Tóm tắt chuyến đi',
        'payment' => 'Tóm tắt thanh toán',
        'customer' => 'Thông tin khách hàng',
        'tour' => 'Tour',
        'departure' => 'Ngày khởi hành',
        'duration' => 'Thời lượng',
        'travelers' => 'Số khách',
        'createdAt' => 'Ngày tạo',
        'customerName' => 'Khách hàng',
        'email' => 'Email',
        'phone' => 'Số điện thoại',
        'method' => 'Phương thức',
        'plan' => 'Gói thanh toán',
        'amountDue' => 'Cần thanh toán/đối soát',
        'amountPaid' => 'Đã thanh toán',
        'total' => 'Tổng giá trị booking',
        'backTour' => 'Về trang tour',
        'contactSupport' => 'Liên hệ hỗ trợ',
        'backHome' => 'Về trang chủ',
        'nextTitle' => 'Bạn nên làm gì tiếp theo?',
        'note' => 'Để tiện tra cứu, trang này có thể tìm bằng mã booking, email hoặc số điện thoại. Liên hệ hỗ trợ nếu cần điều chỉnh thông tin khách hàng.',
        'adult' => 'người lớn',
        'child' => 'trẻ em',
        'infant' => 'em bé',
        'planFull' => 'Thanh toán toàn bộ',
        'planDeposit' => 'Đặt cọc 10%',
    ];

$meta_title = $labels['metaTitle'];
$meta_desc = $labels['metaDesc'];

$statusTone = 'empty';
$statusLabel = '';
$statusMessage = '';
$statusIcon = 'bi-search';
$amountLabel = $labels['amountDue'];
$amountValue = '';
$tripItems = [];
$customerItems = [];
$paymentItems = [];
$nextSteps = [];
$tourLink = $homeLink;

if ($booking !== null) {
    $adultQuantity = max(0, (int) ($booking['adult_quantity'] ?? 0));
    $childQuantity = max(0, (int) ($booking['child_quantity'] ?? 0));
    $infantQuantity = max(0, (int) ($booking['infant_quantity'] ?? 0));
    $travelerParts = [];

    if ($adultQuantity > 0) {
        $travelerParts[] = $adultQuantity . ' ' . $labels['adult'];
    }

    if ($childQuantity > 0) {
        $travelerParts[] = $childQuantity . ' ' . $labels['child'];
    }

    if ($infantQuantity > 0) {
        $travelerParts[] = $infantQuantity . ' ' . $labels['infant'];
    }

    $paymentStatus = strtolower((string) ($booking['payment_status'] ?? ''));
    $paymentMethod = strtolower((string) ($booking['payment_method'] ?? ''));
    $paymentMethodLabel = match ($paymentMethod) {
        'paypal' => 'PayPal',
        'vnpay', 'vnpay_card' => 'VNPAY',
        'vietqr' => 'VietQR',
        'momo' => 'MoMo',
        'zalopay' => 'ZaloPay',
        default => strtoupper((string) ($booking['payment_method'] ?? '-')),
    };
    $paymentPlanLabel = (string) ($booking['payment_plan'] ?? 'deposit') === 'full'
        ? $labels['planFull']
        : $labels['planDeposit'];
    $statusTone = match (true) {
        $paymentStatus === 'paid' => 'paid',
        in_array($paymentStatus, ['cancelled', 'failed', 'rejected'], true) => 'rejected',
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
        default => $paymentStatus === 'pending_transfer'
            ? ($locale === 'en' ? 'Awaiting reconciliation' : 'Chờ đối soát')
            : ($locale === 'en' ? 'Awaiting confirmation' : 'Chờ xác nhận'),
    };
    $statusMessage = match ($statusTone) {
        'paid' => $locale === 'en'
            ? 'Travel Plus has recorded your payment for this booking.'
            : 'Travel Plus đã ghi nhận thanh toán cho booking này.',
        'rejected' => $locale === 'en'
            ? 'This booking was cancelled or the payment was not completed.'
            : 'Booking này đã bị hủy hoặc thanh toán chưa hoàn tất.',
        default => $paymentStatus === 'pending_transfer'
            ? ($locale === 'en'
                ? 'The booking is waiting for bank transfer reconciliation.'
                : 'Booking đang chờ Travel Plus đối soát chuyển khoản.')
            : ($locale === 'en'
                ? 'The booking has been recorded and is waiting for payment confirmation.'
                : 'Booking đã được ghi nhận và đang chờ xác nhận thanh toán.'),
    };
    $amountLabel = $statusTone === 'paid' ? $labels['amountPaid'] : $labels['amountDue'];
    $amountValue = $formatCurrency((float) ($statusTone === 'paid'
        ? ($booking['amount_paid_vnd'] ?? $booking['grand_total'] ?? 0)
        : ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0)));
    $tourLink = trim((string) ($booking['tour_link'] ?? '')) !== '' ? (string) $booking['tour_link'] : $homeLink;
    $travelerSummary = $travelerParts !== [] ? implode(', ', $travelerParts) : '-';

    $tripItems = [
        ['icon' => 'bi-suitcase2', 'label' => $labels['tour'], 'value' => (string) ($booking['tour_title'] ?? '-')],
        ['icon' => 'bi-calendar-check', 'label' => $labels['departure'], 'value' => (string) ($booking['departure_label'] ?? '-')],
        ['icon' => 'bi-clock-history', 'label' => $labels['duration'], 'value' => (string) ($booking['duration_label'] ?? '-')],
        ['icon' => 'bi-people', 'label' => $labels['travelers'], 'value' => $travelerSummary],
        ['icon' => 'bi-receipt', 'label' => $labels['createdAt'], 'value' => $formatDateTime((string) ($booking['created_at'] ?? ''))],
    ];
    $customerItems = [
        ['label' => $labels['customerName'], 'value' => (string) ($booking['customer_name'] ?? '-')],
        ['label' => $labels['email'], 'value' => (string) ($booking['customer_email'] ?? '-')],
        ['label' => $labels['phone'], 'value' => (string) ($booking['customer_phone'] ?? '-')],
    ];
    $paymentItems = [
        ['label' => $labels['method'], 'value' => $paymentMethodLabel],
        ['label' => $labels['plan'], 'value' => $paymentPlanLabel],
        ['label' => $amountLabel, 'value' => $amountValue],
        ['label' => $labels['total'], 'value' => $formatCurrency((float) ($booking['grand_total'] ?? 0))],
    ];
    $nextSteps = match ($statusTone) {
        'paid' => $locale === 'en'
            ? ['Keep this booking code for support.', 'Travel Plus may contact you to confirm service details.', 'Contact support if your itinerary details need adjustment.']
            : ['Lưu mã booking này để được hỗ trợ nhanh.', 'Travel Plus có thể liên hệ để xác nhận thêm chi tiết dịch vụ.', 'Liên hệ hỗ trợ nếu cần điều chỉnh thông tin hành trình.'],
        'rejected' => $locale === 'en'
            ? ['Check the payment or cancellation reason.', 'Contact Travel Plus if you want to restore this booking.', 'Create a new booking if your travel plan has changed.']
            : ['Kiểm tra lý do thanh toán/hủy booking.', 'Liên hệ Travel Plus nếu muốn khôi phục booking này.', 'Tạo booking mới nếu kế hoạch chuyến đi đã thay đổi.'],
        default => $paymentStatus === 'pending_transfer'
            ? ($locale === 'en'
                ? ['Keep the transfer reference exactly as your booking code.', 'Wait for Travel Plus to reconcile the transfer.', 'Contact support if the status is not updated after payment.']
                : ['Giữ đúng nội dung chuyển khoản theo mã booking.', 'Chờ Travel Plus đối soát giao dịch.', 'Liên hệ hỗ trợ nếu đã chuyển khoản nhưng trạng thái chưa cập nhật.'])
            : ($locale === 'en'
                ? ['Complete the selected payment method if you have not paid.', 'Keep this booking code for support.', 'Contact Travel Plus if you need help finishing payment.']
                : ['Hoàn tất phương thức thanh toán đã chọn nếu chưa thanh toán.', 'Lưu mã booking này để được hỗ trợ.', 'Liên hệ Travel Plus nếu cần hỗ trợ hoàn tất thanh toán.']),
    };
}
?>
<section class="booking-lookup-page">
    <div class="container">
        <div class="booking-lookup-hero">
            <div class="booking-lookup-copy">
                <span><?= esc($labels['eyebrow']) ?></span>
                <h1><?= esc($labels['title']) ?></h1>
                <p><?= esc($labels['desc']) ?></p>
            </div>

            <form action="<?= esc($lookupAction) ?>" method="post" class="booking-lookup-form">
                <?= csrf_field() ?>
                <label>
                    <span><?= esc($labels['code']) ?></span>
                    <input type="text" name="booking_code" value="<?= esc($bookingCodeInput, 'attr') ?>" placeholder="<?= esc($labels['codePlaceholder'], 'attr') ?>" autocomplete="off">
                </label>
                <label>
                    <span><?= esc($labels['contact']) ?></span>
                    <input type="text" name="contact" value="<?= esc($contactInput, 'attr') ?>" placeholder="<?= esc($labels['contactPlaceholder'], 'attr') ?>" autocomplete="email">
                </label>
                <button type="submit" class="primary-btn1 two">
                    <span><?= esc($labels['submit']) ?><i class="bi bi-search" aria-hidden="true"></i></span>
                    <span><?= esc($labels['submit']) ?><i class="bi bi-search" aria-hidden="true"></i></span>
                </button>
            </form>
        </div>

        <?php if ($error !== ''): ?>
            <div class="booking-lookup-message booking-lookup-message--error">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <div>
                    <strong><?= esc($labels['errorTitle']) ?></strong>
                    <p><?= esc($error) ?></p>
                </div>
            </div>
        <?php elseif ($bookings === []): ?>
            <div class="booking-lookup-message">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <div>
                    <strong><?= esc($labels['notSubmittedTitle']) ?></strong>
                    <p><?= esc($labels['notSubmittedDesc']) ?></p>
                </div>
            </div>
        <?php elseif (count($bookings) > 1): ?>
            <div class="booking-lookup-list">
                <div class="booking-lookup-list__head">
                    <span><?= esc((string) count($bookings)) ?></span>
                    <div>
                        <h2><?= esc($labels['resultsTitle']) ?></h2>
                        <p><?= esc($labels['resultsDesc']) ?></p>
                    </div>
                </div>

                <div class="booking-lookup-cards">
                    <?php foreach ($bookings as $matchedBooking): ?>
                        <?php
                        $matchedStatus = strtolower((string) ($matchedBooking['payment_status'] ?? ''));
                        $matchedTone = match (true) {
                            $matchedStatus === 'paid' => 'paid',
                            in_array($matchedStatus, ['cancelled', 'failed', 'rejected'], true) => 'rejected',
                            default => 'pending',
                        };
                        $matchedStatusLabel = match ($matchedTone) {
                            'paid' => $locale === 'en' ? 'Confirmed' : 'Đã xác nhận',
                            'rejected' => $locale === 'en' ? 'Not completed' : 'Chưa hoàn tất',
                            default => $matchedStatus === 'pending_transfer'
                                ? ($locale === 'en' ? 'Awaiting reconciliation' : 'Chờ đối soát')
                                : ($locale === 'en' ? 'Awaiting confirmation' : 'Chờ xác nhận'),
                        };
                        $matchedAmountLabel = $matchedTone === 'paid' ? $labels['amountPaid'] : $labels['amountDue'];
                        $matchedAmount = $formatCurrency((float) ($matchedTone === 'paid'
                            ? ($matchedBooking['amount_paid_vnd'] ?? $matchedBooking['grand_total'] ?? 0)
                            : ($matchedBooking['amount_due_vnd'] ?? $matchedBooking['grand_total'] ?? 0)));
                        $matchedMethod = strtolower((string) ($matchedBooking['payment_method'] ?? ''));
                        $matchedMethodLabel = match ($matchedMethod) {
                            'paypal' => 'PayPal',
                            'vnpay', 'vnpay_card' => 'VNPAY',
                            'vietqr' => 'VietQR',
                            default => strtoupper((string) ($matchedBooking['payment_method'] ?? '-')),
                        };
                        ?>
                        <article class="booking-lookup-card booking-lookup-card--<?= esc($matchedTone, 'attr') ?>">
                            <div class="booking-lookup-card__main">
                                <span><?= esc($matchedStatusLabel) ?></span>
                                <h3><?= esc((string) ($matchedBooking['booking_code'] ?? '-')) ?></h3>
                                <p><?= esc((string) ($matchedBooking['tour_title'] ?? '-')) ?></p>
                            </div>
                            <dl>
                                <div>
                                    <dt><?= esc($labels['departure']) ?></dt>
                                    <dd><?= esc((string) ($matchedBooking['departure_label'] ?? '-')) ?></dd>
                                </div>
                                <div>
                                    <dt><?= esc($labels['method']) ?></dt>
                                    <dd><?= esc($matchedMethodLabel) ?></dd>
                                </div>
                                <div>
                                    <dt><?= esc($matchedAmountLabel) ?></dt>
                                    <dd><?= esc($matchedAmount) ?></dd>
                                </div>
                                <div>
                                    <dt><?= esc($labels['createdAt']) ?></dt>
                                    <dd><?= esc($formatDateTime((string) ($matchedBooking['created_at'] ?? ''))) ?></dd>
                                </div>
                            </dl>
                            <form action="<?= esc($lookupAction) ?>" method="post" class="booking-lookup-card__action">
                                <?= csrf_field() ?>
                                <input type="hidden" name="booking_code" value="<?= esc((string) ($matchedBooking['booking_code'] ?? ''), 'attr') ?>">
                                <button type="submit"><?= esc($locale === 'en' ? 'View details' : 'Xem chi tiết') ?></button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="booking-lookup-result travelplus-booking-success travelplus-booking-success--<?= esc($statusTone, 'attr') ?>">
                <div class="travelplus-booking-success__hero">
                    <span class="travelplus-booking-success__icon">
                        <i class="bi <?= esc($statusIcon, 'attr') ?>" aria-hidden="true"></i>
                    </span>
                    <div class="travelplus-booking-success__copy">
                        <span><?= esc($statusLabel) ?></span>
                        <h2><?= esc((string) ($booking['booking_code'] ?? '-')) ?></h2>
                        <p><?= esc($statusMessage) ?></p>
                    </div>
                    <div class="travelplus-booking-success__code">
                        <span><?= esc($amountLabel) ?></span>
                        <strong><?= esc($amountValue) ?></strong>
                    </div>
                </div>

                <div class="travelplus-booking-success__detail-grid">
                    <section class="travelplus-booking-success__panel">
                        <h2><?= esc($labels['trip']) ?></h2>
                        <div class="travelplus-booking-success__info-grid booking-lookup-trip-grid">
                            <?php foreach ($tripItems as $item): ?>
                                <div class="travelplus-booking-success__info-item">
                                    <i class="bi <?= esc($item['icon'], 'attr') ?>" aria-hidden="true"></i>
                                    <span><?= esc($item['label']) ?></span>
                                    <strong><?= esc($item['value']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="travelplus-booking-success__panel">
                        <h2><?= esc($labels['payment']) ?></h2>
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

                <div class="travelplus-booking-success__detail-grid">
                    <section class="travelplus-booking-success__panel">
                        <h2><?= esc($labels['customer']) ?></h2>
                        <dl class="travelplus-booking-success__list">
                            <?php foreach ($customerItems as $item): ?>
                                <div>
                                    <dt><?= esc($item['label']) ?></dt>
                                    <dd><?= esc($item['value']) ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </section>

                    <section class="travelplus-booking-success__next">
                        <div>
                            <span><?= esc($labels['status']) ?></span>
                            <h2><?= esc($labels['nextTitle']) ?></h2>
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
                </div>

                <div class="travelplus-booking-success__note">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <p><?= esc($labels['note']) ?></p>
                </div>

                <div class="travelplus-booking-success__actions">
                    <a href="<?= esc($tourLink) ?>" class="primary-btn1 two">
                        <span><?= esc($labels['backTour']) ?><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                        <span><?= esc($labels['backTour']) ?><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                    </a>
                    <a href="<?= esc($contactLink) ?>" class="primary-btn1 two transparent">
                        <span><?= esc($labels['contactSupport']) ?><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                        <span><?= esc($labels['contactSupport']) ?><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                    </a>
                    <a href="<?= esc($homeLink) ?>" class="travelplus-booking-success__home-link">
                        <?= esc($labels['backHome']) ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
