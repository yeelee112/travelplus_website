<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$websiteSettings = new \App\Services\WebsiteSettingsService();
$contactPhone = $websiteSettings->get('hotline_e164');
$contactPhoneDisplay = $websiteSettings->phoneDisplay($locale);
$contactEmail = $websiteSettings->get('email');
$currentAbsoluteUrl = (string) service('request')->getUri();
$isEnglish = $locale === 'en';
$viUrl = switch_locale_url('vi');
$enUrl = switch_locale_url('en');
$currentPath = trim((string) service('request')->getUri()->getPath(), '/');
$normalizeHeaderPath = static function (string $url): string {
    $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
    return trim($path, '/');
};
$isActiveHeaderUrl = static function (string $url) use ($currentPath, $normalizeHeaderPath): bool {
    $path = $normalizeHeaderPath($url);

    if ($path === '') {
        return $currentPath === '';
    }

    return $currentPath === $path || str_starts_with($currentPath . '/', $path . '/');
};
$loginLabel = lang('Frontend.auth.login');
$logoutLabel = lang('Frontend.auth.logout');
$serviceMenuItems = [
    ['label' => lang('Frontend.header.service.airlineTickets'), 'url' => \App\Data\LocalizedPathCatalog::url('service.airlineTickets', $locale)],
    ['label' => lang('Frontend.header.service.transport'), 'url' => \App\Data\LocalizedPathCatalog::url('service.transport', $locale)],
    ['label' => lang('Frontend.header.service.translation'), 'url' => \App\Data\LocalizedPathCatalog::url('service.translation', $locale)],
    ['label' => lang('Frontend.header.service.hotels'), 'url' => \App\Data\LocalizedPathCatalog::url('service.hotels', $locale)],
];
$languageOptions = [
    'en' => [
        'value' => 'en',
        'code' => 'EN',
        'label' => lang('Frontend.language.en'),
        'url' => $enUrl,
        'flag' => base_url('assets/images/home/en-us.svg'),
        'alt' => 'English',
    ],
    'vi' => [
        'value' => 'vi',
        'code' => 'VI',
        'label' => lang('Frontend.language.vi'),
        'url' => $viUrl,
        'flag' => base_url('assets/images/home/vi-vn.svg'),
        'alt' => 'Tiếng Việt',
    ],
];
$currentLanguage = $languageOptions[$locale] ?? $languageOptions['vi'];
$blogUrl = \App\Data\LocalizedPathCatalog::url('blog', $locale);
$aboutUrl = \App\Data\LocalizedPathCatalog::url('about', $locale);
$outboundUrl = \App\Data\LocalizedPathCatalog::url('outbound', $locale);
$domesticUrl = \App\Data\LocalizedPathCatalog::url('domestic', $locale);
$inboundUrl = \App\Data\LocalizedPathCatalog::url('inbound', $locale);
$visaUrl = \App\Data\LocalizedPathCatalog::url('service.visa', $locale);
$miceUrl = \App\Data\LocalizedPathCatalog::url('service.mice', $locale);
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$bookingLookupUrl = \App\Data\LocalizedPathCatalog::url('booking.lookup', $locale);
$bookingLookupLabel = $locale === 'en' ? 'Booking lookup' : 'Tra cứu booking';
$serviceMenuActive = array_reduce(
    $serviceMenuItems,
    static fn (bool $active, array $item): bool => $active || $isActiveHeaderUrl((string) ($item['url'] ?? '')),
    false
);
$profileUrl = \App\Data\LocalizedPathCatalog::url('auth.profile', $locale);
$loginUrl = \App\Data\LocalizedPathCatalog::url('auth.login', $locale) . '?return_to=' . rawurlencode($currentAbsoluteUrl);
$authPrimaryLabel = $authUser
    ? lang('Frontend.auth.profile.menu', [], $locale)
    : $loginLabel;
$logoutUrl = \App\Data\LocalizedPathCatalog::url('auth.logout', $locale);
$headerMemberName = trim((string) ($authUser['full_name'] ?? ''))
    ?: trim((string) ($authUser['username'] ?? ''))
    ?: $authPrimaryLabel;
