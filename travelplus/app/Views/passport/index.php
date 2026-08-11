<?php
$locale = ($locale ?? service('request')->getLocale()) === 'en' ? 'en' : 'vi';
$isMember = is_array($authUser ?? null) && (int) ($authUser['id'] ?? 0) > 0;
$profileUrl = \App\Data\LocalizedPathCatalog::url('auth.profile', $locale);
$loginUrl = \App\Data\LocalizedPathCatalog::url('auth.login', $locale)
    . '?return_to=' . rawurlencode($profileUrl);
$primaryUrl = $isMember ? $profileUrl : $loginUrl;
$currentTierKey = (string) ($headerMembership['tier_key'] ?? '');

$copy = $locale === 'en'
    ? [
        'eyebrow' => 'TravelPlus Passport',
        'title' => 'Every journey unlocks more',
        'lead' => 'Earn Journey Miles when you complete a paid tour, move through five membership tiers and enjoy clearer benefits at every milestone.',
        'primary' => $isMember ? 'View my Passport' : 'Join for free',
        'secondary' => 'Explore tours',
        'earnValue' => '1 mile / 10,000 VND',
        'earnLabel' => 'on eligible paid tour value',
        'tierValue' => '5 membership tiers',
        'tierLabel' => 'from Member to Signature',
        'expiryValue' => '180 days',
        'expiryLabel' => 'for redeemed vouchers',
        'tiersEyebrow' => 'Membership tiers',
        'tiersTitle' => 'Earn miles, unlock more value',
        'tiersLead' => 'Every member receives the same Travel Plus service standard. Passport tiers add financial benefits through welcome vouchers and capped automatic tour savings.',
        'currentBadge' => 'Current tier',
        'from' => 'From',
        'miles' => 'qualifying miles',
        'welcome' => 'Tier welcome benefit',
        'bookingFrom' => 'Booking from',
        'once' => 'One welcome voucher when first reaching the tier',
        'memberRewardLabel' => 'Join Passport',
        'memberRewardValue' => 'Free',
        'memberRewardHint' => 'Earn miles from your first eligible booking',
        'sharedTitle' => 'Included for every member',
        'sharedBenefits' => ['Earn 1 Journey Mile per 10,000 VND', 'Redeem available miles for tour vouchers', 'The same support standard before, during and after every tour'],
        'tierDiscount' => 'Automatic tour saving',
        'tierDiscountSuffix' => 'off every tour',
        'tierDiscountCap' => 'Up to',
        'noTierDiscount' => 'Starts from Silver',
        'memberNote' => 'No qualifying-mile threshold',
        'rewardsEyebrow' => 'Redeem miles',
        'rewardsTitle' => 'Choose a voucher for your next tour',
        'rewardsLead' => 'Available miles can be exchanged for a single-use voucher valid for 180 days.',
        'howEyebrow' => 'How it works',
        'howTitle' => 'Simple from booking to benefits',
        'termsTitle' => 'Important conditions',
        'ctaTitle' => 'Ready to start your Passport?',
        'ctaText' => 'Create an account, book an eligible tour and your Journey Miles will be credited after payment is confirmed.',
        'cardTierLabel' => 'Membership tier',
        'cardTierCompactLabel' => 'Current tier',
        'cardMilesLabel' => 'qualifying miles',
        'cardMilesUnit' => 'miles',
        'cardMemberLabel' => 'TravelPlus member',
        'cardStatus' => 'Active',
        'cardProgressLabel' => 'Tier progress',
        'cardNextTier' => 'To %s',
        'cardRemainingMiles' => '%s miles remaining',
        'cardHighestTier' => 'Highest tier achieved',
    ]
    : [
        'eyebrow' => 'TravelPlus Passport',
        'title' => 'Mỗi hành trình, thêm nhiều quyền lợi',
        'lead' => 'Tích Dặm Hành Trình sau khi hoàn tất thanh toán tour, nâng dần qua 5 hạng thành viên và nhận quyền lợi rõ ràng ở từng cột mốc.',
        'primary' => $isMember ? 'Xem Passport của tôi' : 'Tham gia miễn phí',
        'secondary' => 'Khám phá tour',
        'earnValue' => '1 dặm / 10.000đ',
        'earnLabel' => 'trên giá trị tour đủ điều kiện',
        'tierValue' => '5 hạng thành viên',
        'tierLabel' => 'từ Thành viên đến Signature',
        'expiryValue' => '180 ngày',
        'expiryLabel' => 'thời hạn voucher đã đổi',
        'tiersEyebrow' => 'Hạng thành viên',
        'tiersTitle' => 'Tích Dặm, mở thêm giá trị',
        'tiersLead' => 'Mọi thành viên đều được phục vụ theo cùng một tiêu chuẩn Travel Plus. Hạng Passport tăng quyền lợi tài chính qua voucher chào hạng và mức giảm tour tự động có giới hạn.',
        'currentBadge' => 'Hạng hiện tại',
        'from' => 'Từ',
        'miles' => 'Dặm xét hạng',
        'welcome' => 'Quyền lợi chào hạng',
        'bookingFrom' => 'Booking từ',
        'once' => '01 voucher chào hạng khi lần đầu đạt hạng',
        'memberRewardLabel' => 'Tham gia Passport',
        'memberRewardValue' => 'Miễn phí',
        'memberRewardHint' => 'Tích Dặm từ booking đủ điều kiện đầu tiên',
        'sharedTitle' => 'Áp dụng cho mọi thành viên',
        'sharedBenefits' => ['Tích 1 Dặm cho mỗi 10.000đ', 'Đổi Dặm khả dụng lấy voucher tour', 'Cùng một tiêu chuẩn hỗ trợ trước, trong và sau tour'],
        'tierDiscount' => 'Ưu đãi tự động mỗi tour',
        'tierDiscountSuffix' => 'giảm trên giá tour',
        'tierDiscountCap' => 'Tối đa',
        'noTierDiscount' => 'Bắt đầu từ hạng Bạc',
        'memberNote' => 'Không yêu cầu mốc Dặm xét hạng',
        'rewardsEyebrow' => 'Đổi Dặm',
        'rewardsTitle' => 'Chọn voucher cho chuyến đi tiếp theo',
        'rewardsLead' => 'Dặm khả dụng có thể đổi thành voucher dùng một lần, hiệu lực trong 180 ngày.',
        'howEyebrow' => 'Cách hoạt động',
        'howTitle' => 'Đơn giản từ lúc đặt tour đến khi nhận quyền lợi',
        'termsTitle' => 'Điều kiện quan trọng',
        'ctaTitle' => 'Sẵn sàng bắt đầu Passport?',
        'ctaText' => 'Tạo tài khoản, đặt tour đủ điều kiện và Dặm Hành Trình sẽ được cộng sau khi thanh toán được xác nhận.',
        'cardTierLabel' => 'Hạng thành viên',
        'cardTierCompactLabel' => 'Hạng hiện tại',
        'cardMilesLabel' => 'Dặm xét hạng',
        'cardMilesUnit' => 'Dặm',
        'cardMemberLabel' => 'Thành viên TravelPlus',
        'cardStatus' => 'Đang hoạt động',
        'cardProgressLabel' => 'Tiến độ lên hạng',
        'cardNextTier' => 'Lên %s',
        'cardRemainingMiles' => 'Còn %s Dặm',
        'cardHighestTier' => 'Đã đạt hạng cao nhất',
    ];

