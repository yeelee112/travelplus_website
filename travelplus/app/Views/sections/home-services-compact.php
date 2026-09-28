<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$isEnglish = $locale === 'en';
$items = [
    ['bi-map', 'customTour', $isEnglish ? 'Your own journey' : 'Tour theo yêu cầu', $isEnglish ? 'A private itinerary for your family or group.' : 'Lịch trình riêng cho gia đình hoặc nhóm bạn.'],
    ['bi-briefcase', 'service.mice', $isEnglish ? 'Travel for your team' : 'Du lịch doanh nghiệp', $isEnglish ? 'Conferences, events and company trips.' : 'Hội nghị, sự kiện và chuyến đi cùng đội ngũ.'],
    ['bi-person-vcard', 'passport.program', 'Travel Plus Reward', $isEnglish ? 'Explore membership benefits and reward vouchers.' : 'Khám phá quyền lợi thành viên và voucher tích điểm.'],
];
?>
<section class="home-services-compact home-section" aria-labelledby="home-services-title">
    <div class="container">
        <div class="home-section-head"><h2 id="home-services-title"><?= $isEnglish ? 'More for your journey' : 'Thêm lựa chọn cho hành trình' ?></h2></div>
        <div class="home-services-compact__grid">
            <?php foreach ($items as [$icon, $route, $title, $description]): ?>
                <a class="home-services-compact__card" href="<?= esc(\App\Data\LocalizedPathCatalog::url($route, $locale), 'attr') ?>">
                    <i class="bi <?= esc($icon, 'attr') ?>" aria-hidden="true"></i>
                    <span><strong><?= esc($title) ?></strong><span><?= esc($description) ?></span></span>
                    <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