$headerMemberNameParts = preg_split('/\s+/u', $headerMemberName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$headerMemberInitials = $headerMemberNameParts !== []
    ? mb_strtoupper(
        mb_substr((string) $headerMemberNameParts[0], 0, 1, 'UTF-8')
        . (count($headerMemberNameParts) > 1
            ? mb_substr((string) $headerMemberNameParts[array_key_last($headerMemberNameParts)], 0, 1, 'UTF-8')
            : ''),
        'UTF-8'
    )
    : 'TP';
$headerMemberTier = (string) ($headerMembership['tier_key'] ?? 'member');
$headerMemberTierIcons = [
    'member' => 'bi-person-fill',
    'silver' => 'bi-stars',
    'gold' => 'bi-award-fill',
    'diamond' => 'bi-gem',
    'signature' => 'bi-suit-diamond-fill',
];
$headerMemberTierIcon = $headerMemberTierIcons[$headerMemberTier] ?? $headerMemberTierIcons['member'];
$headerPassportPoints = max(0, (int) ($headerMembership['points'] ?? 0));
$headerPassportNextReward = is_array($headerMembership['next_reward'] ?? null) ? $headerMembership['next_reward'] : null;
$headerPassportRemaining = $headerPassportNextReward !== null
    ? max(0, (int) ($headerPassportNextReward['points'] ?? 0) - $headerPassportPoints)
    : 0;
$headerPassportSubline = $authUser
    ? number_format($headerPassportPoints, 0, ',', '.') . ' ' . ($locale === 'en' ? 'points' : 'điểm')
    : ($locale === 'en' ? 'Earn points · redeem vouchers' : 'Tích điểm · đổi voucher');
$headerPassportUrl = \App\Data\LocalizedPathCatalog::url('passport.program', $locale);
$megaMenuCountryLimit = 7;
$megaMenuMoreLabel = $locale === 'en' ? 'Show %d more' : 'Xem thêm %d quốc gia';
$megaMenuLessLabel = $locale === 'en' ? 'Show less' : 'Thu gọn';
$inboundMenuGroups = [];
if ($locale === 'en') {
    $vietnamRegionKeys = ['north', 'central', 'south', 'mekong'];
    $vietnamRegions = [];
    foreach ($vietnamRegionKeys as $regionKey) {
        if (! empty($domesticMenu[$regionKey])) {
            $vietnamRegions[] = [
                'name' => (string) $domesticMenu[$regionKey]['name'],
                'slug' => (string) $domesticMenu[$regionKey]['slug'],
                'code' => 'vn',
            ];
        }
    }

    $availableInboundCountries = [];
    foreach ($menu as $continent) {
        foreach ((array) ($continent['countries'] ?? []) as $country) {
            $code = strtolower(trim((string) ($country['code'] ?? '')));
            if ($code !== '') {
                $availableInboundCountries[$code] = $country;
            }
        }
    }

    $makeInboundCountry = static function (string $code, string $fallbackName, string $fallbackSlug) use ($availableInboundCountries): array {
        $country = $availableInboundCountries[$code] ?? [];

        return [
            'name' => trim((string) ($country['name'] ?? '')) ?: $fallbackName,
            'slug' => trim((string) ($country['slug'] ?? '')) ?: $fallbackSlug,
            'code' => $code,
        ];
    };

    $inboundMenuGroups = [
        [
            'name' => 'Vietnam',
            'url' => $inboundUrl,
            'items' => $vietnamRegions,
        ],
        [
            'name' => 'Indochina',
            'url' => $inboundUrl,
            'items' => [
                $makeInboundCountry('kh', 'Cambodia', 'cambodia'),
                $makeInboundCountry('la', 'Laos', 'laos'),
            ],
        ],
        [
            'name' => 'Nearby Asia',
            'url' => $inboundUrl,
            'items' => [
                $makeInboundCountry('th', 'Thailand', 'thailand'),
                $makeInboundCountry('sg', 'Singapore', 'singapore'),
                $makeInboundCountry('my', 'Malaysia', 'malaysia'),
            ],
        ],
    ];
}
$headerAdminMenuItems = [
    [
        'label' => $locale === 'en' ? 'Dashboard' : 'Bảng điều khiển',
        'url' => \App\Data\LocalizedPathCatalog::url('admin.dashboard', $locale),
        'icon' => 'bi-grid-1x2-fill',
    ],
    [
        'label' => $locale === 'en' ? 'Users' : 'Người dùng',
        'url' => \App\Data\LocalizedPathCatalog::url('admin.users', $locale),
        'icon' => 'bi-people-fill',
    ],
    [
        'label' => $locale === 'en' ? 'Tours' : 'Quản lý tour',
        'url' => \App\Data\LocalizedPathCatalog::url('admin.tours', $locale),
        'icon' => 'bi-map-fill',
    ],
    [
        'label' => $locale === 'en' ? 'Posts' : 'Bài viết',
        'url' => \App\Data\LocalizedPathCatalog::url('admin.blogs', $locale),
        'icon' => 'bi-file-earmark-text-fill',
    ],
    [
        'label' => $locale === 'en' ? 'Reviews' : 'Đánh giá',
        'url' => \App\Data\LocalizedPathCatalog::url('admin.reviews', $locale),
        'icon' => 'bi-star-fill',
    ],
    [
        'label' => $locale === 'en' ? 'Media optimization' : 'Tối ưu media',
        'url' => \App\Data\LocalizedPathCatalog::url('admin.mediaAudit', $locale),
        'icon' => 'bi-images',
    ],
];
$headerAdminGroupLabel = $locale === 'en' ? 'Administration' : 'Quản trị';
$headerPersonalGroupLabel = $locale === 'en' ? 'Personal' : 'Cá nhân';
$headerProfileLabel = $locale === 'en' ? 'My account' : 'Tài khoản của tôi';
?>
<div class="topbar-area two d-lg-block d-none">
    <div class="container-fluid">
        <div class="topbar-wrap">
            <ul class="contact-list">
                <li class="single-contact">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <path d="M15.5645 11.7424L13.3317 9.50954C12.5342 8.7121 11.1786 9.03111 10.8596 10.0678C10.6204 10.7855 9.82291 11.1842 9.10521 11.0247C7.51032 10.626 5.35722 8.55261 4.9585 6.87797C4.71926 6.16024 5.19773 5.36279 5.91543 5.12359C6.95211 4.80461 7.27109 3.44895 6.47364 2.65151L4.2408 0.418659C3.60284 -0.139553 2.6459 -0.139553 2.08769 0.418659L0.572545 1.93381C-0.942601 3.5287 0.732035 7.75516 4.48003 11.5032C8.22802 15.2512 12.4545 17.0056 14.0494 15.4106L15.5645 13.8955C16.1228 13.2575 16.1228 12.3006 15.5645 11.7424Z"></path>
                            </g>
                        </svg>
                    </div>
                    <a href="tel:<?= esc($contactPhone, 'attr') ?>"><?= esc($contactPhoneDisplay) ?></a>
                </li>
                <li class="single-contact">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.96372 3.07414L6.28622 7.39851C7.22897 8.33945 8.77003 8.34026 9.71356 7.39851L14.0361 3.07414C14.0463 3.06398 14.0541 3.05169 14.0591 3.03815C14.064 3.02461 14.0659 3.01015 14.0647 2.9958C14.0634 2.98144 14.059 2.96754 14.0517 2.95508C14.0445 2.94262 14.0346 2.93191 14.0227 2.9237C13.5819 2.61623 13.0455 2.43395 12.4677 2.43395H3.53216C2.95431 2.43395 2.41791 2.61626 1.97703 2.9237C1.96519 2.93191 1.95529 2.94262 1.94805 2.95508C1.9408 2.96754 1.93639 2.98144 1.93512 2.9958C1.93385 3.01015 1.93575 3.02461 1.9407 3.03815C1.94564 3.05169 1.9535 3.06398 1.96372 3.07414ZM0.808595 5.15748C0.808243 4.7181 0.915024 4.28525 1.11969 3.89645C1.12683 3.88274 1.13711 3.87091 1.14969 3.86193C1.16226 3.85294 1.17678 3.84705 1.19207 3.84473C1.20735 3.84241 1.22296 3.84372 1.23764 3.84857C1.25232 3.85342 1.26564 3.86167 1.27653 3.87264L5.54431 8.14042C6.89578 9.49385 9.10322 9.49464 10.4555 8.14042L14.7233 3.87264C14.7342 3.86167 14.7475 3.85342 14.7622 3.84857C14.7769 3.84372 14.7925 3.84241 14.8077 3.84473C14.823 3.84705 14.8376 3.85294 14.8501 3.86193C14.8627 3.87091 14.873 3.88274 14.8801 3.89645C15.0848 4.28526 15.1916 4.7181 15.1912 5.15748V10.843C15.1912 12.3459 13.9687 13.5666 12.4677 13.5666H3.53216C2.03116 13.5666 0.808595 12.3459 0.808595 10.843V5.15748Z"></path>
                        </svg>
                    </div>
                    <a href="mailto:<?= esc($contactEmail, 'attr') ?>"><?= esc($contactEmail) ?></a>
                </li>
            </ul>

            <div class="topbar-right">
                <div class="support-and-language-area">
                    <a href="<?= $aboutUrl ?>"><?= esc(lang('Frontend.header.aboutTravelPlus')) ?></a>
                    <a class="header-booking-lookup-btn header-booking-lookup-btn--topbar" href="<?= esc($bookingLookupUrl) ?>" aria-label="<?= esc($bookingLookupLabel, 'attr') ?>" title="<?= esc($bookingLookupLabel, 'attr') ?>">
                        <span><?= esc($bookingLookupLabel) ?></span>
                    </a>
                </div>

                <div class="search-and-login">
                    <a class="header-passport-nav" href="<?= esc($headerPassportUrl, 'attr') ?>">
                        <i class="bi bi-passport-fill" aria-hidden="true"></i>
                        <span><strong>Reward</strong><small><?= esc($headerPassportSubline) ?></small></span>
                    </a>
                    <?php if ($authUser): ?>
                        <div class="account-dropdown">
                            <button type="button" class="header-member-trigger account-btn" aria-label="<?= esc($authPrimaryLabel, 'attr') ?>">
                                <span class="header-member-avatar header-member-avatar--<?= esc($headerMemberTier, 'attr') ?>" aria-hidden="true">
                                    <span><?= esc($headerMemberInitials) ?></span>
                                    <i class="bi <?= esc($headerMemberTierIcon, 'attr') ?>"></i>
                                </span>
                                <strong><?= esc($headerMemberName) ?></strong>
                                <i class="bi bi-caret-down-fill header-member-caret" aria-hidden="true"></i>
                            </button>
                            <ul class="account-list">
                                <?php if (! empty($isAdminUser)): ?>
                                    <li class="account-menu-heading"><?= esc($headerAdminGroupLabel) ?></li>
                                    <?php foreach ($headerAdminMenuItems as $headerAdminMenuItem): ?>
                                        <li>
                                            <a class="account-menu-link" href="<?= esc($headerAdminMenuItem['url']) ?>">
                                                <i class="bi <?= esc($headerAdminMenuItem['icon'], 'attr') ?>" aria-hidden="true"></i>
                                                <span><?= esc($headerAdminMenuItem['label']) ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                    <li class="account-menu-divider" aria-hidden="true"></li>
                                <?php endif; ?>
                                <li class="account-menu-heading"><?= esc($headerPersonalGroupLabel) ?></li>
                                <li class="header-passport-summary">
                                    <a href="<?= esc($profileUrl, 'attr') ?>">
                                        <i class="bi bi-stars" aria-hidden="true"></i>
                                        <span><strong>Travel Plus Reward</strong><small><?= esc($headerPassportNextReward !== null
                                            ? (($locale === 'en' ? $headerPassportRemaining . ' points to the next voucher' : number_format($headerPassportRemaining, 0, ',', '.') . ' điểm nữa tới voucher tiếp theo'))
                                            : ($locale === 'en' ? 'Highest voucher milestone reached' : 'Đã đạt mốc voucher cao nhất')) ?></small></span>
                                    </a>
                                </li>
                                <li>
                                    <a class="account-menu-link" href="<?= $profileUrl ?>">
                                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                                        <span><?= esc($headerProfileLabel) ?></span>
                                    </a>
                                </li>
                                <li class="account-menu-logout">
                                    <form action="<?= esc($logoutUrl) ?>" method="post" class="header-logout-form">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="header-logout-button">
                                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                            <span><?= esc($logoutLabel) ?></span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $loginUrl ?>" class="primary-btn1 three black-bg">
                            <span>
                                <svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z"></path>
                                    </g>
                                </svg><?= esc($loginLabel) ?>
                            </span>
                            <span>
                                <svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z"></path>
                                    </g>
                                </svg><?= esc($loginLabel) ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
                <?= $this->include('partials/language-switcher') ?>
            </div>
        </div>
    </div>
</div>

<div class="header-brand-stage d-lg-flex d-none">
    <a class="header-brand-stage__logo" href="<?= localized_url('/') ?>">
        <img alt="Travel Plus" loading="eager" width="550" height="220" decoding="async" src="<?= base_url('assets/images/logo.svg') ?>">
    </a>
</div>

<header class="style-1 two site-header-modern">
    <div class="container site-header-container">
        <div class="site-header-shell">
        <a class="header-logo d-lg-none d-block" href="<?= localized_url('/') ?>">
            <img alt="Travel Plus" loading="eager" width="550" height="220" decoding="async" src="<?= base_url('assets/images/logo.svg') ?>">
        </a>

        <div class="main-menu" id="site-navigation" aria-label="<?= $locale === 'en' ? 'Main navigation' : 'Điều hướng chính' ?>">
            <div class="mobile-logo-area d-lg-none d-flex align-items-center justify-content-between">
                <a class="mobile-logo-wrap" href="<?= localized_url('/') ?>">
                    <img alt="Travel Plus" loading="lazy" width="550" height="220" decoding="async" src="<?= base_url('assets/images/logo.svg') ?>">
                </a>
                <button type="button" class="menu-close-btn" aria-label="<?= $locale === 'en' ? 'Close menu' : 'Đóng menu' ?>"><i class="bi bi-x" aria-hidden="true"></i></button>
            </div>

            <ul class="menu-list">
                <li class="menu-item-has-children position-inherit <?= $isActiveHeaderUrl($outboundUrl) ? 'current-menu-item' : '' ?>">
                    <a class="drop-down" href="<?= $outboundUrl ?>"><?= esc(lang('Frontend.header.menu.outbound')) ?><i class="bi bi-caret-down-fill"></i></a>
                    <i class="bi bi-plus dropdown-icon"></i>
                    <div class="mega-menu none">
                        <div class="container">
                            <div class="menu-row">
                                <?php foreach ($menu as $continent): ?>
                                    <?php
                                        $continentCountries = array_values((array) ($continent['countries'] ?? []));
                                        $hiddenCountryCount = max(0, count($continentCountries) - $megaMenuCountryLimit);
                                    ?>
                                    <div class="menu-single-item<?= $hiddenCountryCount > 0 ? ' menu-single-item--long' : '' ?>">
                                        <div class="menu-title">
                                            <a href="<?= localized_url($continent['slug']) ?>">
                                                <h5><?= esc($continent['name']) ?></h5>
                                            </a>
                                        </div>
                                        <i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <?php foreach ($continentCountries as $countryIndex => $country): ?>
                                                <?php
                                                    $flagCode = strtolower((string) ($country['code'] ?? ''));
                                                    $countryName = trim((string) ($country['name'] ?? ''));
                                                    if ($countryName === '') {
                                                        $countryName = trim(str_replace('-', ' ', (string) ($country['slug'] ?? '')));
                                                    }
                                                    if ($countryName === '') {
                                                        $countryName = trim(str_replace('-', ' ', strtoupper($flagCode)));
                                                    }
                                                    if ($countryName !== '' && function_exists('mb_convert_case')) {
                                                        $countryName = mb_convert_case($countryName, MB_CASE_TITLE, 'UTF-8');
                                                    }
                                                ?>
                                                <li class="mega-menu-country-item<?= $countryIndex >= $megaMenuCountryLimit ? ' mega-menu-country-item--extra' : '' ?>">
                                                    <a href="<?= localized_url($continent['slug'] . '/' . $country['slug']) ?>">
                                                        <img src="https://flagcdn.com/w20/<?= esc($flagCode) ?>.png" alt="<?= esc($country['name']) ?>" loading="lazy" decoding="async" width="20" height="15">
                                                        <?= esc($countryName) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if ($hiddenCountryCount > 0): ?>
                                                <li class="mega-menu-more">
                                                    <button
                                                        type="button"
                                                        class="mega-menu-more-toggle"
                                                        aria-expanded="false"
                                                        data-more-label="<?= esc(sprintf($megaMenuMoreLabel, $hiddenCountryCount), 'attr') ?>"
                                                        data-less-label="<?= esc($megaMenuLessLabel, 'attr') ?>"
                                                    >
                                                        <span><?= esc(sprintf($megaMenuMoreLabel, $hiddenCountryCount)) ?></span>
                                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?= view('partials/collection-navigation', ['navigationCollections' => $navigationCollections ?? [], 'locale' => $locale]) ?>
                        </div>
                    </div>
                </li>

                <?php if ($locale === 'vi'): ?>
                <li class="menu-item-has-children position-inherit <?= $isActiveHeaderUrl($domesticUrl) ? 'current-menu-item' : '' ?>">
                    <a class="drop-down" href="<?= $domesticUrl ?>"><?= esc(lang('Frontend.header.menu.domestic')) ?><i class="bi bi-caret-down-fill"></i></a>
                    <i class="bi bi-plus dropdown-icon"></i>
                    <div class="mega-menu none">
                        <div class="container">
                            <div class="menu-row grid-temp-col-5">
                                <?php foreach ($domesticMenu as $region): ?>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <a href="<?= $region['link'] ?>">
                                                <h5><?= esc($region['name']) ?></h5>
                                            </a>
                                        </div>
                                        <i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <?php foreach ($region['provinces'] as $province): ?>
                                                <li>
                                                    <a href="<?= $province['link'] ?>">
                                                        <img src="https://flagcdn.com/w20/vn.png" alt="<?= esc($province['name']) ?>" loading="lazy" decoding="async" width="20" height="15">
                                                        <?= esc($province['name']) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?= view('partials/collection-navigation', ['navigationCollections' => $navigationCollections ?? [], 'locale' => $locale]) ?>
                        </div>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($locale === 'en'): ?>
                <li class="menu-item-has-children position-inherit <?= $isActiveHeaderUrl($inboundUrl) ? 'current-menu-item' : '' ?>">
                    <a class="drop-down" href="<?= esc($inboundUrl, 'attr') ?>"><?= esc(lang('Frontend.header.menu.inbound')) ?><i class="bi bi-caret-down-fill"></i></a>
                    <i class="bi bi-plus dropdown-icon"></i>
                    <div class="mega-menu none">
                        <div class="container">
                            <div class="menu-row inbound-menu-row">
                                <?php foreach ($inboundMenuGroups as $group): ?>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <a href="<?= esc($group['url'], 'attr') ?>"><h5><?= esc($group['name']) ?></h5></a>
                                        </div>
                                        <i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <?php foreach ($group['items'] as $item): ?>
                                                <li>
                                                    <a href="<?= esc(rtrim($inboundUrl, '/') . '/' . $item['slug'], 'attr') ?>">
                                                        <img src="https://flagcdn.com/w20/<?= esc($item['code'], 'attr') ?>.png" alt="" loading="lazy" decoding="async" width="20" height="15">
                                                        <?= esc($item['name']) ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                                <div class="menu-single-item">
                                    <div class="menu-title"><a href="<?= esc($inboundUrl, 'attr') ?>"><h5>Plan your trip</h5></a></div>
                                    <ul class="none">
                                        <li><a href="<?= esc($inboundUrl, 'attr') ?>"><i class="bi bi-grid" aria-hidden="true"></i> View all inbound tours</a></li>
                                        <li><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('customTour', $locale), 'attr') ?>"><i class="bi bi-chat-dots" aria-hidden="true"></i> Request a custom tour</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endif; ?>

                <li class="<?= $isActiveHeaderUrl($visaUrl) ? 'current-menu-item' : '' ?>"><a href="<?= $visaUrl ?>"><?= esc(lang('Frontend.header.menu.visa')) ?></a></li>
                <li class="<?= $isActiveHeaderUrl($miceUrl) ? 'current-menu-item' : '' ?>"><a href="<?= $miceUrl ?>"><?= esc(lang('Frontend.header.menu.mice')) ?></a></li>

                <li class="menu-item-has-children <?= $serviceMenuActive ? 'current-menu-item' : '' ?>">
                    <a href="<?= esc($serviceMenuItems[0]['url'] ?? \App\Data\LocalizedPathCatalog::url('service.airlineTickets', $locale)) ?>" class="drop-down"><?= esc(lang('Frontend.header.menu.services')) ?><i class="bi bi-caret-down-fill"></i></a>
                    <i class="bi bi-plus dropdown-icon"></i>
                    <ul class="sub-menu none">
                        <?php foreach ($serviceMenuItems as $serviceMenuItem): ?>
                            <li><a href="<?= esc($serviceMenuItem['url']) ?>"><?= esc($serviceMenuItem['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="<?= $isActiveHeaderUrl($blogUrl) ? 'current-menu-item' : '' ?>"><a href="<?= $blogUrl ?>"><?= esc(lang('Frontend.header.menu.blog')) ?></a></li>
                <li class="<?= $isActiveHeaderUrl(\App\Data\LocalizedPathCatalog::url('customTour', $locale)) ? 'current-menu-item' : '' ?>"><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('customTour', $locale), 'attr') ?>"><?= $locale === 'en' ? 'Custom tours' : 'Tour theo yêu cầu' ?></a></li>
                <li class="<?= $isActiveHeaderUrl($contactUrl) ? 'current-menu-item' : '' ?>"><a href="<?= $contactUrl ?>"><?= esc(lang('Frontend.header.menu.contact')) ?></a></li>
                <li class="mobile-passport-menu-item"><a href="<?= esc($headerPassportUrl, 'attr') ?>"><i class="bi bi-passport-fill" aria-hidden="true"></i>Travel Plus Reward</a></li>
                <li class="mobile-booking-menu-item <?= $isActiveHeaderUrl($bookingLookupUrl) ? 'current-menu-item' : '' ?>"><a href="<?= esc($bookingLookupUrl) ?>"><?= esc($bookingLookupLabel) ?></a></li>
            </ul>

            <div class="language-and-login-area d-lg-none d-block">
                <?= $this->include('partials/language-switcher') ?>

                <?php if ($authUser): ?>
                    <div class="account-dropdown">
                        <button type="button" class="header-member-trigger account-btn" aria-label="<?= esc($authPrimaryLabel, 'attr') ?>">
                            <span class="header-member-avatar header-member-avatar--<?= esc($headerMemberTier, 'attr') ?>" aria-hidden="true">
                                <span><?= esc($headerMemberInitials) ?></span>
                                <i class="bi <?= esc($headerMemberTierIcon, 'attr') ?>"></i>
                            </span>
                            <strong><?= esc($headerMemberName) ?></strong>
                            <i class="bi bi-caret-down-fill header-member-caret" aria-hidden="true"></i>
                        </button>
                        <ul class="account-list">
                            <?php if (! empty($isAdminUser)): ?>
                                <li class="account-menu-heading"><?= esc($headerAdminGroupLabel) ?></li>
                                <?php foreach ($headerAdminMenuItems as $headerAdminMenuItem): ?>
                                    <li>
                                        <a class="account-menu-link" href="<?= esc($headerAdminMenuItem['url']) ?>">
                                            <i class="bi <?= esc($headerAdminMenuItem['icon'], 'attr') ?>" aria-hidden="true"></i>
                                            <span><?= esc($headerAdminMenuItem['label']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <li class="account-menu-divider" aria-hidden="true"></li>
                            <?php endif; ?>
                            <li class="account-menu-heading"><?= esc($headerPersonalGroupLabel) ?></li>
                            <li class="header-passport-summary">
                                <a href="<?= esc($profileUrl, 'attr') ?>">
                                    <i class="bi bi-stars" aria-hidden="true"></i>
                                    <span><strong>Travel Plus Reward</strong><small><?= esc($headerPassportNextReward !== null
                                        ? (($locale === 'en' ? $headerPassportRemaining . ' points to the next voucher' : number_format($headerPassportRemaining, 0, ',', '.') . ' điểm nữa tới voucher tiếp theo'))
                                        : ($locale === 'en' ? 'Highest voucher milestone reached' : 'Đã đạt mốc voucher cao nhất')) ?></small></span>
                                </a>
                            </li>
                            <li>
                                <a class="account-menu-link" href="<?= $profileUrl ?>">
                                    <i class="bi bi-person-circle" aria-hidden="true"></i>
                                    <span><?= esc($headerProfileLabel) ?></span>
                                </a>
                            </li>
                            <li class="account-menu-logout">
                                <form action="<?= esc($logoutUrl) ?>" method="post" class="header-logout-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="header-logout-button">
                                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                                        <span><?= esc($logoutLabel) ?></span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $loginUrl ?>" class="primary-btn1 three black-bg">
                        <span>
                            <svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg"><g><path d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z"></path></g></svg><?= esc($loginLabel) ?>
                        </span>
                        <span>
                            <svg width="15" height="15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg"><g><path d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z"></path></g></svg><?= esc($loginLabel) ?>
                        </span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="nav-right">
            <button class="site-language-open d-lg-none" type="button" data-language-open hidden aria-haspopup="dialog" aria-label="Chọn ngôn ngữ / Choose language"><i class="bi bi-globe2" aria-hidden="true"></i> <?= $locale === 'en' ? 'EN' : 'VI' ?></button>
            <button type="button" class="sidebar-button mobile-menu-btn" aria-controls="site-navigation" aria-expanded="false" aria-label="<?= $locale === 'en' ? 'Open menu' : 'Mở menu' ?>">
                <svg width="20" height="18" viewBox="0 0 20 18" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.29445 2.8421H10.5237C11.2389 2.8421 11.8182 2.2062 11.8182 1.42105C11.8182 0.635903 11.2389 0 10.5237 0H1.29445C0.579249 0 0 0.635903 0 1.42105C0 2.2062 0.579249 2.8421 1.29445 2.8421Z"></path>
                    <path d="M1.23002 10.421H18.77C19.4496 10.421 20 9.78506 20 8.99991C20 8.21476 19.4496 7.57886 18.77 7.57886H1.23002C0.550421 7.57886 0 8.21476 0 8.99991C0 9.78506 0.550421 10.421 1.23002 10.421Z"></path>
                    <path d="M18.8052 15.1579H10.2858C9.62563 15.1579 9.09094 15.7938 9.09094 16.5789C9.09094 17.3641 9.62563 18 10.2858 18H18.8052C19.4653 18 20 17.3641 20 16.5789C20 15.7938 19.4653 15.1579 18.8052 15.1579Z"></path>
                </svg>
            </button>
        </div>
        </div>
    </div>
</header>
