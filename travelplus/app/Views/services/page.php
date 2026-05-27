<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>

<?php
$locale = service('request')->getLocale() ?: 'vi';
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$pageTitle = $nav_label[$locale] ?? $nav_label['vi'];
$t = static fn(string $key) => lang('Frontend.' . $key, [], $locale);
?>

<div class="service-landing-page">
    <section class="service-hero-section">
        <div class="container">
            <div class="service-hero-grid">
                <div class="service-hero-content">
                    <span class="service-eyebrow"><?= esc($hero['eyebrow'][$locale] ?? $hero['eyebrow']['vi']) ?></span>
                    <h1><?= esc($hero['title'][$locale] ?? $hero['title']['vi']) ?></h1>
                    <p><?= esc($hero['description'][$locale] ?? $hero['description']['vi']) ?></p>
                    <div class="service-hero-actions">
                        <a class="primary-btn1 two" href="<?= esc($contactUrl) ?>">
                            <span><?= esc($t('services.contactCta')) ?></span>
                            <span><?= esc($t('services.contactCta')) ?></span>
                        </a>
                        <a class="primary-btn1 two transparent" href="#service-cta">
                            <span><?= esc($t('services.quoteCta')) ?></span>
                            <span><?= esc($t('services.quoteCta')) ?></span>
                        </a>
                    </div>
                </div>
                <div class="service-hero-visual">
                    <img src="<?= base_url($hero['image']) ?>" alt="<?= esc($pageTitle) ?>" loading="eager" fetchpriority="high" decoding="async" width="720" height="520">
                </div>
            </div>

            <div class="service-metric-grid">
                <?php foreach ($metrics as $metric): ?>
                    <article class="service-metric-card">
                        <div class="service-metric-icon"><i class="<?= esc($metric['icon']) ?>"></i></div>
                        <h3><?= esc($metric['title'][$locale] ?? $metric['title']['vi']) ?></h3>
                        <p><?= esc($metric['text'][$locale] ?? $metric['text']['vi']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="service-intro-section pt-100 pb-100">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="section-title">
                        <span><?= esc($t('services.overviewEyebrow')) ?></span>
                        <h2><?= esc($intro['title'][$locale] ?? $intro['title']['vi']) ?></h2>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="service-intro-card">
                        <p><?= esc($intro['body'][$locale] ?? $intro['body']['vi']) ?></p>
                        <ul class="service-intro-points">
                            <?php foreach ($intro['points'] as $point): ?>
                                <li><?= esc($point[$locale] ?? $point['vi']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="service-capabilities-section pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($t('services.supportEyebrow')) ?></span>
                <h2><?= esc($capabilities_title[$locale] ?? $capabilities_title['vi']) ?></h2>
            </div>

            <div class="row g-4">
                <?php foreach ($capabilities as $item): ?>
                    <div class="col-lg-3 col-md-6">
                        <article class="service-capability-card">
                            <div class="service-capability-icon"><i class="<?= esc($item['icon']) ?>"></i></div>
                            <h3><?= esc($item['title'][$locale] ?? $item['title']['vi']) ?></h3>
                            <p><?= esc($item['text'][$locale] ?? $item['text']['vi']) ?></p>
                            <ul>
                                <?php foreach ($item['bullets'] as $bullet): ?>
                                    <li><?= esc($bullet[$locale] ?? $bullet['vi']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="service-fit-section pb-100">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="service-fit-panel">
                        <span><?= esc($t('services.fitEyebrow')) ?></span>
                        <h2><?= esc($use_cases_title[$locale] ?? $use_cases_title['vi']) ?></h2>
                        <ul class="service-fit-list">
                            <?php foreach ($use_cases as $item): ?>
                                <li><?= esc($item[$locale] ?? $item['vi']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="service-why-card">
                        <span><?= esc($t('services.whyEyebrow')) ?></span>
                        <h2><?= esc($why_title[$locale] ?? $why_title['vi']) ?></h2>
                        <div class="service-why-list">
                            <?php foreach ($why as $item): ?>
                                <article>
                                    <i class="bi bi-check2-circle"></i>
                                    <p><?= esc($item[$locale] ?? $item['vi']) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="service-process-section pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($t('services.workflowEyebrow')) ?></span>
                <h2><?= esc($t('services.workflowTitle')) ?></h2>
            </div>

            <div class="service-process-grid">
                <?php foreach ($process as $index => $step): ?>
                    <article class="service-process-step">
                        <span><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                        <p><?= esc($step[$locale] ?? $step['vi']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="service-cta-section pb-100" id="service-cta">
        <div class="container">
            <div class="service-cta-card">
                <div class="service-cta-copy">
                    <span><?= esc($t('services.requestEyebrow')) ?></span>
                    <h2><?= esc($cta['title'][$locale] ?? $cta['title']['vi']) ?></h2>
                    <p><?= esc($cta['text'][$locale] ?? $cta['text']['vi']) ?></p>
                </div>
                <div class="service-cta-contact">
                    <div class="service-cta-contact-item">
                        <small><?= esc(lang('Frontend.footer.hotline', [], $locale)) ?></small>
                        <a href="tel:+84795681568">+84 79 568 1 568</a>
                    </div>
                    <div class="service-cta-contact-item">
                        <small><?= esc(lang('Frontend.footer.email', [], $locale)) ?></small>
                        <a href="mailto:info@travelplusvn.com">info@travelplusvn.com</a>
                    </div>
                    <a class="primary-btn1" href="<?= esc($contactUrl) ?>">
                        <span><?= esc($t('services.sendNow')) ?></span>
                        <span><?= esc($t('services.sendNow')) ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>
