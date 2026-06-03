<?php
$request = service('request');
$locale = $request->getLocale() === 'en' ? 'en' : 'vi';
$totalViews = (new \App\Services\VisitorCounterService())->getTotalViews();
$offices = \App\Data\OfficeLocationCatalog::getAll($locale);

$footerCopy = [
    'vi' => [
        'eyebrow' => 'Travel Plus Vietnam',
        'ctaTitle' => 'Cần tour, visa hoặc chương trình MICE riêng?',
        'ctaDesc' => 'Gửi nhu cầu để Travel Plus đề xuất lịch trình, ngân sách và phương án vận hành phù hợp cho gia đình, nhóm bạn hoặc doanh nghiệp.',
        'ctaPrimary' => 'Liên hệ Travel Plus',
        'ctaSecondary' => 'Tìm tour',
        'brandDesc' => 'Travel Plus cung cấp tour nước ngoài, tour trong nước, visa và MICE cho khách cá nhân, gia đình, nhóm bạn và doanh nghiệp.',
        'contactTitle' => 'Liên hệ nhanh',
        'officeTitle' => 'Văn phòng Travel Plus',
        'socialTitle' => 'Kết nối với Travel Plus',
        'viewLabel' => 'Lượt truy cập',
    ],
    'en' => [
        'eyebrow' => 'Travel Plus Vietnam',
        'ctaTitle' => 'Need a tour, visa service or custom MICE program?',
        'ctaDesc' => 'Send your requirements and Travel Plus will recommend the right itinerary, budget and operating plan for families, groups or corporate teams.',
        'ctaPrimary' => 'Contact Travel Plus',
        'ctaSecondary' => 'Find tours',
        'brandDesc' => 'Travel Plus provides outbound tours, domestic tours, visa services and MICE programs for individuals, families, groups and companies.',
        'contactTitle' => 'Quick contact',
        'officeTitle' => 'Travel Plus offices',
        'socialTitle' => 'Connect with Travel Plus',
        'viewLabel' => 'Total views',
    ],
][$locale];

$tourLinks = [
    ['label' => lang('Frontend.footer.link.outbound'), 'url' => \App\Data\LocalizedPathCatalog::url('outbound', $locale)],
    ['label' => lang('Frontend.footer.link.domestic'), 'url' => \App\Data\LocalizedPathCatalog::url('domestic', $locale)],
    ['label' => lang('Frontend.footer.link.search'), 'url' => \App\Data\LocalizedPathCatalog::url('search', $locale)],
    ['label' => lang('Frontend.footer.link.blog'), 'url' => \App\Data\LocalizedPathCatalog::url('blog', $locale)],
];

$serviceLinks = [
    ['label' => lang('Frontend.header.menu.visa'), 'url' => \App\Data\LocalizedPathCatalog::url('service.visa', $locale)],
    ['label' => lang('Frontend.header.menu.mice'), 'url' => \App\Data\LocalizedPathCatalog::url('service.mice', $locale)],
    ['label' => lang('Frontend.header.service.airlineTickets'), 'url' => \App\Data\LocalizedPathCatalog::url('service.airlineTickets', $locale)],
    ['label' => lang('Frontend.header.service.transport'), 'url' => \App\Data\LocalizedPathCatalog::url('service.transport', $locale)],
    ['label' => lang('Frontend.header.service.translation'), 'url' => \App\Data\LocalizedPathCatalog::url('service.translation', $locale)],
    ['label' => lang('Frontend.header.service.hotels'), 'url' => \App\Data\LocalizedPathCatalog::url('service.hotels', $locale)],
];

$infoLinks = [
    ['label' => lang('Frontend.footer.link.about'), 'url' => \App\Data\LocalizedPathCatalog::url('about', $locale)],
    ['label' => lang('Frontend.footer.link.contact'), 'url' => \App\Data\LocalizedPathCatalog::url('contact', $locale)],
    ['label' => lang('Frontend.footer.link.customTour'), 'url' => \App\Data\LocalizedPathCatalog::url('contact', $locale)],
    ['label' => lang('Frontend.footer.link.profile'), 'url' => base_url('assets/images/TravelPlus_CompanyProfile.png')],
    ['label' => lang('Frontend.footer.link.terms'), 'url' => \App\Data\LocalizedPathCatalog::url('legal.terms', $locale)],
    ['label' => lang('Frontend.footer.link.privacy'), 'url' => \App\Data\LocalizedPathCatalog::url('legal.privacy', $locale)],
];