$tiers = $locale === 'en'
    ? [
        ['key' => 'member', 'name' => 'Member', 'points' => 0, 'discount' => 0, 'minimum' => 0, 'rate' => 0, 'cap' => 0, 'icon' => 'bi-person-badge', 'tone' => 'member'],
        ['key' => 'silver', 'name' => 'Silver', 'points' => 5000, 'discount' => 100000, 'minimum' => 3000000, 'rate' => 1, 'cap' => 200000, 'icon' => 'bi-shield-fill-check', 'tone' => 'silver'],
        ['key' => 'gold', 'name' => 'Gold', 'points' => 20000, 'discount' => 200000, 'minimum' => 6000000, 'rate' => 1.5, 'cap' => 400000, 'icon' => 'bi-award-fill', 'tone' => 'gold'],
        ['key' => 'diamond', 'name' => 'Diamond', 'points' => 60000, 'discount' => 300000, 'minimum' => 10000000, 'rate' => 2, 'cap' => 600000, 'icon' => 'bi-gem', 'tone' => 'diamond'],
        ['key' => 'signature', 'name' => 'Signature', 'points' => 150000, 'discount' => 500000, 'minimum' => 15000000, 'rate' => 3, 'cap' => 1000000, 'icon' => 'bi-stars', 'tone' => 'signature'],
    ]
    : [
        ['key' => 'member', 'name' => 'Thành viên', 'points' => 0, 'discount' => 0, 'minimum' => 0, 'rate' => 0, 'cap' => 0, 'icon' => 'bi-person-badge', 'tone' => 'member'],
        ['key' => 'silver', 'name' => 'Bạc', 'points' => 5000, 'discount' => 100000, 'minimum' => 3000000, 'rate' => 1, 'cap' => 200000, 'icon' => 'bi-shield-fill-check', 'tone' => 'silver'],
        ['key' => 'gold', 'name' => 'Vàng', 'points' => 20000, 'discount' => 200000, 'minimum' => 6000000, 'rate' => 1.5, 'cap' => 400000, 'icon' => 'bi-award-fill', 'tone' => 'gold'],
        ['key' => 'diamond', 'name' => 'Kim Cương', 'points' => 60000, 'discount' => 300000, 'minimum' => 10000000, 'rate' => 2, 'cap' => 600000, 'icon' => 'bi-gem', 'tone' => 'diamond'],
        ['key' => 'signature', 'name' => 'Signature', 'points' => 150000, 'discount' => 500000, 'minimum' => 15000000, 'rate' => 3, 'cap' => 1000000, 'icon' => 'bi-stars', 'tone' => 'signature'],
    ];

