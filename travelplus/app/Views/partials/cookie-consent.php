<?php
$locale = service('request')->getLocale() ?: 'vi';
$privacyUrl = \App\Data\LocalizedPathCatalog::url('legal.privacy', $locale);
$termsUrl = \App\Data\LocalizedPathCatalog::url('legal.terms', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Privacy preferences',
        'title' => 'Travel Plus uses cookies',
        'body' => 'We use necessary cookies to run the website and optional cookies to understand traffic, improve service quality and support marketing campaigns.',
        'accept' => 'Accept all',
        'reject' => 'Reject optional',
        'customize' => 'Customize',
        'save' => 'Save choices',
        'necessary' => 'Necessary',
        'necessaryDesc' => 'Required for session, sign-in, booking, security and checkout.',
        'analytics' => 'Analytics',
        'analyticsDesc' => 'Helps Travel Plus understand page views, searches and user journeys.',
        'marketing' => 'Marketing',
        'marketingDesc' => 'Used for advertising, remarketing and campaign measurement when enabled.',
        'preferences' => 'Preferences',
        'preferencesDesc' => 'Keeps useful interface preferences for a smoother experience.',
        'alwaysOn' => 'Always on',
        'privacy' => 'Privacy Statement',
        'terms' => 'Terms of Service',
    ]
    : [
        'eyebrow' => 'Tùy chọn riêng tư',
        'title' => 'Travel Plus sử dụng cookie',
        'body' => 'Chúng tôi dùng cookie cần thiết để website hoạt động và cookie tùy chọn để phân tích lượt truy cập, cải thiện dịch vụ và hỗ trợ chiến dịch marketing.',
        'accept' => 'Chấp nhận tất cả',
        'reject' => 'Từ chối tùy chọn',
        'customize' => 'Tùy chỉnh',
        'save' => 'Lưu lựa chọn',
        'necessary' => 'Cần thiết',
        'necessaryDesc' => 'Bắt buộc cho phiên đăng nhập, booking, bảo mật và thanh toán.',
        'analytics' => 'Phân tích',
        'analyticsDesc' => 'Giúp Travel Plus hiểu lượt xem trang, nội dung tìm kiếm và hành trình khách hàng.',
        'marketing' => 'Marketing',
        'marketingDesc' => 'Dùng cho quảng cáo, remarketing và đo lường chiến dịch khi được bật.',
        'preferences' => 'Tiện ích',
        'preferencesDesc' => 'Ghi nhớ một số tùy chọn giao diện để trải nghiệm mượt hơn.',
        'alwaysOn' => 'Luôn bật',
        'privacy' => 'Chính sách bảo mật',
        'terms' => 'Điều khoản sử dụng',
    ];
?>
<div class="tp-cookie-consent" data-cookie-consent hidden>
    <div class="tp-cookie-consent__panel" role="dialog" aria-live="polite" aria-label="<?= esc($copy['title'], 'attr') ?>">
        <div class="tp-cookie-consent__main">
            <span class="tp-cookie-consent__eyebrow"><?= esc($copy['eyebrow']) ?></span>
            <h2><?= esc($copy['title']) ?></h2>
            <p><?= esc($copy['body']) ?></p>
            <div class="tp-cookie-consent__links">
                <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($copy['privacy']) ?></a>
                <a href="<?= esc($termsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($copy['terms']) ?></a>
            </div>
        </div>

        <div class="tp-cookie-consent__choices" data-cookie-choices hidden>
            <div class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['necessary']) ?></strong>
                    <span><?= esc($copy['necessaryDesc']) ?></span>
                </div>
                <em><?= esc($copy['alwaysOn']) ?></em>
            </div>
            <label class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['analytics']) ?></strong>
                    <span><?= esc($copy['analyticsDesc']) ?></span>
                </div>
                <input type="checkbox" value="analytics" data-cookie-category>
            </label>
            <label class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['marketing']) ?></strong>
                    <span><?= esc($copy['marketingDesc']) ?></span>
                </div>
                <input type="checkbox" value="marketing" data-cookie-category>
            </label>
            <label class="tp-cookie-choice">
                <div>
                    <strong><?= esc($copy['preferences']) ?></strong>
                    <span><?= esc($copy['preferencesDesc']) ?></span>
                </div>
                <input type="checkbox" value="preferences" data-cookie-category>
            </label>
        </div>

        <div class="tp-cookie-consent__actions">
            <button type="button" class="tp-cookie-btn tp-cookie-btn--primary" data-cookie-accept><?= esc($copy['accept']) ?></button>
            <button type="button" class="tp-cookie-btn tp-cookie-btn--ghost" data-cookie-reject><?= esc($copy['reject']) ?></button>
            <button type="button" class="tp-cookie-btn tp-cookie-btn--link" data-cookie-customize><?= esc($copy['customize']) ?></button>
            <button type="button" class="tp-cookie-btn tp-cookie-btn--primary" data-cookie-save hidden><?= esc($copy['save']) ?></button>
        </div>
    </div>
</div>
