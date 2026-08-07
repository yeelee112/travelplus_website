<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$isMember = is_array($authUser ?? null) && (int) ($authUser['id'] ?? 0) > 0;
$passportUrl = $isMember
    ? \App\Data\LocalizedPathCatalog::url('auth.profile', $locale)
    : \App\Data\LocalizedPathCatalog::url('passport.program', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'TravelPlus Passport',
        'title' => 'Every journey brings the next one closer',
        'desc' => 'Earn 1 Journey Mile for every 10,000 VND paid, then redeem your miles for a voucher on your next tour.',
        'cta' => $isMember ? 'View my Passport' : 'Explore benefits',
        'steps' => [['Book a tour', 'Choose the journey that fits you.'], ['Earn miles', 'Miles are credited after payment.'], ['Redeem vouchers', 'Use your voucher on a future tour.']],
    ]
    : [
        'eyebrow' => 'TravelPlus Passport',
        'title' => 'Mỗi hành trình, gần hơn với chuyến đi tiếp theo',
        'desc' => 'Mỗi 10.000đ thanh toán nhận 1 Dặm Hành Trình, tích dặm để đổi voucher cho tour tiếp theo.',
        'cta' => $isMember ? 'Xem Passport của tôi' : 'Khám phá quyền lợi',
        'steps' => [['Đặt tour', 'Chọn hành trình phù hợp với bạn.'], ['Nhận dặm', 'Dặm được cộng sau khi thanh toán.'], ['Đổi voucher', 'Dùng cho chuyến đi tiếp theo.']],
    ];
$icons = ['bi-luggage-fill', 'bi-stars', 'bi-ticket-perforated-fill'];
?>
<section class="home-passport-section home-section" aria-labelledby="home-passport-title">
    <div class="container">
        <div class="home-passport-panel">
            <div class="home-passport-copy">
                <span><i class="bi bi-passport-fill" aria-hidden="true"></i><?= esc($copy['eyebrow']) ?></span>
                <h2 id="home-passport-title"><?= esc($copy['title']) ?></h2>
                <p><?= esc($copy['desc']) ?></p>
                <a href="<?= esc($passportUrl, 'attr') ?>"><?= esc($copy['cta']) ?><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <ol class="home-passport-steps">
                <?php foreach ($copy['steps'] as $index => $step): ?>
                    <li>
                        <span><i class="bi <?= esc($icons[$index], 'attr') ?>" aria-hidden="true"></i><em><?= esc((string) ($index + 1)) ?></em></span>
                        <div><strong><?= esc($step[0]) ?></strong><small><?= esc($step[1]) ?></small></div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</section>
