<?php
$passportSummary = is_array($passportSummary ?? null) ? $passportSummary : [];
$locale = ($locale ?? service('request')->getLocale()) === 'en' ? 'en' : 'vi';

if (! (bool) ($passportSummary['visible'] ?? false)) {
    return;
}

$state = (string) ($passportSummary['state'] ?? 'pending');
$earnedPoints = max(0, (int) ($passportSummary['earned_points'] ?? 0));
$reversedPoints = max(0, (int) ($passportSummary['reversed_points'] ?? 0));
$previewPoints = max(0, (int) ($passportSummary['preview_points'] ?? 0));
$availablePoints = max(0, (int) ($passportSummary['available_points'] ?? 0));
$qualifyingPoints = max(0, (int) ($passportSummary['qualifying_points'] ?? 0));
$progress = min(100, max(0, (int) ($passportSummary['progress'] ?? 0)));
$remainingPoints = max(0, (int) ($passportSummary['remaining_points'] ?? 0));
$currentTier = is_array($passportSummary['current_tier'] ?? null) ? $passportSummary['current_tier'] : ['key' => 'member'];
$nextTier = is_array($passportSummary['next_tier'] ?? null) ? $passportSummary['next_tier'] : null;
$currentTierKey = (string) ($currentTier['key'] ?? 'member');
$bookingTierKey = (string) ($passportSummary['membership_tier_key'] ?? 'member');
$earnedTierKey = (string) ($passportSummary['booking_tier_after_key'] ?? $currentTierKey);
$membershipDiscount = max(0, (float) ($passportSummary['membership_discount_amount_vnd'] ?? 0));
$membershipRate = min(3.0, max(0, (float) ($passportSummary['membership_discount_rate'] ?? 0)));
$voucher = is_array($passportSummary['voucher'] ?? null) ? $passportSummary['voucher'] : null;
$voucherDiscount = max(0, (float) ($passportSummary['coupon_discount_amount_vnd'] ?? 0));
$totalBenefit = max(0, (float) ($passportSummary['total_benefit_vnd'] ?? 0));
$tierLabels = $locale === 'en'
    ? ['member' => 'Member', 'silver' => 'Silver', 'gold' => 'Gold', 'diamond' => 'Diamond', 'signature' => 'Signature']
    : ['member' => 'Thành viên', 'silver' => 'Bạc', 'gold' => 'Vàng', 'diamond' => 'Kim Cương', 'signature' => 'Signature'];
