<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
$bookings = is_array($bookings ?? null) ? $bookings : [];
$authSuccess = session()->getFlashdata('auth_success');
$authError = session()->getFlashdata('auth_error');
$administrativeProvinces = is_array($administrativeProvinces ?? null) ? $administrativeProvinces : [];
$addressDataUrl = (string) ($addressDataUrl ?? '');
$selectedProvinceCode = (string) old('province_code', $user['province_code'] ?? '');
$selectedWardCode = (string) old('ward_code', $user['ward_code'] ?? '');
$selectedAddressLine = (string) old('address_line', $user['address_line'] ?? '');
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
$rewardCatalog = is_array($rewardCatalog ?? null) ? $rewardCatalog : [];
$rewardVouchers = is_array($rewardVouchers ?? null) ? $rewardVouchers : [];
$rewardsAvailable = (bool) ($rewardsAvailable ?? false);
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
$membershipQualifyingPoints = max(0, (int) ($membership['qualifying_points'] ?? $membershipPoints));
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
$passportSearchUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$passportProgramUrl = \App\Data\LocalizedPathCatalog::url('passport.program', $locale);
$readyPassportVouchers = [];
$expiringPassportVouchers = [];
$nearestPassportExpiry = null;
$nowTimestamp = time();
foreach ($rewardVouchers as $rewardVoucher) {
    $voucherStatus = strtolower((string) ($rewardVoucher['status'] ?? 'issued'));
    $voucherExpiryTimestamp = strtotime((string) ($rewardVoucher['expires_at'] ?? '')) ?: 0;
    $isVoucherReady = $voucherStatus === 'issued'
        && (int) ($rewardVoucher['used_count'] ?? 0) === 0
        && (int) ($rewardVoucher['is_active'] ?? 0) === 1
        && $voucherExpiryTimestamp >= $nowTimestamp;

    if (! $isVoucherReady) {
        continue;
    }

    $readyPassportVouchers[] = $rewardVoucher;
    if ($nearestPassportExpiry === null || $voucherExpiryTimestamp < $nearestPassportExpiry) {
        $nearestPassportExpiry = $voucherExpiryTimestamp;
    }
    if ($voucherExpiryTimestamp <= strtotime('+30 days', $nowTimestamp)) {
        $expiringPassportVouchers[] = $rewardVoucher;
    }
}
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

                    <section class="travelplus-membership-panel travelplus-membership-panel--<?= esc($membershipCurrentKey, 'attr') ?> travelplus-profile-passport" aria-labelledby="membership-title">
                        <article class="travelplus-profile-passport-card" aria-label="<?= esc(($locale === 'en' ? 'Current tier: ' : 'Hạng hiện tại: ') . ($membershipTierLabels[$membershipCurrentKey] ?? ucfirst($membershipCurrentKey)), 'attr') ?>">
                            <span class="travelplus-profile-passport-card__shine" aria-hidden="true"></span>
                            <div class="travelplus-profile-passport-card__top">
                                <span class="travelplus-profile-passport-card__brand"><i class="bi bi-passport-fill" aria-hidden="true"></i><span>TravelPlus<strong>Passport</strong></span></span>
                                <span class="travelplus-profile-passport-card__status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Active' : 'Đang hoạt động') ?></span>
                            </div>
                            <div class="travelplus-profile-passport-card__tier">
                                <span><i class="bi <?= esc($membershipCurrentIcon, 'attr') ?>" aria-hidden="true"></i></span>
                                <div>
                                    <small><?= esc($locale === 'en' ? 'Current member tier' : 'Hạng thành viên hiện tại') ?></small>
                                    <strong><?= esc($membershipTierLabels[$membershipCurrentKey] ?? ucfirst($membershipCurrentKey)) ?></strong>
                                    <b><?= esc(number_format($membershipQualifyingPoints, 0, ',', '.')) ?> <?= esc($locale === 'en' ? 'qualifying miles' : 'Dặm xét hạng') ?></b>
                                </div>
                            </div>
                            <div class="travelplus-profile-passport-card__foot">
                                <span><small><?= esc($locale === 'en' ? 'TravelPlus member' : 'Thành viên TravelPlus') ?></small><strong><?= esc(mb_strtoupper($displayName, 'UTF-8')) ?></strong></span>
                                <img src="<?= esc(base_url('assets/images/logo-white.svg'), 'attr') ?>" alt="Travel Plus">
                            </div>
                        </article>

                        <div class="travelplus-profile-passport__content">
                            <header class="travelplus-profile-passport__head">
                                <div>
                                    <span><i class="bi bi-stars" aria-hidden="true"></i> <?= esc($locale === 'en' ? 'My membership' : 'Passport của tôi') ?></span>
                                    <h2 id="membership-title"><?= esc($locale === 'en' ? 'Your miles and benefits' : 'Dặm và quyền lợi của bạn') ?></h2>
                                </div>
                                <a href="<?= esc($passportProgramUrl, 'attr') ?>"><?= esc($locale === 'en' ? 'View tier benefits' : 'Xem quyền lợi hạng') ?> <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                            </header>

                            <div class="travelplus-profile-passport__balances">
                                <article class="travelplus-profile-passport__balance travelplus-profile-passport__balance--available">
                                    <header><span class="travelplus-profile-passport__balance-icon"><i class="bi bi-ticket-perforated-fill" aria-hidden="true"></i></span><small><?= esc($locale === 'en' ? 'Available miles' : 'Dặm khả dụng') ?></small></header>
                                    <strong><?= esc(number_format($membershipPoints, 0, ',', '.')) ?><small><?= esc($locale === 'en' ? ' miles' : ' Dặm') ?></small></strong>
                                    <span class="travelplus-profile-passport__balance-use"><i class="bi bi-arrow-left-right" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Redeem tour vouchers' : 'Dùng để đổi voucher tour') ?></span>
                                </article>
                                <article class="travelplus-profile-passport__balance travelplus-profile-passport__balance--qualifying">
                                    <header><span class="travelplus-profile-passport__balance-icon"><i class="bi bi-award-fill" aria-hidden="true"></i></span><small><?= esc($locale === 'en' ? 'Qualifying miles' : 'Dặm xét hạng') ?></small></header>
                                    <strong><?= esc(number_format($membershipQualifyingPoints, 0, ',', '.')) ?><small><?= esc($locale === 'en' ? ' miles' : ' Dặm') ?></small></strong>
                                    <span class="travelplus-profile-passport__balance-use"><i class="bi bi-bar-chart-steps" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Determines your member tier' : 'Dùng để xác định hạng') ?></span>
                                </article>
                            </div>

                            <div class="travelplus-profile-passport__progress travelplus-profile-passport__progress--<?= esc((string) ($membershipNextTier['key'] ?? $membershipCurrentKey), 'attr') ?>">
                                <div class="travelplus-profile-passport__progress-head">
                                    <span class="travelplus-profile-passport__next-icon"><i class="bi <?= esc($membershipTierIcons[(string) ($membershipNextTier['key'] ?? $membershipCurrentKey)] ?? $membershipCurrentIcon, 'attr') ?>" aria-hidden="true"></i></span>
                                    <span>
                                        <small><?= esc($membershipNextTier !== null
                                            ? ($locale === 'en' ? 'Your next milestone' : 'Mục tiêu hạng tiếp theo')
                                            : ($locale === 'en' ? 'Tier status' : 'Trạng thái hạng')) ?></small>
                                        <strong><?= esc($membershipNextTier !== null
                                            ? ($membershipTierLabels[$membershipNextTier['key'] ?? ''] ?? '')
                                            : ($locale === 'en' ? 'Highest tier reached' : 'Đã đạt hạng cao nhất')) ?></strong>
                                    </span>
                                    <b><?= esc($membershipNextTier !== null
                                        ? ($locale === 'en' ? 'Only ' : 'Còn ') . number_format((int) ($membership['remaining_points'] ?? 0), 0, ',', '.') . ($locale === 'en' ? ' miles' : ' Dặm')
                                        : '100%') ?></b>
                                </div>
                                <span class="travelplus-profile-passport__progress-track" role="progressbar" aria-label="<?= esc($locale === 'en' ? 'Tier progress' : 'Tiến độ lên hạng', 'attr') ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= esc((string) $membershipProgress, 'attr') ?>"><i style="width:<?= esc((string) $membershipProgress, 'attr') ?>%"></i></span>
                                <div class="travelplus-profile-passport__progress-foot">
                                    <?php if ($membershipNextTier !== null): ?>
                                        <small><strong><?= esc(number_format($membershipQualifyingPoints, 0, ',', '.')) ?></strong> / <?= esc(number_format((int) ($membershipNextTier['minimum_points'] ?? 0), 0, ',', '.')) ?> <?= esc($locale === 'en' ? 'qualifying miles' : 'Dặm xét hạng') ?></small>
                                    <?php endif; ?>
                                    <span><?= esc((string) $membershipProgress) ?>% <?= esc($locale === 'en' ? 'completed' : 'chặng lên hạng') ?></span>
                                </div>
                            </div>

                            <div class="travelplus-profile-passport__actions">
                                <a class="is-primary" href="<?= esc($passportSearchUrl, 'attr') ?>"><i class="bi bi-luggage-fill" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Book a tour to earn miles' : 'Đặt tour tích thêm Dặm') ?></a>
                                <a href="#passport-wallet"><i class="bi bi-ticket-detailed" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Open voucher wallet' : 'Mở ví voucher') ?></a>
                            </div>
                        </div>

                        <aside class="travelplus-profile-passport__wallet" aria-label="<?= esc($locale === 'en' ? 'Voucher wallet summary' : 'Tóm tắt ví voucher', 'attr') ?>">
                            <span class="travelplus-profile-passport__wallet-icon"><i class="bi bi-wallet2" aria-hidden="true"></i></span>
                            <div><small><?= esc($locale === 'en' ? 'Ready to use' : 'Voucher sẵn sàng') ?></small><strong><?= esc(number_format(count($readyPassportVouchers), 0, ',', '.')) ?></strong></div>
                            <div><small><?= esc($locale === 'en' ? 'Expiring in 30 days' : 'Sắp hết hạn trong 30 ngày') ?></small><strong><?= esc(number_format(count($expiringPassportVouchers), 0, ',', '.')) ?></strong></div>
                            <div><small><?= esc($locale === 'en' ? 'Nearest expiry' : 'Hạn gần nhất') ?></small><strong><?= esc($nearestPassportExpiry !== null ? date('d/m/Y', $nearestPassportExpiry) : ($locale === 'en' ? 'No active voucher' : 'Chưa có voucher')) ?></strong></div>
                        </aside>
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
                        <div
                            class="row g-3"
                            data-address-selector
                            data-address-source="<?= esc($addressDataUrl, 'attr') ?>"
                            data-selected-ward="<?= esc($selectedWardCode, 'attr') ?>"
                            data-ward-first="<?= esc($locale === 'en' ? 'Select province/city first' : 'Chọn tỉnh/thành phố trước', 'attr') ?>"
                            data-ward-placeholder="<?= esc($locale === 'en' ? 'Select ward/commune' : 'Chọn phường/xã', 'attr') ?>"
                            data-loading="<?= esc($locale === 'en' ? 'Loading address data...' : 'Đang tải dữ liệu địa chỉ...', 'attr') ?>"
                            data-error="<?= esc($locale === 'en' ? 'Address data could not be loaded.' : 'Không thể tải dữ liệu địa chỉ.', 'attr') ?>">
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($t('auth.profile.fullName')) ?></label>
                                    <input type="text" name="full_name" value="<?= esc((string) ($user['full_name'] ?? '')) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($t('auth.profile.phone')) ?></label>
                                    <input type="tel" name="phone" value="<?= esc((string) ($user['phone'] ?? '')) ?>" autocomplete="tel" inputmode="tel" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($locale === 'en' ? 'Province/City' : 'Tỉnh/Thành phố') ?></label>
                                    <select name="province_code" required data-address-province>
                                        <option value=""><?= esc($locale === 'en' ? 'Select province/city' : 'Chọn tỉnh/thành phố') ?></option>
                                        <?php foreach ($administrativeProvinces as $province): ?>
                                            <option
                                                value="<?= esc((string) ($province['code'] ?? ''), 'attr') ?>"
                                                <?= $selectedProvinceCode === (string) ($province['code'] ?? '') ? 'selected' : '' ?>>
                                                <?= esc((string) ($province['name'] ?? '')) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label><?= esc($locale === 'en' ? 'Ward/Commune' : 'Phường/Xã') ?></label>
                                    <select name="ward_code" required disabled data-address-ward>
                                        <option value=""><?= esc($locale === 'en' ? 'Select province/city first' : 'Chọn tỉnh/thành phố trước') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-inner">
                                    <label><?= esc($locale === 'en' ? 'Street address' : 'Số nhà, tên đường') ?></label>
                                    <input type="text" name="address_line" value="<?= esc($selectedAddressLine) ?>" autocomplete="street-address" maxlength="255" required data-address-line>
                                    <small class="travelplus-address-status" data-address-status aria-live="polite"></small>
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
                <section id="passport-wallet" class="travelplus-account-card travelplus-passport-rewards travelplus-profile-voucher-center" aria-labelledby="passport-rewards-title" data-passport-rewards>
                    <div class="travelplus-passport-rewards__head">
                        <div>
                            <span><i class="bi bi-passport-fill" aria-hidden="true"></i> TravelPlus Passport</span>
                            <h2 id="passport-rewards-title"><?= esc($locale === 'en' ? 'Vouchers and mile redemption' : 'Ví voucher & đổi Dặm') ?></h2>
                            <p><?= esc($locale === 'en'
                                ? 'Use an available voucher first, or redeem more with your available miles. Redeemed vouchers are valid for 180 days.'
                                : 'Ưu tiên dùng voucher đang có, hoặc đổi thêm bằng Dặm khả dụng. Voucher sau khi đổi có hạn 180 ngày.') ?></p>
                        </div>
                        <span class="travelplus-passport-rewards__balance">
                            <small><?= esc($locale === 'en' ? 'Available' : 'Hiện có') ?></small>
                            <strong><?= esc(number_format($membershipPoints, 0, ',', '.')) ?></strong>
                            <em><?= esc($locale === 'en' ? 'miles' : 'dặm') ?></em>
                        </span>
                    </div>

                    <div class="travelplus-profile-voucher-wallet">
                        <div class="travelplus-profile-voucher-wallet__head">
                            <div>
                                <span><i class="bi bi-wallet2" aria-hidden="true"></i><?= esc($locale === 'en' ? 'My voucher wallet' : 'Ví voucher của tôi') ?></span>
                                <strong><?= esc(count($readyPassportVouchers) > 0
                                    ? number_format(count($readyPassportVouchers), 0, ',', '.') . ($locale === 'en' ? ' ready to use' : ' voucher sẵn sàng dùng')
                                    : ($locale === 'en' ? 'No voucher ready to use' : 'Chưa có voucher sẵn sàng')) ?></strong>
                            </div>
                            <?php if ($expiringPassportVouchers !== []): ?>
                                <em><i class="bi bi-alarm" aria-hidden="true"></i><?= esc(number_format(count($expiringPassportVouchers), 0, ',', '.')) ?> <?= esc($locale === 'en' ? 'expiring soon' : 'sắp hết hạn') ?></em>
                            <?php endif; ?>
                        </div>

                        <?php if ($rewardVouchers === []): ?>
                            <div class="travelplus-profile-voucher-wallet__empty">
                                <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                                <span><strong><?= esc($locale === 'en' ? 'Your voucher wallet is empty' : 'Ví voucher đang trống') ?></strong><small><?= esc($locale === 'en' ? 'Choose a reward below when you have enough available miles.' : 'Chọn một mức đổi bên dưới khi bạn có đủ Dặm khả dụng.') ?></small></span>
                            </div>
                        <?php else: ?>
                            <div class="travelplus-profile-voucher-wallet__grid">
                                <?php foreach ($rewardVouchers as $voucher): ?>
                                    <?php
                                    $voucherStatus = strtolower((string) ($voucher['status'] ?? 'issued'));
                                    $voucherUsed = $voucherStatus === 'used' || (int) ($voucher['used_count'] ?? 0) > 0;
                                    $voucherReserved = ! $voucherUsed && $voucherStatus === 'reserved';
                                    $voucherExpiryTimestamp = strtotime((string) ($voucher['expires_at'] ?? '')) ?: 0;
                                    $voucherExpired = ! $voucherUsed && $voucherExpiryTimestamp < $nowTimestamp;
                                    $voucherInactive = ! $voucherUsed && ! $voucherExpired && ($voucherReserved || (int) ($voucher['is_active'] ?? 0) !== 1);
                                    $voucherExpiringSoon = ! $voucherUsed && ! $voucherInactive && $voucherExpiryTimestamp <= strtotime('+30 days', $nowTimestamp);
                                    if ($voucherUsed) {
                                        $voucherStatusLabel = $locale === 'en' ? 'Used' : 'Đã dùng';
                                        $voucherStatusIcon = 'bi-check2-circle';
                                    } elseif ($voucherReserved) {
                                        $voucherStatusLabel = $locale === 'en' ? 'Held for booking' : 'Đang giữ cho booking';
                                        $voucherStatusIcon = 'bi-lock-fill';
                                    } elseif ($voucherExpired) {
                                        $voucherStatusLabel = $locale === 'en' ? 'Expired' : 'Hết hạn';
                                        $voucherStatusIcon = 'bi-clock-history';
                                    } elseif ($voucherInactive) {
                                        $voucherStatusLabel = $locale === 'en' ? 'Paused' : 'Tạm dừng';
                                        $voucherStatusIcon = 'bi-pause-circle';
                                    } else {
                                        $voucherStatusLabel = $locale === 'en' ? 'Ready to use' : 'Sẵn sàng dùng';
                                        $voucherStatusIcon = 'bi-check-circle-fill';
                                    }
                                    ?>
                                    <article class="travelplus-profile-voucher<?= ($voucherUsed || $voucherExpired || $voucherInactive) ? ' is-disabled' : '' ?><?= $voucherExpiringSoon ? ' is-expiring' : '' ?>">
                                        <div class="travelplus-profile-voucher__value">
                                            <span><i class="bi bi-ticket-perforated-fill" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Tour voucher' : 'Voucher tour') ?></span>
                                            <strong><?= esc(number_format((int) ($voucher['voucher_amount_vnd'] ?? 0), 0, ',', '.')) ?><sup>đ</sup></strong>
                                        </div>
                                        <div class="travelplus-profile-voucher__meta">
                                            <span class="travelplus-profile-voucher__status"><i class="bi <?= esc($voucherStatusIcon, 'attr') ?>" aria-hidden="true"></i><?= esc($voucherStatusLabel) ?></span>
                                            <span class="travelplus-profile-voucher__expiry<?= $voucherExpiringSoon ? ' is-warning' : '' ?>"><i class="bi bi-calendar-event" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Expires ' : 'Hạn dùng ') ?><?= esc(app_datetime((string) ($voucher['expires_at'] ?? ''), 'd/m/Y', '-')) ?></span>
                                        </div>
                                        <div class="travelplus-profile-voucher__code"><small><?= esc($locale === 'en' ? 'Voucher code' : 'Mã voucher') ?></small><strong><?= esc((string) ($voucher['code'] ?? '')) ?></strong></div>
                                        <small class="travelplus-profile-voucher__condition"><?= esc(($locale === 'en' ? 'For bookings from ' : 'Áp dụng booking từ ') . number_format((int) ($voucher['min_order_vnd'] ?? 0), 0, ',', '.') . 'đ') ?></small>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="travelplus-profile-redeem-head">
                        <span><?= esc($locale === 'en' ? 'Redeem more vouchers' : 'Đổi thêm voucher') ?></span>
                        <small><?= esc($locale === 'en' ? 'Single-use voucher · valid for 180 days' : 'Voucher dùng một lần · hiệu lực 180 ngày') ?></small>
                    </div>
                    <div class="travelplus-passport-reward-grid">
                        <?php foreach ($rewardCatalog as $rewardIndex => $reward): ?>
                            <?php $canRedeem = $rewardsAvailable && (bool) ($reward['available'] ?? false); ?>
                            <article class="travelplus-passport-reward travelplus-passport-reward--<?= esc((string) ((int) $rewardIndex + 1), 'attr') ?><?= $canRedeem ? ' is-available' : '' ?>">
                                <div class="travelplus-passport-reward__topline">
                                    <span><i class="bi bi-ticket-perforated-fill" aria-hidden="true"></i> <?= esc($locale === 'en' ? 'Tour voucher' : 'Voucher tour') ?></span>
                                    <em><?= esc($locale === 'en' ? 'Single use' : '1 lần dùng') ?></em>
                                </div>
                                <span class="travelplus-passport-reward__value"><?= esc(number_format((int) ($reward['amount_vnd'] ?? 0), 0, ',', '.')) ?><sup>đ</sup></span>
                                <strong><?= esc(number_format((int) ($reward['points'] ?? 0), 0, ',', '.')) ?> <?= esc($locale === 'en' ? 'miles' : 'dặm') ?></strong>
                                <small><?= esc(($locale === 'en' ? 'For bookings from ' : 'Booking từ ') . number_format((int) ($reward['min_order_vnd'] ?? 0), 0, ',', '.') . 'đ') ?></small>
                                <form method="post" action="<?= esc(site_url(($locale === 'en' ? 'en/' : '') . 'account/passport/redeem')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="reward_key" value="<?= esc((string) ($reward['key'] ?? ''), 'attr') ?>">
                                    <button type="submit"<?= $canRedeem ? '' : ' disabled' ?> onclick="return confirm('<?= esc($locale === 'en' ? 'Redeem this voucher now?' : 'Dùng Dặm Hành Trình để đổi voucher này?', 'attr') ?>')">
                                        <?= esc($canRedeem
                                            ? ($locale === 'en' ? 'Redeem voucher →' : 'Đổi voucher →')
                                            : (($reward['points_needed'] ?? 0) > 0
                                                ? ($locale === 'en' ? 'Need ' : 'Cần thêm ') . number_format((int) $reward['points_needed'], 0, ',', '.')
                                                : ($locale === 'en' ? 'Coming soon' : 'Sắp mở'))) ?>
                                    </button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>

                </section>

                <details class="travelplus-account-card travelplus-loyalty-history">
                    <summary>
                        <span class="travelplus-loyalty-history-icon" aria-hidden="true">
                            <i class="bi bi-clock-history"></i>
                        </span>
                        <span class="travelplus-loyalty-history-heading">
                            <strong><?= esc($locale === 'en' ? 'Journey Miles history' : 'Lịch sử Dặm Hành Trình') ?></strong>
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
                                        'voucher_redeemed' => $locale === 'en' ? 'Passport voucher redeemed' : 'Đổi voucher Passport',
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
<script>
(() => {
    const passportRewards = document.querySelector('[data-passport-rewards]');
    const profileForm = document.querySelector('.travelplus-account-profile-form');
    if (passportRewards && profileForm) {
        profileForm.before(passportRewards);
    }
})();
</script>
<script defer src="<?= esc(frontend_asset_url('assets/js/address-selector.js'), 'attr') ?>"></script>
<?= $this->endSection() ?>
