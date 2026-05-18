<?php
$locale = service('request')->getLocale() ?: 'vi';
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$c = is_array($content ?? null) ? $content : [];
$metrics = $c['metrics'] ?? [];
$regions = $c['regions'] ?? [];
$processSteps = $c['process'] ?? [];
$faqs = $c['faqs'] ?? [];

$sampleCountries = $locale === 'en'
    ? ['France', 'Japan', 'United States', 'Australia']
    : ['Pháp', 'Nhật Bản', 'Hoa Kỳ', 'Úc'];
$sampleVisaTypes = $locale === 'en'
    ? ['Travel visa', 'Business visa', 'Family visit visa', 'Student visa']
    : ['Visa du lịch', 'Visa công tác', 'Visa thăm thân', 'Visa du học'];
$sampleCitizenships = $locale === 'en'
    ? ['Vietnam', 'Australia', 'Singapore', 'Canada']
    : ['Việt Nam', 'Úc', 'Singapore', 'Canada'];
$sampleResidences = $locale === 'en'
    ? ['Vietnam', 'Canada', 'Japan', 'Germany']
    : ['Việt Nam', 'Canada', 'Nhật Bản', 'Đức'];

$flagMap = [
    'France' => 'fr', 'Pháp' => 'fr',
    'Germany' => 'de', 'Đức' => 'de',
    'Italy' => 'it', 'Ý' => 'it',
    'Switzerland' => 'ch', 'Thụy Sĩ' => 'ch',
    'Netherlands' => 'nl', 'Hà Lan' => 'nl',
    'Spain' => 'es', 'Tây Ban Nha' => 'es',
    'Japan' => 'jp', 'Nhật Bản' => 'jp',
    'South Korea' => 'kr', 'Hàn Quốc' => 'kr',
    'China' => 'cn', 'Trung Quốc' => 'cn',
    'Singapore' => 'sg',
    'Thailand' => 'th', 'Thái Lan' => 'th',
    'Malaysia' => 'my',
    'United States' => 'us', 'Hoa Kỳ' => 'us',
    'Canada' => 'ca',
    'Mexico' => 'mx',
    'Brazil' => 'br',
    'Australia' => 'au', 'Úc' => 'au',
    'New Zealand' => 'nz',
];
?>