$tierIcons = ['member' => 'bi-person-badge', 'silver' => 'bi-shield-fill-check', 'gold' => 'bi-award-fill', 'diamond' => 'bi-gem', 'signature' => 'bi-stars'];
$pointsShown = match ($state) {
    'earned' => $earnedPoints,
    'reversed' => $reversedPoints > 0 ? $reversedPoints : $earnedPoints,
    default => $previewPoints,
};
$headline = match ($state) {
    'earned' => $locale === 'en' ? 'Journey Miles added' : 'Dặm Hành Trình đã được cộng',
    'reversed' => $locale === 'en' ? 'Journey Miles adjusted' : 'Dặm của booking đã được điều chỉnh',
    default => $locale === 'en' ? 'Journey Miles pending' : 'Dặm đang chờ ghi nhận',
};
$lead = match ($state) {
    'earned' => $locale === 'en'
        ? 'Your payment has been recorded and the miles are now available in your Passport account.'
        : 'Thanh toán đã được ghi nhận và Dặm đã có trong tài khoản Passport của bạn.',
    'reversed' => $locale === 'en'
        ? 'Miles from this booking were removed after its payment status changed.'
        : 'Dặm từ booking này đã được trừ lại sau khi trạng thái thanh toán thay đổi.',
    default => $locale === 'en'
        ? 'Miles will be added after Travel Plus confirms the payment.'
        : 'Dặm sẽ được cộng sau khi Travel Plus xác nhận thanh toán.',
};
$voucherStatus = '';
if ($voucher !== null) {
    $voucherStatus = match (true) {
        $state === 'reversed' && (string) ($voucher['status'] ?? '') === 'issued' => $locale === 'en' ? 'Returned to wallet' : 'Đã hoàn lại ví',
        (string) ($voucher['status'] ?? '') === 'used' => $locale === 'en' ? 'Applied' : 'Đã áp dụng',
        (string) ($voucher['status'] ?? '') === 'reserved' => $locale === 'en' ? 'Held for booking' : 'Đang giữ cho booking',
        default => $locale === 'en' ? 'Available in wallet' : 'Có trong ví voucher',
    };
}
?>
<section class="booking-passport-breakdown booking-passport-breakdown--<?= esc($state, 'attr') ?> booking-passport-breakdown--tier-<?= esc($currentTierKey, 'attr') ?>" aria-labelledby="booking-passport-title">
    <header class="booking-passport-breakdown__head">
        <span class="booking-passport-breakdown__brand"><i class="bi bi-passport-fill" aria-hidden="true"></i><span>TravelPlus<strong>Passport</strong></span></span>
        <div>
            <span><?= esc($locale === 'en' ? 'Membership summary for this booking' : 'Tổng kết thành viên của booking') ?></span>
            <h2 id="booking-passport-title"><?= esc($headline) ?></h2>
            <p><?= esc($lead) ?></p>
        </div>
        <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('auth.profile', $locale), 'attr') ?>"><?= esc($locale === 'en' ? 'Open my Passport' : 'Mở Passport của tôi') ?><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </header>

    <?php if ((bool) ($passportSummary['tier_up'] ?? false)): ?>
        <div class="booking-passport-breakdown__tier-up booking-passport-breakdown__tier-up--<?= esc($earnedTierKey, 'attr') ?>">
            <span><i class="bi <?= esc($tierIcons[$earnedTierKey] ?? 'bi-award-fill', 'attr') ?>" aria-hidden="true"></i></span>
            <div><small><?= esc($locale === 'en' ? 'New milestone reached' : 'Cột mốc mới đã mở khóa') ?></small><strong><?= esc($locale === 'en' ? 'Congratulations! You reached ' : 'Chúc mừng! Bạn đã đạt hạng ') ?><?= esc($tierLabels[$earnedTierKey] ?? ucfirst($earnedTierKey)) ?></strong><p><?= esc($locale === 'en' ? 'Your tier welcome benefit has been added to your voucher wallet.' : 'Quyền lợi chào hạng đã được đưa vào ví voucher của bạn.') ?></p></div>
        </div>
    <?php endif; ?>

    <div class="booking-passport-breakdown__main">
        <article class="booking-passport-mile-card">
            <span class="booking-passport-mile-card__icon"><i class="bi <?= $state === 'reversed' ? 'bi-arrow-counterclockwise' : 'bi-stars' ?>" aria-hidden="true"></i></span>
            <div>
                <small><?= esc(match ($state) {
                    'earned' => $locale === 'en' ? 'Earned from this payment' : 'Nhận từ lần thanh toán này',
                    'reversed' => $locale === 'en' ? 'Adjusted from this booking' : 'Đã điều chỉnh từ booking',
                    default => $locale === 'en' ? 'Expected after confirmation' : 'Dự kiến sau khi xác nhận',
                }) ?></small>
                <strong><?= $state === 'reversed' ? '-' : '+' ?><?= esc(number_format($pointsShown, 0, ',', '.')) ?><span><?= esc($locale === 'en' ? ' miles' : ' Dặm') ?></span></strong>
                <p><i class="bi bi-wallet2" aria-hidden="true"></i><?= esc($locale === 'en' ? 'Current available balance: ' : 'Số dư khả dụng hiện tại: ') ?><b><?= esc(number_format($availablePoints, 0, ',', '.')) ?> <?= esc($locale === 'en' ? 'miles' : 'Dặm') ?></b></p>
            </div>
        </article>

        <article class="booking-passport-tier-progress booking-passport-tier-progress--<?= esc((string) ($nextTier['key'] ?? $currentTierKey), 'attr') ?>">
            <div class="booking-passport-tier-progress__top">
                <span><i class="bi <?= esc($tierIcons[$currentTierKey] ?? 'bi-person-badge', 'attr') ?>" aria-hidden="true"></i></span>
                <div><small><?= esc($locale === 'en' ? 'Current tier' : 'Hạng hiện tại') ?></small><strong><?= esc($tierLabels[$currentTierKey] ?? ucfirst($currentTierKey)) ?></strong></div>
                <?php if ($nextTier !== null): ?><b><?= esc($locale === 'en' ? '' : 'Còn ') ?><?= esc(number_format($remainingPoints, 0, ',', '.')) ?> <?= esc($locale === 'en' ? 'miles to ' : 'Dặm để đạt ') ?><?= esc($tierLabels[(string) ($nextTier['key'] ?? '')] ?? '') ?></b><?php endif; ?>
            </div>
            <span class="booking-passport-tier-progress__track" role="progressbar" aria-label="<?= esc($locale === 'en' ? 'Tier progress' : 'Tiến độ lên hạng', 'attr') ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= esc((string) $progress, 'attr') ?>"><i style="width:<?= esc((string) $progress, 'attr') ?>%"></i></span>
            <div class="booking-passport-tier-progress__foot"><span><strong><?= esc(number_format($qualifyingPoints, 0, ',', '.')) ?></strong> <?= esc($locale === 'en' ? 'qualifying miles' : 'Dặm xét hạng') ?></span><b><?= esc((string) $progress) ?>%</b></div>
        </article>
    </div>

    <div class="booking-passport-benefits">
        <header><span><?= esc($locale === 'en' ? 'Passport benefits used' : 'Quyền lợi Passport đã dùng') ?></span><?php if ($totalBenefit > 0): ?><strong><?= esc($locale === 'en' ? 'Total saved ' : 'Tổng tiết kiệm ') ?><?= esc(number_format($totalBenefit, 0, ',', '.')) ?>đ</strong><?php endif; ?></header>
        <div class="booking-passport-benefits__grid">
            <article class="<?= $membershipDiscount > 0 ? 'is-applied' : 'is-empty' ?>">
                <i class="bi bi-percent" aria-hidden="true"></i>
                <span><small><?= esc($locale === 'en' ? 'Tier saving' : 'Giảm theo hạng') ?></small><strong><?= esc($tierLabels[$bookingTierKey] ?? ucfirst($bookingTierKey)) ?><?= $membershipRate > 0 ? ' · ' . esc(rtrim(rtrim(number_format($membershipRate, 2, ',', '.'), '0'), ',')) . '%' : '' ?></strong><em><?= $membershipDiscount > 0 ? '-' . esc(number_format($membershipDiscount, 0, ',', '.')) . 'đ' : esc($locale === 'en' ? 'Not applied' : 'Không áp dụng') ?></em></span>
            </article>
            <article class="<?= $voucher !== null && $voucherDiscount > 0 ? 'is-applied' : 'is-empty' ?>">
                <i class="bi bi-ticket-perforated-fill" aria-hidden="true"></i>
                <span><small><?= esc($locale === 'en' ? 'Passport voucher' : 'Voucher thành viên') ?></small><strong><?= esc($voucher !== null ? ((string) ($voucher['code'] ?? '') ?: ($locale === 'en' ? 'Member voucher' : 'Voucher thành viên')) : ($locale === 'en' ? 'No voucher used' : 'Không dùng voucher')) ?></strong><em><?= $voucher !== null && $voucherDiscount > 0 ? '-' . esc(number_format($voucherDiscount, 0, ',', '.')) . 'đ · ' . esc($voucherStatus) : esc($locale === 'en' ? 'No voucher discount' : 'Không có khoản giảm voucher') ?></em></span>
            </article>
        </div>
    </div>
</section>
