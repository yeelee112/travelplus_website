<?php
$locale = service('request')->getLocale() ?: 'vi';
$c = is_array($content ?? null) ? $content : [];
?>

<div class="about-section pt-100 mb-100">
    <div class="container">
        <div class="about-wrapper">
            <div class="row align-items-center justify-content-between">
                <div class="col-xl-6 col-lg-7 wow animate fadeInLeft" data-wow-delay="200ms" data-wow-duration="1500ms">
                    <div class="about-content">
                        <div class="section-title">
                            <h4 class="pb-2"><?= esc($c['welcome'] ?? '') ?></h4>
                            <h2><?= esc($c['title'] ?? '') ?></h2>
                            <p><?= esc($c['intro_1'] ?? '') ?></p>
                            <p><?= esc($c['intro_2'] ?? '') ?></p>
                        </div>
                        <div class="founder-area">
                            <div class="founder-info">
                                <h6><?= esc($c['founder'] ?? '') ?></h6>
                                <span><?= esc($c['position'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-lg-block d-none wow animate fadeInRight" data-wow-delay="200ms" data-wow-duration="1500ms">
                    <div class="about-img">
                        <img src="<?= base_url('assets/images/about.webp') ?>" alt="<?= esc(lang('Frontend.common.alt.aboutTravelPlus', [], $locale)) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="home1-service-section mb-100">
    <div class="container">
        <div class="service-wrapper">
            <div class="row justify-content-center wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
                <div class="col-lg-9">
                    <div class="section-title">
                        <h2><?= esc($c['service_title'] ?? '') ?></h2>
                        <svg height="6" viewBox="0 0 872 6" xmlns="http://www.w3.org/2000/svg"><path d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM867 3.5L872 5.88675V0.113249L867 2.5V3.5ZM4.5 3.5H867.5V2.5H4.5V3.5Z"></path></svg>
                    </div>
                </div>
            </div>
            <ul class="service-list wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
                <?php foreach (($c['services'] ?? []) as $service): ?>
                    <li class="single-service">
                        <div class="content">
                            <h4><?= esc($service['title']) ?></h4>
                            <p><?= esc($service['text']) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="bottom-area d-flex justify-content-center wow animate fadeInUp" data-wow-delay="200ms" data-wow-duration="1500ms">
                <div class="batch"><span></span><a href="<?= \App\Data\LocalizedPathCatalog::url('contact', $locale ?? (service('request')->getLocale() === 'en' ? 'en' : 'vi')) ?>"><?= esc($c['cta'] ?? '') ?><svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><path d="M1 9L9 1M9 1C7.22222 1.33333 3.33333 2 1 1M9 1C8.66667 2.66667 8 6.33333 9 9" stroke-width="1.5" stroke-linecap="round"></path></svg></a></div>
            </div>
        </div>
    </div>
</div>

<div class="about-page-journey-section mb-100">
    <div class="container">
        <div class="row justify-content-center mb-50 wow animate fadeInDown" data-wow-delay="200ms" data-wow-duration="1500ms">
            <div class="col-lg-8">
                <div class="section-title text-center">
                    <h2><?= esc($c['journey_title'] ?? '') ?></h2>
                    <p><?= esc($c['journey_desc'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="jouney-content-wrapper">
            <div class="nav-area mb-50">
                <div class="nav nav-pills" id="pills-tab" role="tablist">
                    <div class="swiper about-page-journey-slider">
                        <div class="swiper-wrapper">
                            <?php foreach (($c['timeline'] ?? []) as $index => $item): ?>
                                <?php $active = $index === 0; $tabId = 'pills-' . ($index + 1); ?>
                                <div class="swiper-slide">
                                    <div class="nav-item" role="presentation">
                                        <div class="nav-link <?= $active ? 'active' : '' ?>" id="<?= esc($tabId) ?>-tab" data-bs-toggle="pill" data-bs-target="#<?= esc($tabId) ?>" role="tab" aria-controls="<?= esc($tabId) ?>" aria-selected="<?= $active ? 'true' : 'false' ?>">
                                            <img src="<?= base_url('assets/images/logo.svg') ?>" alt="Travel Plus logo">
                                            <h4><?= esc($item['year']) ?></h4>
                                        </div>
                                        <span class="dot"></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <svg class="line" height="6" viewBox="0 0 1320 6" xmlns="http://www.w3.org/2000/svg"><path d="M5 2.5L0 0.113249V5.88675L5 3.5V2.5ZM1315 3.5L1320 5.88675V0.113249L1315 2.5V3.5ZM4.5 3.5H1315.5V2.5H4.5V3.5Z"></path></svg>
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="tab-content" id="pills-tabContent">
                        <?php foreach (($c['timeline'] ?? []) as $index => $item): ?>
                            <?php $active = $index === 0; $tabId = 'pills-' . ($index + 1); ?>
                            <div class="tab-pane fade <?= $active ? 'show active' : '' ?>" id="<?= esc($tabId) ?>" role="tabpanel" aria-labelledby="<?= esc($tabId) ?>-tab">
                                <h4><?= esc($item['title']) ?></h4>
                                <p><?= esc($item['body']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