$socialLinks = [
    ['label' => 'Facebook', 'url' => 'https://www.facebook.com/uuthedulich.vietnam', 'icon' => 'bi-facebook'],
    ['label' => 'YouTube', 'url' => 'https://www.youtube.com/@TravelPlus2023', 'icon' => 'bi-youtube'],
];
?>
<footer class="travelplus-footer" itemscope itemtype="https://schema.org/TravelAgency">
    <meta itemprop="name" content="Travel Plus Vietnam">
    <meta itemprop="url" content="<?= esc(base_url(), 'attr') ?>">
    <meta itemprop="telephone" content="+84795681568">
    <meta itemprop="email" content="info@travelplusvn.com">
    <div class="container">
        <div class="travelplus-footer__cta">
            <div class="travelplus-footer__cta-copy">
                <span class="travelplus-footer__eyebrow"><?= esc($footerCopy['eyebrow']) ?></span>
                <h2><?= esc($footerCopy['ctaTitle']) ?></h2>
                <p><?= esc($footerCopy['ctaDesc']) ?></p>
            </div>
            <div class="travelplus-footer__cta-actions">
                <a class="travelplus-footer__button travelplus-footer__button--primary" href="<?= esc(\App\Data\LocalizedPathCatalog::url('contact', $locale)) ?>">
                    <?= esc($footerCopy['ctaPrimary']) ?>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
                <a class="travelplus-footer__button travelplus-footer__button--ghost" href="<?= esc(\App\Data\LocalizedPathCatalog::url('search', $locale)) ?>">
                    <?= esc($footerCopy['ctaSecondary']) ?>
                </a>
            </div>
        </div>

        <div class="travelplus-footer__grid">
            <div class="travelplus-footer__brand">
                <a class="travelplus-footer__logo" href="<?= localized_url('/') ?>" aria-label="Travel Plus homepage" itemprop="url">
                    <img src="<?= base_url('assets/images/logo.svg') ?>" alt="Travel Plus" loading="lazy" decoding="async" width="240" height="96" itemprop="logo">
                </a>
                <p><?= esc($footerCopy['brandDesc']) ?></p>
                <div class="travelplus-footer__contact" aria-label="<?= esc($footerCopy['contactTitle']) ?>">
                    <a href="tel:+84795681568"><i class="bi bi-telephone"></i> +84 79 568 1 568</a>
                    <a href="mailto:info@travelplusvn.com"><i class="bi bi-envelope"></i> info@travelplusvn.com</a>
                </div>
                <div class="travelplus-footer__socials" aria-label="<?= esc($footerCopy['socialTitle']) ?>">
                    <?php foreach ($socialLinks as $social): ?>
                        <a href="<?= esc($social['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= esc($social['label']) ?>">
                            <i class="bi <?= esc($social['icon']) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <nav class="travelplus-footer__nav" aria-label="<?= esc(lang('Frontend.footer.title.tours')) ?>">
                <h3><?= esc(lang('Frontend.footer.title.tours')) ?></h3>
                <ul>
                    <?php foreach ($tourLinks as $link): ?>
                        <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <nav class="travelplus-footer__nav" aria-label="<?= esc(lang('Frontend.footer.title.services')) ?>">
                <h3><?= esc(lang('Frontend.footer.title.services')) ?></h3>
                <ul>
                    <?php foreach ($serviceLinks as $link): ?>
                        <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="travelplus-footer__side">
                <nav class="travelplus-footer__nav" aria-label="<?= esc(lang('Frontend.footer.title.contactLegal')) ?>">
                    <h3><?= esc(lang('Frontend.footer.title.contactLegal')) ?></h3>
                    <ul>
                        <?php foreach ($infoLinks as $link): ?>
                            <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <?php if ($offices !== []): ?>
            <div class="travelplus-footer__offices">
                <h3><?= esc($footerCopy['officeTitle']) ?></h3>
                <div class="travelplus-footer__office-grid">
                    <?php foreach ($offices as $office): ?>
                        <a class="travelplus-footer__office" href="<?= esc($office['map_url']) ?>" target="_blank" rel="noopener noreferrer" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                            <strong><?= esc($office['title']) ?></strong>
                            <span itemprop="streetAddress"><?= esc($office['address']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="travelplus-footer__bottom">
        <div class="container">
            <div class="travelplus-footer__bottom-inner">
                <p><?= esc(lang('Frontend.footer.copyright')) ?></p>
                <div class="travelplus-footer__views" aria-label="<?= esc($footerCopy['viewLabel']) ?>">
                    <i class="bi bi-eye"></i>
                    <span><?= esc($footerCopy['viewLabel']) ?>:</span>
                    <strong><?= esc(number_format($totalViews, 0, ',', '.')) ?></strong>
                </div>
            </div>
        </div>
    </div>
</footer>

<div class="progress-wrap" id="progressWrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path id="progressPath" d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"></path>
    </svg>
    <svg class="arrow" width="22" height="25" viewBox="0 0 24 23" xmlns="http://www.w3.org/2000/svg">
        <path d="M0.556131 11.4439L11.8139 0.186067L13.9214 2.29352L13.9422 20.6852L9.70638 20.7061L9.76793 8.22168L3.6064 14.4941L0.556131 11.4439Z"></path>
        <path d="M23.1276 11.4999L16.0288 4.40105L15.9991 10.4203L20.1031 14.5243L23.1276 11.4999Z"></path>
    </svg>
</div>
