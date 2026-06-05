<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
$bookings = is_array($bookings ?? null) ? $bookings : [];
$authSuccess = session()->getFlashdata('auth_success');
$authError = session()->getFlashdata('auth_error');
$statusValue = strtolower(trim((string) ($user['status'] ?? 'active')));
$statusLabel = match ($statusValue) {
    'active' => $locale === 'en' ? 'Active' : 'Đang hoạt động',
    'inactive' => $locale === 'en' ? 'Inactive' : 'Ngưng hoạt động',
    'blocked' => $locale === 'en' ? 'Blocked' : 'Bị khóa',
    default => ucfirst($statusValue !== '' ? $statusValue : ($locale === 'en' ? 'Unknown' : 'Không xác định')),
};
$statusClass = match ($statusValue) {
    'active' => 'success',
    'inactive' => 'secondary',
    'blocked' => 'danger',
    default => 'dark',
};
$lastLoginLabel = app_datetime(
    (string) ($user['last_login_at'] ?? ''),
    'd/m/Y H:i',
    $locale === 'en' ? 'Not available' : 'Chưa có dữ liệu'
);
$bookingStatusLabels = [
    'draft' => $locale === 'en' ? 'Draft' : 'Nháp',
    'pending_payment' => $locale === 'en' ? 'Pending payment' : 'Chờ thanh toán',
    'pending_transfer' => $locale === 'en' ? 'Pending transfer' : 'Chờ chuyển khoản',
    'paid' => $locale === 'en' ? 'Paid' : 'Đã thanh toán',
    'cancelled' => $locale === 'en' ? 'Cancelled' : 'Đã hủy',
    'failed' => $locale === 'en' ? 'Failed' : 'Thất bại',
];
$bookingStatusClasses = [
    'draft' => 'secondary',
    'pending_payment' => 'warning',
    'pending_transfer' => 'warning',
    'paid' => 'success',
    'cancelled' => 'dark',
    'failed' => 'danger',
];
$paymentLabels = [
    'paypal' => 'PayPal',
    'vnpay' => 'VNPAY',
    'vietqr' => 'VietQR',
    'momo' => 'MoMo',
    'zalopay' => 'ZaloPay',
];
?>
<section class="travelplus-account-page">
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            <?php if (! empty($authSuccess)): ?>
                <div class="alert alert-success mb-4"><?= esc($authSuccess) ?></div>
            <?php endif; ?>
            <?php if (! empty($authError)): ?>
                <div class="alert alert-danger mb-4"><?= esc($authError) ?></div>
            <?php endif; ?>

            <div class="travelplus-account-card">
                <div class="travelplus-account-head">
                    <div>
                        <span><?= esc($locale === 'en' ? 'My Travel Plus' : 'Tài khoản của tôi') ?></span>
                        <h2><?= esc($t('auth.profile.title')) ?></h2>
                        <p><?= esc($t('auth.profile.desc')) ?></p>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.logout', $locale) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="primary-btn1 transparent">
                                <span><?= esc($t('auth.logout')) ?></span>
                                <span><?= esc($t('auth.logout')) ?></span>
                            </button>
                        </form>
                    </div>
                </div>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.profile', $locale) ?>">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.fullName')) ?></label>
                                <input type="text" name="full_name" value="<?= esc((string) ($user['full_name'] ?? '')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.email')) ?></label>
                                <input type="text" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.username')) ?></label>
                                <input type="text" value="<?= esc((string) ($user['username'] ?? '')) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.phone')) ?></label>
                                <input type="text" name="phone" value="<?= esc((string) ($user['phone'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.status')) ?></label>
                                <div class="border rounded-3 px-3 py-3 bg-light d-flex align-items-center gap-2">
                                    <span class="badge text-bg-<?= esc($statusClass) ?> px-3 py-2"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.lastLogin')) ?></label>
                                <div class="border rounded-3 px-3 py-3 bg-light text-muted"><?= esc($lastLoginLabel) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($locale === 'en' ? 'New password' : 'Mật khẩu mới') ?></label>
                                <input type="password" name="new_password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($locale === 'en' ? 'Confirm new password' : 'Xác nhận mật khẩu mới') ?></label>
                                <input type="password" name="new_password_confirm">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                                <button type="submit" class="btn btn-outline-danger" form="logout-all-devices-form">
                                    <?= esc($locale === 'en' ? 'Log out all devices' : 'Đăng xuất mọi thiết bị') ?>
                                </button>
                                <button type="submit" class="primary-btn1 two">
                                    <span><?= esc($locale === 'en' ? 'Save changes' : 'Lưu thay đổi') ?></span>
                                    <span><?= esc($locale === 'en' ? 'Save changes' : 'Lưu thay đổi') ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <form
                id="logout-all-devices-form"
                method="post"
                action="<?= \App\Data\LocalizedPathCatalog::url('auth.logoutAll', $locale) ?>"
                onsubmit="return confirm('<?= esc($locale === 'en' ? 'This will sign you out on all remembered devices. Continue?' : 'Thao tác này sẽ đăng xuất bạn trên tất cả thiết bị đã ghi nhớ. Tiếp tục?') ?>');">
                <?= csrf_field() ?>
            </form>

            <div class="travelplus-account-card travelplus-account-bookings">
                <div class="travelplus-account-head travelplus-account-head--compact">
                    <div>
                        <h4 class="mb-1"><?= esc($locale === 'en' ? 'My bookings' : 'Booking đã đặt') ?></h4>
                        <p class="mb-0 text-muted"><?= esc($locale === 'en' ? 'Recent bookings linked to your account or email.' : 'Các booking gần đây gắn với tài khoản hoặc email của bạn.') ?></p>
                    </div>
                </div>

                <?php if ($bookings === []): ?>
                    <div class="border rounded-3 px-3 py-3 bg-light text-muted">
                        <?= esc($locale === 'en' ? 'No bookings have been recorded yet.' : 'Chưa có booking nào được ghi nhận.') ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th><?= esc($locale === 'en' ? 'Code' : 'Mã') ?></th>
                                    <th><?= esc($locale === 'en' ? 'Tour' : 'Tour') ?></th>
                                    <th><?= esc($locale === 'en' ? 'Departure' : 'Khởi hành') ?></th>
                                    <th><?= esc($locale === 'en' ? 'Travelers' : 'Số khách') ?></th>
                                    <th><?= esc($locale === 'en' ? 'Payment' : 'Thanh toán') ?></th>
                                    <th><?= esc($locale === 'en' ? 'Status' : 'Trạng thái') ?></th>
                                    <th><?= esc($locale === 'en' ? 'Created' : 'Ngày tạo') ?></th>
                                    <th class="text-end"><?= esc($locale === 'en' ? 'Action' : 'Thao tác') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <?php
                                    $bookingStatus = strtolower((string) ($booking['payment_status'] ?? 'draft'));
                                    $bookingStatusLabel = $bookingStatusLabels[$bookingStatus] ?? ($booking['payment_status'] ?? '-');
                                    $bookingStatusClass = $bookingStatusClasses[$bookingStatus] ?? 'secondary';
                                    $bookingPaymentMethod = strtolower((string) ($booking['payment_method'] ?? ''));
                                    $bookingPaymentLabel = $paymentLabels[$bookingPaymentMethod] ?? strtoupper((string) ($booking['payment_method'] ?? '-'));
                                    $bookingTravelerParts = [];
                                    $bookingAdult = max(0, (int) ($booking['adult_quantity'] ?? 0));
                                    $bookingChild = max(0, (int) ($booking['child_quantity'] ?? 0));
                                    $bookingInfant = max(0, (int) ($booking['infant_quantity'] ?? 0));
                                    if ($bookingAdult > 0) { $bookingTravelerParts[] = $bookingAdult . ' ' . $t('tour.booking.adult'); }
                                    if ($bookingChild > 0) { $bookingTravelerParts[] = $bookingChild . ' ' . $t('tour.booking.child'); }
                                    if ($bookingInfant > 0) { $bookingTravelerParts[] = $bookingInfant . ' ' . $t('tour.booking.infant'); }
                                    $bookingTravelerSummary = $bookingTravelerParts !== [] ? implode(', ', $bookingTravelerParts) : '-';
                                    $bookingAmount = (float) (($bookingStatus === 'paid'
                                        ? ($booking['amount_paid_vnd'] ?? 0)
                                        : ($booking['amount_due_vnd'] ?? 0)) ?: 0);
                                    $bookingLink = \App\Data\LocalizedPathCatalog::url('booking.successPrefix', $locale) . '/' . rawurlencode((string) ($booking['booking_code'] ?? ''));
                                    ?>
                                    <tr>
                                        <td><strong><?= esc((string) ($booking['booking_code'] ?? '-')) ?></strong></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc((string) ($booking['tour_title'] ?? '-')) ?></div>
                                            <div class="text-muted small"><?= esc($bookingPaymentLabel) ?> • <?= esc(number_format($bookingAmount, 0, ',', '.')) ?> VND</div>
                                        </td>
                                        <td><?= esc((string) ($booking['departure_label'] ?? '-')) ?></td>
                                        <td><?= esc($bookingTravelerSummary) ?></td>
                                        <td><?= esc($bookingPaymentLabel) ?></td>
                                        <td><span class="badge text-bg-<?= esc($bookingStatusClass) ?>"><?= esc((string) $bookingStatusLabel) ?></span></td>
                                        <td><?= esc(app_datetime((string) ($booking['created_at'] ?? ''), 'd/m/Y H:i', '-')) ?></td>
                                        <td class="text-end">
                                            <a href="<?= esc($bookingLink) ?>" class="btn btn-sm btn-outline-primary">
                                                <?= esc($locale === 'en' ? 'View' : 'Xem') ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
</section>
<?= $this->endSection() ?>
