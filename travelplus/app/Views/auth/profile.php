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
$membership = is_array($membership ?? null) ? $membership : [];
$loyaltyHistory = is_array($loyaltyHistory ?? null) ? $loyaltyHistory : [];
$membershipTiers = is_array($membership['tiers'] ?? null) ? $membership['tiers'] : [];
$membershipTierLabels = $locale === 'en'
    ? [
        'member' => 'Member',
        'silver' => 'Silver',
        'gold' => 'Gold',
        'diamond' => 'Diamond',
        'signature' => 'Signature',
    ]
    : [
        'member' => 'Thành viên',
        'silver' => 'Bạc',
        'gold' => 'Vàng',
        'diamond' => 'Kim cương',
        'signature' => 'Signature',
    ];
$membershipTierIcons = [
    'member' => 'bi-person-fill',
    'silver' => 'bi-stars',
    'gold' => 'bi-award-fill',
    'diamond' => 'bi-gem',
    'signature' => 'bi-suit-diamond-fill',
];
$membershipCurrentTier = is_array($membership['current_tier'] ?? null)
    ? $membership['current_tier']
    : ['key' => 'member', 'minimum_points' => 0];
$membershipCurrentKey = (string) ($membershipCurrentTier['key'] ?? 'member');
$membershipCurrentIcon = $membershipTierIcons[$membershipCurrentKey] ?? $membershipTierIcons['member'];
$membershipNextTier = is_array($membership['next_tier'] ?? null) ? $membership['next_tier'] : null;
$membershipProgramActive = (bool) ($membership['program_active'] ?? false);
$membershipPoints = max(0, (int) ($membership['points'] ?? 0));
$membershipProgress = min(100, max(0, (int) ($membership['progress'] ?? 0)));
$membershipCurrentIndex = 0;
foreach ($membershipTiers as $tierIndex => $tier) {
    if (($tier['key'] ?? '') === $membershipCurrentKey) {
        $membershipCurrentIndex = (int) $tierIndex;
        break;
    }
}
$displayName = trim((string) ($user['full_name'] ?? '')) ?: ($locale === 'en' ? 'Travel Plus member' : 'Thành viên Travel Plus');
$nameParts = preg_split('/\s+/u', $displayName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$firstInitial = $nameParts !== [] ? mb_substr((string) $nameParts[0], 0, 1, 'UTF-8') : 'T';
$lastInitial = count($nameParts) > 1 ? mb_substr((string) $nameParts[array_key_last($nameParts)], 0, 1, 'UTF-8') : '';
$accountInitials = mb_strtoupper($firstInitial . $lastInitial, 'UTF-8');
?>
<section class="travelplus-account-page">
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-11 col-lg-12">
            <?php if (! empty($authSuccess)): ?>
                <div class="alert alert-success mb-4"><?= esc($authSuccess) ?></div>
            <?php endif; ?>
            <?php if (! empty($authError)): ?>
                <div class="alert alert-danger mb-4"><?= esc($authError) ?></div>
            <?php endif; ?>

            <div class="travelplus-account-card travelplus-account-dashboard travelplus-account-dashboard--<?= esc($membershipCurrentKey, 'attr') ?>">
                <div class="travelplus-account-overview">
                    <div class="travelplus-account-identity">
                        <div class="travelplus-account-avatar travelplus-account-avatar--<?= esc($membershipCurrentKey, 'attr') ?>" aria-hidden="true">
                            <span><?= esc($accountInitials) ?></span>
                            <i class="bi <?= esc($membershipCurrentIcon, 'attr') ?>"></i>
                        </div>
                        <div class="travelplus-account-identity-copy">
                            <span><?= esc($locale === 'en' ? 'My Travel Plus' : 'Tài khoản Travel Plus') ?></span>
                            <h1><?= esc($displayName) ?></h1>
                            <p><?= esc((string) ($user['email'] ?? '')) ?></p>
                            <div class="travelplus-account-meta">
                                <span class="travelplus-account-status travelplus-account-status--<?= esc($statusClass, 'attr') ?>">
                                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                    <?= esc($statusLabel) ?>
                                </span>
                                <span>
                                    <i class="bi bi-clock-history" aria-hidden="true"></i>
                                    <?= esc($locale === 'en' ? 'Last sign-in: ' : 'Đăng nhập gần nhất: ') ?><?= esc($lastLoginLabel) ?>
                                </span>
                            </div>
                        </div>
                        <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.logout', $locale) ?>" class="travelplus-account-signout">
                            <?= csrf_field() ?>
                            <button type="submit" class="travelplus-account-signout-btn">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                <span><?= esc($t('auth.logout')) ?></span>
                            </button>
                        </form>
                    </div>

                    <section class="travelplus-membership-panel travelplus-membership-panel--<?= esc($membershipCurrentKey, 'attr') ?>" aria-labelledby="membership-title">
                        <div class="travelplus-membership-head">
                            <div class="travelplus-membership-pass-identity">
                                <span class="travelplus-membership-emblem" aria-hidden="true">
                                    <i class="bi <?= esc($membershipCurrentIcon, 'attr') ?>"></i>
                                </span>
                                <div class="travelplus-membership-title">
                                    <span>Travel Plus Rewards</span>
                                    <h2 id="membership-title"><?= esc($membershipTierLabels[$membershipCurrentKey] ?? ucfirst($membershipCurrentKey)) ?></h2>
                                </div>
                            </div>
                            <div class="travelplus-membership-state">
                                <div class="travelplus-membership-state-label">
                                    <small><?= esc($locale === 'en' ? 'Points balance' : 'Điểm hiện có') ?></small>
                                    <?php if ($membershipProgramActive): ?>
                                        <details class="travelplus-membership-rate">
                                            <summary aria-label="<?= esc($locale === 'en' ? 'How membership points are earned' : 'Cách quy đổi điểm thành viên', 'attr') ?>">
                                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                            </summary>
                                            <span role="tooltip">
                                                <?= esc($locale === 'en' ? '10,000 VND paid = 1 point' : '10.000đ thanh toán = 1 điểm') ?>
                                            </span>
                                        </details>
                                    <?php endif; ?>
                                </div>
                                <strong>
                                    <?= esc($membershipProgramActive
                                        ? number_format($membershipPoints, 0, ',', '.')
                                        : ($locale === 'en' ? 'Syncing' : 'Đang đồng bộ')) ?>
                                </strong>
                            </div>
                        </div>

                        <div class="travelplus-membership-progress-copy">
                            <span>
                                <?php if (! $membershipProgramActive): ?>
                                    <?= esc($locale === 'en'
                                        ? 'Your membership points are being synchronized.'
                                        : 'Điểm hội viên của bạn đang được đồng bộ.') ?>
                                <?php elseif ($membershipNextTier !== null): ?>
                                    <?= esc(number_format((int) ($membership['remaining_points'] ?? 0), 0, ',', '.')) ?>
                                    <?= esc($locale === 'en' ? ' points to ' : ' điểm nữa để lên hạng ') ?>
                                    <?= esc($membershipTierLabels[$membershipNextTier['key'] ?? ''] ?? '') ?>
                                <?php else: ?>
                                    <?= esc($locale === 'en' ? 'You have reached the highest member tier.' : 'Bạn đã đạt hạng thành viên cao nhất.') ?>
                                <?php endif; ?>
                            </span>
                            <?php if ($membershipProgramActive): ?>
                                <strong><?= esc((string) $membershipProgress) ?>%</strong>
                            <?php endif; ?>
                        </div>
                        <div
                            class="travelplus-membership-progress"
                            role="progressbar"
                            aria-label="<?= esc($locale === 'en' ? 'Tier progress' : 'Tiến độ hạng thành viên', 'attr') ?>"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="<?= esc((string) $membershipProgress, 'attr') ?>">
                            <span style="width: <?= esc((string) $membershipProgress, 'attr') ?>%">
                                <i class="bi bi-airplane-fill" aria-hidden="true"></i>
                            </span>
                        </div>

                        <ol class="travelplus-membership-tiers">
                            <?php foreach ($membershipTiers as $tierIndex => $tier): ?>
                                <?php $tierKey = (string) ($tier['key'] ?? 'member'); ?>
                                <li class="<?= $tierIndex <= $membershipCurrentIndex ? 'is-reached' : '' ?> <?= $tierKey === $membershipCurrentKey ? 'is-current' : '' ?>">
                                    <span></span>
                                    <small><?= esc($membershipTierLabels[$tierKey] ?? ucfirst($tierKey)) ?></small>
                                    <em><?= esc(number_format((int) ($tier['minimum_points'] ?? 0), 0, ',', '.')) ?></em>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                </div>

                <div class="travelplus-account-stats">
                    <div>
                        <i class="bi bi-journal-check" aria-hidden="true"></i>
                        <span><?= esc($locale === 'en' ? 'Bookings' : 'Booking đã đặt') ?></span>
                        <strong><?= esc(number_format((int) ($membership['booking_count'] ?? count($bookings)), 0, ',', '.')) ?></strong>
                    </div>
                    <div>
                        <i class="bi bi-patch-check" aria-hidden="true"></i>
                        <span><?= esc($locale === 'en' ? 'Paid tours' : 'Tour đã thanh toán') ?></span>
                        <strong><?= esc(number_format((int) ($membership['paid_booking_count'] ?? 0), 0, ',', '.')) ?></strong>
                    </div>
                    <div>
                        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                        <span><?= esc($locale === 'en' ? 'Awaiting action' : 'Đang chờ xử lý') ?></span>
                        <strong><?= esc(number_format((int) ($membership['pending_booking_count'] ?? 0), 0, ',', '.')) ?></strong>
                    </div>
                </div>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.profile', $locale) ?>" class="travelplus-account-profile-form">
                    <?= csrf_field() ?>

                    <section class="travelplus-account-form-section">
                        <div class="travelplus-account-section-head">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <div>
                                <h2><?= esc($locale === 'en' ? 'Personal information' : 'Thông tin cá nhân') ?></h2>
                                <p><?= esc($locale === 'en' ? 'Keep your contact details accurate for booking support.' : 'Cập nhật thông tin để được hỗ trợ booking chính xác hơn.') ?></p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($t('auth.profile.fullName')) ?></label>
                                    <input type="text" name="full_name" value="<?= esc((string) ($user['full_name'] ?? '')) ?>" required>
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
                        </div>
                    </section>

                    <section class="travelplus-account-form-section travelplus-account-security">
                        <div class="travelplus-account-section-head">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <div>
                                <h2><?= esc($locale === 'en' ? 'Account security' : 'Bảo mật tài khoản') ?></h2>
                                <p><?= esc($locale === 'en' ? 'Leave both password fields blank if you do not want to change it.' : 'Để trống hai ô mật khẩu nếu bạn không muốn thay đổi.') ?></p>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($locale === 'en' ? 'New password' : 'Mật khẩu mới') ?></label>
                                    <input type="password" name="new_password" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($locale === 'en' ? 'Confirm new password' : 'Xác nhận mật khẩu mới') ?></label>
                                    <input type="password" name="new_password_confirm" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="travelplus-account-actions">
                        <button type="submit" class="btn btn-outline-danger travelplus-account-logout-all" form="logout-all-devices-form">
                            <?= esc($locale === 'en' ? 'Log out all devices' : 'Đăng xuất mọi thiết bị') ?>
                        </button>
                        <button type="submit" class="primary-btn1 two travelplus-account-save">
                            <span><?= esc($locale === 'en' ? 'Save changes' : 'Lưu thay đổi') ?></span>
                            <span><?= esc($locale === 'en' ? 'Save changes' : 'Lưu thay đổi') ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($membershipProgramActive): ?>
                <details class="travelplus-account-card travelplus-loyalty-history">
                    <summary>
                        <span class="travelplus-loyalty-history-icon" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </span>
                        <span class="travelplus-loyalty-history-heading">
                            <strong><?= esc($locale === 'en' ? 'Points history' : 'Lịch sử điểm') ?></strong>
                            <small><?= esc($locale === 'en'
                                ? 'Track points earned and adjusted from your paid bookings.'
                                : 'Theo dõi điểm được cộng và điều chỉnh từ các booking đã thanh toán.') ?></small>
                        </span>
                        <span class="travelplus-loyalty-history-count">
                            <?= esc(number_format(count($loyaltyHistory), 0, ',', '.')) ?>
                            <?= esc($locale === 'en' ? 'entries' : 'giao dịch') ?>
                        </span>
                        <i class="bi bi-chevron-down travelplus-loyalty-history-chevron" aria-hidden="true"></i>
                    </summary>

                    <div class="travelplus-loyalty-history-body">
                        <?php if ($loyaltyHistory === []): ?>
                            <div class="travelplus-loyalty-history-empty">
                                <i class="bi bi-stars" aria-hidden="true"></i>
                                <span><?= esc($locale === 'en'
                                    ? 'Your first paid booking will appear here.'
                                    : 'Booking thanh toán đầu tiên của bạn sẽ được ghi nhận tại đây.') ?></span>
                            </div>
                        <?php else: ?>
                            <div class="travelplus-loyalty-history-list">
                                <?php foreach ($loyaltyHistory as $transaction): ?>
                                    <?php
                                    $transactionPoints = (int) ($transaction['points'] ?? 0);
                                    $isCredit = $transactionPoints > 0;
                                    $transactionType = (string) ($transaction['type'] ?? '');
                                    $transactionTitle = match ($transactionType) {
                                        'booking_earned' => $locale === 'en' ? 'Points earned from booking' : 'Cộng điểm từ booking',
                                        'booking_reversed' => $locale === 'en' ? 'Booking points adjusted' : 'Điều chỉnh điểm booking',
                                        default => $locale === 'en' ? 'Points adjustment' : 'Điều chỉnh điểm',
                                    };
                                    ?>
                                    <div class="travelplus-loyalty-transaction travelplus-loyalty-transaction--<?= $isCredit ? 'credit' : 'debit' ?>">
                                        <span class="travelplus-loyalty-transaction-icon" aria-hidden="true">
                                            <i class="bi <?= $isCredit ? 'bi-plus-lg' : 'bi-arrow-counterclockwise' ?>"></i>
                                        </span>
                                        <span class="travelplus-loyalty-transaction-copy">
                                            <strong><?= esc($transactionTitle) ?></strong>
                                            <small>
                                                <?= esc(trim((string) ($transaction['description'] ?? '')) ?: ($locale === 'en' ? 'Travel Plus booking' : 'Booking Travel Plus')) ?>
                                                <span aria-hidden="true">&middot;</span>
                                                <?= esc(app_datetime((string) ($transaction['created_at'] ?? ''), 'd/m/Y H:i', '-')) ?>
                                            </small>
                                        </span>
                                        <strong class="travelplus-loyalty-transaction-points">
                                            <?= $transactionPoints > 0 ? '+' : '' ?><?= esc(number_format($transactionPoints, 0, ',', '.')) ?>
                                            <small><?= esc($locale === 'en' ? 'pts' : 'điểm') ?></small>
                                        </strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>
            <?php endif; ?>

            <form
                id="logout-all-devices-form"
                method="post"
                action="<?= \App\Data\LocalizedPathCatalog::url('auth.logoutAll', $locale) ?>"
                onsubmit="return confirm('<?= esc($locale === 'en' ? 'This will sign you out on all remembered devices. Continue?' : 'Thao tác này sẽ đăng xuất bạn trên tất cả thiết bị đã ghi nhớ. Tiếp tục?') ?>');">
                <?= csrf_field() ?>
            </form>

            <section class="travelplus-account-bookings">
                <div class="travelplus-account-head travelplus-account-head--compact">
                    <div>
                        <h2><?= esc($locale === 'en' ? 'My bookings' : 'Booking đã đặt') ?></h2>
                        <p><?= esc($locale === 'en' ? 'Recent bookings linked to your account or email.' : 'Các booking gần đây gắn với tài khoản hoặc email của bạn.') ?></p>
                    </div>
                    <span class="travelplus-account-booking-count"><?= esc(number_format(count($bookings), 0, ',', '.')) ?> booking</span>
                </div>

                <?php if ($bookings === []): ?>
                    <div class="travelplus-booking-empty">
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                        <?= esc($locale === 'en' ? 'No bookings have been recorded yet.' : 'Chưa có booking nào được ghi nhận.') ?>
                    </div>
                <?php else: ?>
                    <div class="travelplus-booking-list">
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
                            $isRejectedBooking = in_array($bookingStatus, ['cancelled', 'failed'], true);
                            $bookingAmount = (float) (($bookingStatus === 'paid'
                                ? ($booking['amount_paid_vnd'] ?? 0)
                                : ($isRejectedBooking ? ($booking['grand_total'] ?? 0) : ($booking['amount_due_vnd'] ?? 0))) ?: 0);
                            $bookingAmountLabel = match (true) {
                                $bookingStatus === 'paid' => $locale === 'en' ? 'Paid' : 'Đã thanh toán',
                                $isRejectedBooking => $locale === 'en' ? 'Booking value' : 'Giá trị booking',
                                default => $locale === 'en' ? 'Amount due' : 'Cần thanh toán',
                            };
                            $bookingLink = \App\Data\LocalizedPathCatalog::url('booking.successPrefix', $locale) . '/' . rawurlencode((string) ($booking['booking_code'] ?? ''));
                            ?>
                            <article class="travelplus-booking-item travelplus-booking-item--<?= esc($bookingStatusClass, 'attr') ?>">
                                <div class="travelplus-booking-item-main">
                                    <div class="travelplus-booking-item-topline">
                                        <span class="travelplus-booking-code">
                                            <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                                            <?= esc((string) ($booking['booking_code'] ?? '-')) ?>
                                        </span>
                                        <span class="travelplus-booking-status travelplus-booking-status--<?= esc($bookingStatusClass, 'attr') ?>">
                                            <?= esc((string) $bookingStatusLabel) ?>
                                        </span>
                                    </div>
                                    <h3><?= esc((string) ($booking['tour_title'] ?? '-')) ?></h3>
                                </div>

                                <dl class="travelplus-booking-facts">
                                    <div>
                                        <dt><i class="bi bi-calendar3" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Departure' : 'Khởi hành') ?></dt>
                                        <dd><?= esc((string) ($booking['departure_label'] ?? '-')) ?></dd>
                                    </div>
                                    <div>
                                        <dt><i class="bi bi-people" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Travelers' : 'Số khách') ?></dt>
                                        <dd><?= esc($bookingTravelerSummary) ?></dd>
                                    </div>
                                    <div>
                                        <dt><i class="bi bi-credit-card" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Payment' : 'Thanh toán') ?></dt>
                                        <dd><?= esc($bookingPaymentLabel) ?></dd>
                                    </div>
                                    <div>
                                        <dt><i class="bi bi-clock-history" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Booked on' : 'Ngày đặt') ?></dt>
                                        <dd><?= esc(app_datetime((string) ($booking['created_at'] ?? ''), 'd/m/Y H:i', '-')) ?></dd>
                                    </div>
                                </dl>

                                <div class="travelplus-booking-item-side">
                                    <span><?= esc($bookingAmountLabel) ?></span>
                                    <strong><?= esc(number_format($bookingAmount, 0, ',', '.')) ?> VND</strong>
                                    <a href="<?= esc($bookingLink) ?>" class="travelplus-booking-detail-link">
                                        <span><?= esc($locale === 'en' ? 'View booking' : 'Xem booking') ?></span>
                                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
    </div>
</section>
<?= $this->endSection() ?>