$currentCardTier = $tiers[0];
$currentCardTierIndex = 0;
foreach ($tiers as $tierIndex => $tier) {
    if ($tier['key'] === $currentTierKey) {
        $currentCardTier = $tier;
        $currentCardTierIndex = $tierIndex;
        break;
    }
}
$cardTierKey = $isMember ? (string) $currentCardTier['key'] : 'member';
$cardTierName = $isMember
    ? (string) $currentCardTier['name']
    : ($locale === 'en' ? 'Member' : 'Thành viên');
$cardTierIcon = (string) ($currentCardTier['icon'] ?? 'bi-person-badge');
$cardQualifyingMiles = max(0, (int) ($headerMembership['qualifying_points'] ?? 0));
$cardMemberName = $isMember
    ? strtoupper((string) ($authUser['full_name'] ?? $authUser['email'] ?? 'MEMBER'))
    : ($locale === 'en' ? 'YOUR JOURNEY' : 'HÀNH TRÌNH CỦA BẠN');
$nextCardTier = $tiers[$currentCardTierIndex + 1] ?? null;
$nextCardTierPoints = is_array($nextCardTier) ? max(1, (int) ($nextCardTier['points'] ?? 0)) : 0;
$cardRemainingMiles = $nextCardTierPoints > 0 ? max(0, $nextCardTierPoints - $cardQualifyingMiles) : 0;
$cardTierProgress = $nextCardTierPoints > 0
    ? min(100, max(0, ($cardQualifyingMiles / $nextCardTierPoints) * 100))
    : 100;

$rewards = [
    ['points' => 500, 'amount' => 50000, 'minimum' => 2000000],
    ['points' => 1200, 'amount' => 120000, 'minimum' => 5000000],
    ['points' => 2500, 'amount' => 250000, 'minimum' => 10000000],
];
$steps = $locale === 'en'
    ? [['bi-luggage-fill', 'Book a tour', 'Sign in and complete payment for an eligible tour.'], ['bi-stars', 'Earn two mile balances', 'Available miles can be redeemed; qualifying miles determine your tier.'], ['bi-ticket-perforated-fill', 'Use your benefits', 'Redeem a voucher or use an issued tier welcome benefit.']]
    : [['bi-luggage-fill', 'Đặt tour', 'Đăng nhập và hoàn tất thanh toán tour đủ điều kiện.'], ['bi-stars', 'Nhận hai loại số dư Dặm', 'Dặm khả dụng dùng để đổi quà; Dặm xét hạng dùng để xác định hạng.'], ['bi-ticket-perforated-fill', 'Dùng quyền lợi', 'Đổi voucher hoặc sử dụng quyền lợi chào hạng đã được cấp.']];