<div class="home8-banner-section">
    <div class="banner-content-wrapper" style="background-image:url(<?= esc(base_url('assets/images/visa-banner.png')) ?>)">
        <div class="container">
            <div class="row gy-5 justify-content-between">
                <div class="col-xl-6 col-lg-8">
                    <div class="banner-title-area">
                        <span>#1 <?= esc($c['hero_eyebrow'] ?? '') ?></span>
                        <h1><?= esc($c['hero_title'] ?? '') ?></h1>
                        <p><?= esc($c['hero_desc'] ?? '') ?></p>
                    </div>
                </div>
                <div class="col-lg-3 d-flex align-items-center">
                    <div class="award-and-btn-area">
                        <a class="primary-btn1 five" href="<?= esc($contactUrl) ?>">
                            <span><?= esc($c['hero_cta_primary'] ?? '') ?><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z"></path></svg></span>
                            <span><?= esc($c['hero_cta_primary'] ?? '') ?><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z"></path></svg></span>
                        </a>
                        <span><?= esc(lang('Frontend.visaPage.freeConsultation', [], $locale)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="home8-feature-section">
    <div class="container">
        <div class="section-title text-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <h2><?= esc(lang('Frontend.visaPage.highlightsTitle', [], $locale)) ?></h2>
        </div>
        <div class="row gy-5">
            <?php foreach (array_slice($metrics, 0, 3) as $index => $metric): ?>
                <div class="col-lg-4 col-sm-6 d-flex justify-content-lg-center wow animate fadeInDown" data-wow-delay="<?= esc((string) (200 + ($index * 200))) ?>ms" data-wow-duration="1500ms">
                    <div class="single-feature">
                        <svg width="50" height="50" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                            <path d="M25 3C12.87 3 3 12.87 3 25s9.87 22 22 22 22-9.87 22-22S37.13 3 25 3zm0 4c9.93 0 18 8.07 18 18s-8.07 18-18 18S7 34.93 7 25 15.07 7 25 7zm-2.21 8.14-7.65 7.65 2.83 2.83 4.82-4.82 9.04 9.05 2.83-2.83-11.87-11.88z"></path>
                        </svg>
                        <h4><?= esc($metric['title'] ?? '') ?></h4>
                        <p><?= esc($metric['text'] ?? '') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="filter-wrapper three mb-100">
    <div class="container">
        <div class="filter-input-wrap">
            <h6><?= esc(lang('Frontend.visaPage.eligibilityTitle', [], $locale)) ?></h6>
            <form class="filter-input two show" action="<?= esc($contactUrl) ?>" method="get">
                <?php
                $filterSamples = [
                    [lang('Frontend.visaPage.country', [], $locale), $sampleCountries[0], $sampleCountries],
                    [lang('Frontend.visaPage.visaType', [], $locale), $sampleVisaTypes[0], $sampleVisaTypes],
                    [lang('Frontend.visaPage.citizenship', [], $locale), $sampleCitizenships[0], $sampleCitizenships],
                    [lang('Frontend.visaPage.livingIn', [], $locale), $sampleResidences[0], $sampleResidences],
                ];
                ?>
                <?php foreach ($filterSamples as [$label, $value, $options]): ?>
                    <div class="single-search-box">
                        <div class="custom-select-dropdown">
                            <span><?= esc($label) ?></span>
                            <input type="text" readonly value="<?= esc($value) ?>">
                        </div>
                        <div class="custom-select-wrap four">
                            <ul class="option-list">
                                <?php foreach ($options as $option): ?>
                                    <li class="single-item"><h6><?= esc($option) ?></h6></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="primary-btn1 white-bg">
                    <span><?= esc(lang('Frontend.visaPage.checkNow', [], $locale)) ?></span>
                    <span><?= esc(lang('Frontend.visaPage.checkNow', [], $locale)) ?></span>
                </button>
            </form>
            <p style="font-weight:400"><?= esc(lang('Frontend.visaPage.eligibilityNote', [], $locale)) ?></p>
        </div>
    </div>
</div>

<div class="home8-country-serve-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-lg-6">
                <div class="section-title text-center">
                    <h2><?= esc(lang('Frontend.visaPage.countryServeTitle', [], $locale)) ?></h2>
                    <p><?= esc(lang('Frontend.visaPage.countryServeDesc', [], $locale)) ?></p>
                </div>
            </div>
        </div>
        <div class="row g-xl-4 g-md-3 g-4">
            <?php foreach ($regions as $index => $region): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 wow animate fadeInUp" data-wow-delay="<?= esc((string) (200 + ($index * 200))) ?>ms" data-wow-duration="1500ms">
                    <div class="single-item">
                        <div class="title-area">
                            <svg width="35" height="35" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm6.92 9h-3.05a15.7 15.7 0 00-1.38-5.03A8.03 8.03 0 0118.92 11zM12 4.04c.87 1.06 1.8 3.06 2.12 6.96H9.88C10.2 7.1 11.13 5.1 12 4.04zM9.51 5.97A15.7 15.7 0 008.13 11H5.08a8.03 8.03 0 014.43-5.03zM4.26 13h3.69c.08 1.78.38 3.46.87 4.88A8.05 8.05 0 014.26 13zm5.62 0h4.24c-.32 3.9-1.25 5.9-2.12 6.96-.87-1.06-1.8-3.06-2.12-6.96zm4.61 4.88c.49-1.42.79-3.1.87-4.88h3.69a8.05 8.05 0 01-4.56 4.88z"></path></svg>
                            <h4><?= esc($region['title'] ?? '') ?></h4>
                        </div>
                        <svg class="line" height="6" viewBox="0 0 262 6" xmlns="http://www.w3.org/2000/svg"><path d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM257 3.5L262 5.88675V0.113249L257 2.5V3.5ZM4.5 3.5H257.5V2.5H4.5V3.5Z"></path></svg>
                        <ul>
                            <?php foreach (array_slice($region['items'] ?? [], 0, 8) as $item): ?>
                                <?php $flag = $flagMap[$item] ?? null; ?>
                                <li>
                                    <?php if ($flag): ?>
                                        <img alt="<?= esc($item) ?>" loading="lazy" width="35" height="20" src="https://flagcdn.com/h20/<?= esc($flag) ?>.png">
                                    <?php endif; ?>
                                    <?= esc($item) ?>
                                </li>
                            <?php endforeach; ?>
                            <li><?= esc(lang('Frontend.visaPage.manyMore', [], $locale)) ?></li>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="home8-process-section">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-lg-6">
                <div class="section-title white text-center">
                    <h2><?= esc($c['process_title'] ?? '') ?></h2>
                    <p><?= esc(lang('Frontend.visaPage.processDesc', [], $locale)) ?></p>
                </div>
            </div>
        </div>
        <div class="process-wrapper mb-60">
            <div class="row gy-5">
                <?php foreach ($processSteps as $index => $step): ?>
                    <div class="col-lg-3 col-sm-6">
                        <div class="process-card">
                            <span class="process-no"><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                            <h4><?= esc($step['title'] ?? '') ?></h4>
                            <p><?= esc($step['text'] ?? '') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <svg class="line" height="6" viewBox="0 0 1320 6" xmlns="http://www.w3.org/2000/svg"><path d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM1315 3.5L1320 5.88675V0.113249L1315 2.5V3.5ZM4.5 3.5H1315.5V2.5H4.5V3.5Z"></path></svg>
        </div>
        <div class="apply-area wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="contact-area">
                <div class="icon"><svg width="20" height="16" viewBox="0 0 20 16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.7006 1.22694L7.64404 7.17295C8.94032 8.46674 11.0593 8.46786 12.3566 7.17295L18.3001 1.22694C18.3141 1.21297 18.3249 1.19607 18.3317 1.17746C18.3385 1.15884 18.3411 1.13896 18.3394 1.11922C18.3377 1.09948 18.3316 1.08037 18.3216 1.06324C18.3117 1.0461 18.2981 1.03137 18.2818 1.02009C17.6756 0.597317 16.938 0.34668 16.1435 0.34668H3.8572C3.06267 0.34668 2.32511 0.59736 1.71891 1.02009C1.70262 1.03137 1.68901 1.0461 1.67905 1.06324C1.66909 1.08037 1.66302 1.09948 1.66128 1.11922C1.65953 1.13896 1.66215 1.15884 1.66894 1.17746C1.67574 1.19607 1.68655 1.21297 1.7006 1.22694ZM0.112306 4.09154C0.111822 3.48738 0.258646 2.89223 0.54006 2.35762C0.549884 2.33877 0.564016 2.32251 0.581309 2.31015C0.598601 2.29779 0.618565 2.28969 0.639578 2.2865C0.660591 2.28331 0.68206 2.28512 0.702241 2.29179C0.722422 2.29846 0.740745 2.30979 0.75572 2.32488L6.62392 8.19307C8.48219 10.0541 11.5174 10.0551 13.3768 8.19307L19.245 2.32488C19.26 2.30979 19.2783 2.29846 19.2985 2.29179C19.3187 2.28512 19.3401 2.28331 19.3611 2.2865C19.3822 2.28969 19.4021 2.29779 19.4194 2.31015C19.4367 2.32251 19.4508 2.33877 19.4607 2.35762C19.7421 2.89223 19.8889 3.48739 19.8884 4.09154V11.9091C19.8884 13.9756 18.2074 15.654 16.1435 15.654H3.8572C1.79333 15.654 0.112306 13.9756 0.112306 11.9091V4.09154Z"></path></svg></div>
                <div class="content">
                    <span><?= esc(lang('Frontend.footer.email', [], $locale)) ?></span>
                    <a href="mailto:info@travelplusvn.com">info@travelplusvn.com</a>
                </div>
            </div>
            <strong><?= esc(lang('Frontend.visaPage.or', [], $locale)) ?></strong>
            <a class="primary-btn1 two five" href="<?= esc($contactUrl) ?>">
                <span><?= esc(lang('Frontend.visaPage.registerNow', [], $locale)) ?></span>
                <span><?= esc(lang('Frontend.visaPage.registerNow', [], $locale)) ?></span>
            </a>
        </div>
    </div>
    <div class="vector">
        <img alt="<?= esc(lang('Frontend.common.alt.visaService', [], $locale)) ?>" loading="lazy" width="400" height="220" src="<?= esc(base_url('assets/images/visa-progress.webp')) ?>">
    </div>
</div>

<div class="home8-company-intro-section mb-100" style="background-image:url(<?= esc(base_url('assets/images/visa-wrapper.jpg')) ?>)">
    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-7 col-sm-9">
                <div class="lg-react-element company-intro-content">
                    <a href="https://www.youtube.com/watch?v=L8BXvDEF_Yc" target="_blank" rel="noopener noreferrer" style="cursor:pointer" class="video-area gallery-item">
                        <div class="play-btn"><svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M15.1574 8C15.1592 8.30356 15.0802 8.60213 14.9284 8.86503C14.7766 9.12793 14.5575 9.34567 14.2937 9.49588L3.43353 15.766C3.17151 15.9192 2.87344 16 2.5699 16C2.26636 16 1.96829 15.9192 1.70628 15.766C1.44249 15.6158 1.22345 15.398 1.07166 15.1351C0.919879 14.8723 0.840824 14.5737 0.84262 14.2701V1.72988C0.840832 1.42632 0.919886 1.12776 1.07166 0.864859C1.22344 0.601963 1.44247 0.38421 1.70625 0.23397C1.96827 0.0807516 2.26635 0 2.56989 0C2.87342 0 3.1715 0.0807516 3.43353 0.23397L14.2937 6.50413C14.5575 6.65434 14.7766 6.87208 14.9284 7.13498C15.0801 7.39787 15.1592 7.69644 15.1574 8Z"></path></svg></div>
                        <span><?= esc(lang('Frontend.visaPage.watchVideo', [], $locale)) ?></span>
                    </a>
                    <svg class="line" height="6" viewBox="0 0 344 6" xmlns="http://www.w3.org/2000/svg"><path d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM339 3.5L344 5.88675V0.113249L339 2.5V3.5ZM4.5 3.5H339.5V2.5H4.5V3.5Z"></path></svg>
                    <p><?= esc(lang('Frontend.visaPage.companyIntro', [], $locale)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="home8-faq-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-xl-6 col-lg-8">
                <div class="section-title text-center">
                    <h2><?= esc($c['faq_title'] ?? '') ?></h2>
                    <p><?= esc($c['faq_desc'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="faq-wrap three">
                    <div class="accordion accordion-flush" id="visaFaqAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <?php $id = 'visa-faq-' . ($index + 1); ?>
                            <div class="accordion-item wow animate fadeInDown" data-wow-delay="<?= esc((string) (200 + ($index * 200))) ?>ms" data-wow-duration="1500ms">
                                <h5 class="accordion-header" id="heading-<?= esc($id) ?>">
                                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($id) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= esc($id) ?>">
                                        <?= esc($faq['q'] ?? '') ?>
                                    </button>
                                </h5>
                                <div id="<?= esc($id) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading-<?= esc($id) ?>" data-bs-parent="#visaFaqAccordion">
                                    <div class="accordion-body"><?= esc($faq['a'] ?? '') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
