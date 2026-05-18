<?php
$request = service('request');
$locale = $request->getLocale() === 'en' ? 'en' : 'vi';
$totalViews = (new \App\Services\VisitorCounterService())->getTotalViews();
$offices = \App\Data\OfficeLocationCatalog::getAll($locale);

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
];

$legalLinks = [
    ['label' => lang('Frontend.footer.link.terms'), 'url' => \App\Data\LocalizedPathCatalog::url('legal.terms', $locale)],
    ['label' => lang('Frontend.footer.link.privacy'), 'url' => \App\Data\LocalizedPathCatalog::url('legal.privacy', $locale)],
];
?>
<footer class="footer-section">
    <div class="container">
        <div class="footer-contact-wrap">
            <div class="inquiry-area">
                <svg width="36" height="36" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                    <g>
                        <path d="M35.8703 28.2548L33.7795 22.1697C34.7873 20.1094 35.3199 17.8181 35.3235 15.5126C35.3297 11.5039 33.7788 7.71355 30.9563 4.83988C28.1332 1.96565 24.3714 0.347686 20.3636 0.284193C16.2077 0.218522 12.3015 1.79929 9.36472 4.73596C6.53295 7.56766 4.96231 11.3008 4.9126 15.29C2.12162 17.3914 0.474267 20.6676 0.479681 24.167C0.482282 25.8045 0.850861 27.4323 1.54927 28.9043L0.109064 33.0955C-0.138507 33.816 0.0423371 34.5983 0.581071 35.1371C0.960196 35.5162 1.46005 35.7181 1.9741 35.7181C2.19038 35.7181 2.4092 35.6824 2.62259 35.6091L6.81385 34.1688C8.28584 34.8673 9.91365 35.2358 11.5512 35.2384H11.5687C15.1201 35.2383 18.4213 33.5485 20.515 30.6891C22.6938 30.6317 24.8495 30.1043 26.7983 29.1509L32.8835 31.2419C33.1314 31.3274 33.3918 31.3712 33.654 31.3715C34.2649 31.3715 34.8589 31.1316 35.3095 30.6809C35.9497 30.0407 36.1645 29.1111 35.8703 28.2548Z"></path>
                        <path d="M26.5002 9.80957H13.7343C13.1426 9.80957 12.6629 10.2893 12.6629 10.881C12.6629 11.4727 13.1426 11.9524 13.7343 11.9524H26.5002C27.092 11.9524 27.5717 11.4727 27.5717 10.881C27.5717 10.2893 27.092 9.80957 26.5002 9.80957ZM26.5002 14.2161H13.7343C13.1426 14.2161 12.6629 14.6959 12.6629 15.2875C12.6629 15.8792 13.1426 16.359 13.7343 16.359H26.5002C27.092 16.359 27.5717 15.8792 27.5717 15.2875C27.5717 14.6959 27.092 14.2161 26.5002 14.2161ZM21.5862 18.6225H13.7342C13.1425 18.6225 12.6628 19.1023 12.6628 19.694C12.6628 20.2857 13.1426 20.7654 13.7342 20.7654H21.5862C22.1779 20.7654 22.6576 20.2856 22.6576 19.694C22.6576 19.1023 22.178 18.6225 21.5862 18.6225Z"></path>
                    </g>
                </svg>
                <div class="content">
                    <h6><?= esc(lang('Frontend.footer.needHelp')) ?></h6>
                    <span><?= esc(lang('Frontend.footer.needHelpDesc')) ?></span>
                </div>
            </div>
            <ul class="contact-area">
                <li class="single-contact">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 100 100"><g><path d="m90.99 16.75H9.01l37.98 32.416a4.638 4.638 0 0 0 6.02 0zM69 48.037l28.5 28.5V23.666zM46.987 61.684l-8.767-7.466L9.188 83.25h81.624L61.729 54.218l-8.721 7.46a4.638 4.638 0 0 1-6.021.006zM2.5 23.666v52.82l28.5-28.5z" fill="#009CDE"></path></g></svg>
                    </div>
                    <div class="content">
                        <span><?= esc(lang('Frontend.footer.email')) ?></span>
                        <a href="mailto:info@travelplusvn.com">info@travelplusvn.com</a>
                    </div>
                </li>
                <li class="single-contact">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 513.64 513.64"><g><path d="m499.66 376.96-71.68-71.68c-25.6-25.6-69.12-15.359-79.36 17.92-7.68 23.041-33.28 35.841-56.32 30.72-51.2-12.8-120.32-79.36-133.12-133.12-7.68-23.041 7.68-48.641 30.72-56.32 33.28-10.24 43.52-53.76 17.92-79.36l-71.68-71.68c-20.48-17.92-51.2-17.92-69.12 0L18.38 62.08c-48.64 51.2 5.12 186.88 125.44 307.2s256 176.641 307.2 125.44l48.64-48.64c17.921-20.48 17.921-51.2 0-69.12z" fill="#009CDE"></path></g></svg>
                    </div>
                    <div class="content">
                        <span><?= esc(lang('Frontend.footer.hotline')) ?></span>
                        <a href="tel:+84795681568">+84 79 568 1 568</a>
                    </div>
                </li>
            </ul>
        </div>
        <svg class="divider" width="1320" height="6" viewBox="0 0 1320 6" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM1315 3.5L1320 5.88675V0.113249L1315 2.5V3.5ZM4.5 3.5H1315.5V2.5H4.5V3.5Z"></path>
        </svg>
        <div class="footer-menu-wrap">
            <div class="row gy-md-4 gy-5">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="footer-logo-and-addition-info">
                        <a class="footer-logo" href="<?= localized_url('/') ?>" aria-label="Travel Plus homepage">
                            <img src="<?= base_url('assets/images/logo.svg') ?>" alt="Travel Plus" loading="lazy" width="240">
                        </a>
                        <p class="mt-3 mb-4"><?= esc(lang('Frontend.footer.aboutDesc')) ?></p>
                        <?php foreach ($offices as $office): ?>
                            <div class="address-area">
                                <span><?= esc($office['title']) ?></span>
                                <a href="<?= esc($office['map_url']) ?>" target="_blank"><?= esc($office['address']) ?></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 d-flex justify-content-lg-end">
                    <div class="footer-widget">
                        <div class="widget-title">
                            <h5><?= esc(lang('Frontend.footer.title.tours')) ?></h5>
                        </div>
                        <ul class="widget-list">
                            <?php foreach ($tourLinks as $link): ?>
                                <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 d-flex justify-content-lg-end">
                    <div class="footer-widget">
                        <div class="widget-title">
                            <h5><?= esc(lang('Frontend.footer.title.services')) ?></h5>
                        </div>
                        <ul class="widget-list">
                            <?php foreach ($serviceLinks as $link): ?>
                                <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 d-flex justify-content-lg-end">
                    <div class="footer-widget">
                        <div class="widget-title">
                            <h5><?= esc(lang('Frontend.footer.title.contactLegal')) ?></h5>
                        </div>
                        <ul class="widget-list">
                            <?php foreach ($infoLinks as $link): ?>
                                <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                            <?php endforeach; ?>
                            <?php foreach ($legalLinks as $link): ?>
                                <li><a href="<?= esc($link['url']) ?>"><?= esc($link['label']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="copyright-and-payment-method-area">
                <p><?= esc(lang('Frontend.footer.copyright')) ?></p>
                <div class="payment-method-area footer-view-metric">
                    <div class="footer-view-badge" aria-label="<?= esc(lang('Frontend.footer.views')) ?>">
                        <span class="footer-view-icon"><i class="bi bi-eye"></i></span>
                        <div class="footer-view-content">
                            <small><?= esc(lang('Frontend.footer.views')) ?></small>
                            <strong><?= esc(number_format($totalViews, 0, ',', '.')) ?></strong>
                        </div>
                    </div>
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