$terms = $locale === 'en'
    ? ['Tier welcome benefits are issued once when a tier is reached for the first time and remain valid for 365 days.', 'Automatic tier savings are calculated on eligible tour value, subject to the cap shown for each tier, and may be combined with one valid voucher.', 'Minimum booking values apply before the tier voucher is deducted.', 'Passport vouchers are single-use, non-refundable and cannot be exchanged for cash.', 'Reversed or refunded bookings may reduce qualifying and available miles.']
    : ['Quyền lợi chào hạng được cấp một lần khi thành viên lần đầu đạt hạng và có hiệu lực 365 ngày.', 'Mức giảm theo hạng được tính trên giá tour đủ điều kiện, theo trần hiển thị của từng hạng và có thể dùng cùng một voucher hợp lệ.', 'Booking phải đạt giá trị tối thiểu trước khi trừ voucher chào hạng.', 'Voucher Passport dùng một lần, không hoàn tiền và không quy đổi thành tiền mặt.', 'Booking hoàn hoặc hủy có thể làm điều chỉnh Dặm khả dụng và Dặm xét hạng.'];
?>

<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="travelplus-passport-page">
    <section class="travelplus-passport-hero">
        <div class="container">
            <div class="travelplus-passport-hero__copy">
                <span><i class="bi bi-passport-fill" aria-hidden="true"></i><?= esc($copy['eyebrow']) ?></span>
                <h1><?= esc($copy['title']) ?></h1>
                <p><?= esc($copy['lead']) ?></p>
                <div class="travelplus-passport-actions">
                    <a class="travelplus-passport-btn travelplus-passport-btn--primary" href="<?= esc($primaryUrl, 'attr') ?>"><?= esc($copy['primary']) ?><i class="bi bi-arrow-right"></i></a>
                    <a class="travelplus-passport-btn travelplus-passport-btn--ghost" href="<?= esc(\App\Data\LocalizedPathCatalog::url('search', $locale), 'attr') ?>"><?= esc($copy['secondary']) ?></a>
                </div>
            </div>
            <div class="travelplus-passport-card travelplus-passport-card--<?= esc($cardTierKey, 'attr') ?><?= $isMember ? ' is-authenticated' : '' ?>" aria-label="<?= esc($copy['cardTierLabel'] . ': ' . $cardTierName, 'attr') ?>">
                <span class="travelplus-passport-card__glow" aria-hidden="true"></span>
                <div class="travelplus-passport-card__top">
                    <span class="travelplus-passport-card__brand"><i class="bi bi-passport-fill" aria-hidden="true"></i><span>TravelPlus<br><strong>Passport</strong></span></span>
                    <?php if ($isMember): ?>
                        <span class="travelplus-passport-card__status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><?= esc($copy['cardStatus']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="travelplus-passport-card__tier">
                    <span class="travelplus-passport-card__tier-icon"><i class="bi <?= esc($cardTierIcon, 'attr') ?>" aria-hidden="true"></i></span>
                    <span>
                        <small><?= esc($copy['cardTierCompactLabel']) ?></small>
                        <strong><?= esc($cardTierName) ?></strong>
                        <?php if ($isMember): ?>
                            <b><?= number_format($cardQualifyingMiles, 0, ',', '.') ?> <?= esc($copy['cardMilesUnit']) ?></b>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($isMember): ?>
                    <div class="travelplus-passport-card__progress">
                        <span>
                            <small>
                                <?php if (is_array($nextCardTier)): ?>
                                    <?= esc(sprintf($copy['cardNextTier'], (string) $nextCardTier['name'])) ?>
                                <?php else: ?>
                                    <?= esc($copy['cardProgressLabel']) ?>
                                <?php endif; ?>
                            </small>
                            <strong>
                                <?php if (is_array($nextCardTier)): ?>
                                    <?= esc(sprintf(
                                        $copy['cardRemainingMiles'],
                                        number_format($cardRemainingMiles, 0, ',', '.')
                                    )) ?>
                                <?php else: ?>
                                    <?= esc($copy['cardHighestTier']) ?>
                                <?php endif; ?>
                            </strong>
                        </span>
                        <div
                            class="travelplus-passport-card__progress-track"
                            role="progressbar"
                            aria-label="<?= esc($copy['cardProgressLabel'], 'attr') ?>"
                            aria-valuemin="0"
                            aria-valuemax="<?= esc((string) ($nextCardTierPoints > 0 ? $nextCardTierPoints : $cardQualifyingMiles), 'attr') ?>"
                            aria-valuenow="<?= esc((string) $cardQualifyingMiles, 'attr') ?>">
                            <i style="width:<?= esc(number_format($cardTierProgress, 2, '.', ''), 'attr') ?>%"></i>
                        </div>
                        <b>
                            <?= number_format($cardQualifyingMiles, 0, ',', '.') ?>
                            <?php if ($nextCardTierPoints > 0): ?> / <?= number_format($nextCardTierPoints, 0, ',', '.') ?><?php endif; ?>
                            <?= esc($copy['cardMilesUnit']) ?>
                        </b>
                    </div>
                <?php endif; ?>
                <div class="travelplus-passport-card__foot">
                    <span><small><?= esc($copy['cardMemberLabel']) ?></small><strong><?= esc($cardMemberName) ?></strong></span>
                    <span class="travelplus-passport-card__seal" aria-hidden="true">
                        <img src="<?= esc(base_url('assets/images/logo-white.svg'), 'attr') ?>" alt="">
                    </span>
                </div>
            </div>
            <dl class="travelplus-passport-stats">
                <div><dt><?= esc($copy['earnValue']) ?></dt><dd><?= esc($copy['earnLabel']) ?></dd></div>
                <div><dt><?= esc($copy['tierValue']) ?></dt><dd><?= esc($copy['tierLabel']) ?></dd></div>
                <div><dt><?= esc($copy['expiryValue']) ?></dt><dd><?= esc($copy['expiryLabel']) ?></dd></div>
            </dl>
        </div>
    </section>

    <section class="travelplus-passport-section travelplus-passport-tiers" id="membership-tiers">
        <div class="container">
            <header class="travelplus-passport-heading">
                <span><?= esc($copy['tiersEyebrow']) ?></span>
                <h2><?= esc($copy['tiersTitle']) ?></h2>
                <p><?= esc($copy['tiersLead']) ?></p>
            </header>
            <div class="travelplus-passport-shared-benefits">
                <div class="travelplus-passport-shared-benefits__title">
                    <i class="bi bi-heart-fill" aria-hidden="true"></i>
                    <strong><?= esc($copy['sharedTitle']) ?></strong>
                </div>
                <ul>
                    <?php foreach ($copy['sharedBenefits'] as $benefit): ?>
                        <li><i class="bi bi-check2-circle" aria-hidden="true"></i><?= esc($benefit) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="travelplus-passport-tier-grid">
                <?php foreach ($tiers as $tier): ?>
                    <?php $tierRateLabel = rtrim(rtrim(number_format((float) $tier['rate'], 2, ',', '.'), '0'), ','); ?>
                    <article class="travelplus-passport-tier travelplus-passport-tier--<?= esc($tier['tone'], 'attr') ?><?= $currentTierKey === $tier['key'] ? ' is-current' : '' ?>"<?= $currentTierKey === $tier['key'] ? ' aria-current="true"' : '' ?>>
                        <div class="travelplus-passport-tier__top">
                            <div class="travelplus-passport-tier__icon"><i class="bi <?= esc($tier['icon'], 'attr') ?>"></i></div>
                            <?php if ($currentTierKey === $tier['key']): ?>
                                <span class="travelplus-passport-tier__current"><i class="bi bi-check2" aria-hidden="true"></i><?= esc($copy['currentBadge']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3><?= esc($tier['name']) ?></h3>
                        <p class="travelplus-passport-tier__threshold"><?= esc($copy['from']) ?> <strong><?= number_format($tier['points'], 0, ',', '.') ?></strong> <?= esc($copy['miles']) ?></p>
                        <div class="travelplus-passport-tier__reward">
                            <?php if ($tier['discount'] > 0): ?>
                                <span><?= esc($copy['welcome']) ?></span>
                                <strong><?= number_format($tier['discount'], 0, ',', '.') ?>đ</strong>
                                <small><?= esc($copy['bookingFrom']) ?> <?= number_format($tier['minimum'], 0, ',', '.') ?>đ</small>
                            <?php else: ?>
                                <span><?= esc($copy['memberRewardLabel']) ?></span>
                                <strong><?= esc($copy['memberRewardValue']) ?></strong>
                                <small><?= esc($copy['memberRewardHint']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="travelplus-passport-tier__saving<?= $tier['rate'] > 0 ? '' : ' is-empty' ?>">
                            <i class="bi bi-percent" aria-hidden="true"></i>
                            <span>
                                <small><?= esc($copy['tierDiscount']) ?></small>
                                <?php if ($tier['rate'] > 0): ?>
                                    <strong><?= esc($tierRateLabel) ?>% <em><?= esc($copy['tierDiscountSuffix']) ?></em></strong>
                                    <b><?= esc($copy['tierDiscountCap']) ?> <?= number_format($tier['cap'], 0, ',', '.') ?>đ</b>
                                <?php else: ?>
                                    <strong><?= esc($copy['noTierDiscount']) ?></strong>
                                <?php endif; ?>
                            </span>
                        </div>
                        <p class="travelplus-passport-tier__note"><?= esc($tier['discount'] > 0 ? $copy['once'] : $copy['memberNote']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="travelplus-passport-section travelplus-passport-rewards">
        <div class="container">
            <header class="travelplus-passport-heading travelplus-passport-heading--light">
                <span><?= esc($copy['rewardsEyebrow']) ?></span><h2><?= esc($copy['rewardsTitle']) ?></h2><p><?= esc($copy['rewardsLead']) ?></p>
            </header>
            <div class="travelplus-passport-reward-grid">
                <?php foreach ($rewards as $reward): ?>
                    <article><i class="bi bi-ticket-perforated-fill"></i><span><?= number_format($reward['points'], 0, ',', '.') ?> <?= esc($locale === 'en' ? 'miles' : 'Dặm') ?></span><strong><?= number_format($reward['amount'], 0, ',', '.') ?>đ</strong><small><?= esc($copy['bookingFrom']) ?> <?= number_format($reward['minimum'], 0, ',', '.') ?>đ</small></article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="travelplus-passport-section travelplus-passport-how">
        <div class="container travelplus-passport-how__layout">
            <div>
                <header class="travelplus-passport-heading"><span><?= esc($copy['howEyebrow']) ?></span><h2><?= esc($copy['howTitle']) ?></h2></header>
                <ol class="travelplus-passport-steps">
                    <?php foreach ($steps as $index => $step): ?><li><em><?= $index + 1 ?></em><i class="bi <?= esc($step[0], 'attr') ?>"></i><span><strong><?= esc($step[1]) ?></strong><small><?= esc($step[2]) ?></small></span></li><?php endforeach; ?>
                </ol>
            </div>
            <aside class="travelplus-passport-terms"><h3><?= esc($copy['termsTitle']) ?></h3><ul><?php foreach ($terms as $term): ?><li><i class="bi bi-info-circle"></i><?= esc($term) ?></li><?php endforeach; ?></ul></aside>
        </div>
    </section>

    <section class="travelplus-passport-cta">
        <div class="container"><div><span><i class="bi bi-stars"></i>TravelPlus Passport</span><h2><?= esc($copy['ctaTitle']) ?></h2><p><?= esc($copy['ctaText']) ?></p></div><a href="<?= esc($primaryUrl, 'attr') ?>"><?= esc($copy['primary']) ?><i class="bi bi-arrow-right"></i></a></div>
    </section>
</main>

<?= $this->endSection() ?>
