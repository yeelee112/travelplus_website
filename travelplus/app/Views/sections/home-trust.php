<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$trustItems = $locale === 'en'
    ? [
        ['bi-shield-check', 'Thoughtful service', 'Carefully prepared for every journey'],
        ['bi-tag', 'Great prices every day', 'Attractive offers are always available'],
        ['bi-headset', 'Dedicated support', 'With you before, during and after'],
        ['bi-bag-check', 'Secure payment', 'Your information is always protected'],
    ]
    : [
        ['bi-shield-check', 'Dịch vụ chỉn chu', 'Tận tâm trong từng hành trình'],
        ['bi-tag', 'Giá tốt mỗi ngày', 'Luôn có ưu đãi hấp dẫn'],
        ['bi-headset', 'Hỗ trợ tận tâm', 'Đồng hành trước – trong – sau tour'],
        ['bi-bag-check', 'Thanh toán an toàn', 'Bảo mật thông tin tuyệt đối'],
    ];
?>
<section class="home-hero-trust-section" aria-label="<?= esc($locale === 'en' ? 'Travel Plus commitments' : 'Cam kết của Travel Plus', 'attr') ?>">
    <div class="container">
        <ul class="home-hero-trust">
            <?php foreach ($trustItems as $trustItem): ?>
                <li>
                    <i class="bi <?= esc($trustItem[0], 'attr') ?>" aria-hidden="true"></i>
                    <span><strong><?= esc($trustItem[1]) ?></strong><small><?= esc($trustItem[2]) ?></small></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
