<?php
$locale = service('request')->getLocale() ?: 'vi';
$contentLocale = $locale ?? (service('request')->getLocale() === 'en' ? 'en' : 'vi');
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $contentLocale);
$outboundUrl = \App\Data\LocalizedPathCatalog::url('outbound', $contentLocale);
$domesticUrl = \App\Data\LocalizedPathCatalog::url('domestic', $contentLocale);
$c = is_array($content ?? null) ? $content : [];
?>

<div class="mice-service-page">
    <section class="mice-hero-section">
        <div class="container">
            <div class="mice-hero-grid">
                <div class="mice-hero-content">
                    <span class="mice-eyebrow"><?= esc($c['hero_eyebrow'] ?? '') ?></span>
                    <h1><?= esc($c['hero_title'] ?? '') ?></h1>
                    <p><?= esc($c['hero_desc'] ?? '') ?></p>
                    <div class="mice-hero-actions">
                        <a class="primary-btn1 two" href="<?= esc($contactUrl) ?>">
                            <span><?= esc($c['hero_cta_primary'] ?? '') ?></span>
                            <span><?= esc($c['hero_cta_primary'] ?? '') ?></span>
                        </a>
                        <a class="primary-btn1 two transparent" href="#mice-brief">
                            <span><?= esc($c['hero_cta_secondary'] ?? '') ?></span>
                            <span><?= esc($c['hero_cta_secondary'] ?? '') ?></span>
                        </a>
                    </div>
                    <div class="mice-hero-metrics">
                        <?php foreach (($c['metrics'] ?? []) as $metric): ?>
                            <div class="mice-metric-card">
                                <strong><?= esc($metric['title']) ?></strong>
                                <span><?= esc($metric['text']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mice-hero-visual">
                    <div class="mice-hero-main-image">
                        <img src="<?= base_url('assets/images/mice-1.jpeg') ?>" alt="<?= esc(lang('Frontend.common.alt.travelPlusMice', [], $locale)) ?>">
                    </div>
                    <div class="mice-hero-side-grid">
                        <div class="mice-hero-side-card">
                            <img src="<?= base_url('assets/images/mice-2.jpg') ?>" alt="<?= esc(lang('Frontend.common.alt.corporateConference', [], $locale)) ?>">
                        </div>
                        <div class="mice-hero-side-card">
                            <img src="<?= base_url('assets/images/mice-3.jpg') ?>" alt="<?= esc(lang('Frontend.common.alt.teamBuildingProgram', [], $locale)) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mice-intro-section pt-100 pb-100">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="section-title">
                        <span><?= esc($c['intro_eyebrow'] ?? '') ?></span>
                        <h2><?= esc($c['intro_title'] ?? '') ?></h2>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="mice-copy-card">
                        <p><?= esc($c['intro_p1'] ?? '') ?></p>
                        <p><?= esc($c['intro_p2'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mice-services-section pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($c['services_eyebrow'] ?? '') ?></span>
                <h2><?= esc($c['services_title'] ?? '') ?></h2>
                <p><?= esc($c['services_desc'] ?? '') ?></p>
            </div>
            <div class="row g-4">
                <?php foreach (($c['service_cards'] ?? []) as $card): ?>
                    <div class="col-lg-3 col-md-6">
                        <article class="mice-service-card">
                            <h3><?= esc($card['title']) ?></h3>
                            <p><?= esc($card['text']) ?></p>
                            <ul>
                                <?php foreach (($card['bullets'] ?? []) as $bullet): ?>
                                    <li><?= esc($bullet) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mice-solutions-section pb-100">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="mice-solution-panel is-dark">
                        <span><?= esc($c['solution_eyebrow'] ?? '') ?></span>
                        <h2><?= esc($c['solution_title'] ?? '') ?></h2>
                        <p><?= esc($c['solution_text'] ?? '') ?></p>
                        <div class="mice-solution-links">
                            <a href="<?= esc($domesticUrl) ?>"><?= esc($c['solution_links']['domestic'] ?? '') ?></a>
                            <a href="<?= esc($outboundUrl) ?>"><?= esc($c['solution_links']['outbound'] ?? '') ?></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mice-solution-list">
                        <?php foreach (($c['solution_items'] ?? []) as $item): ?>
                            <article class="mice-solution-item">
                                <h3><?= esc($item['title']) ?></h3>
                                <p><?= esc($item['text']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mice-why-section pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($c['why_eyebrow'] ?? '') ?></span>
                <h2><?= esc($c['why_title'] ?? '') ?></h2>
            </div>
            <div class="row g-4">
                <?php foreach (($c['why_items'] ?? []) as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="mice-why-card">
                            <h3><?= esc($item['title']) ?></h3>
                            <p><?= esc($item['text']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mice-process-section pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($c['process_eyebrow'] ?? '') ?></span>
                <h2><?= esc($c['process_title'] ?? '') ?></h2>
            </div>
            <div class="mice-process-grid">
                <?php foreach (($c['process'] ?? []) as $index => $step): ?>
                    <article class="mice-process-step">
                        <span><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                        <h3><?= esc($step['title']) ?></h3>
                        <p><?= esc($step['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mice-brief-section mb-100" id="mice-brief">
        <div class="container">
            <div class="mice-brief-card">
                <div class="mice-brief-copy">
                    <span><?= esc($c['brief_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['brief_title'] ?? '') ?></h2>
                    <p><?= esc($c['brief_text'] ?? '') ?></p>
                </div>
                <div class="mice-brief-contact">
                    <div class="mice-brief-contact-item">
                        <small><?= esc(lang('Frontend.footer.hotline', [], $locale)) ?></small>
                        <a href="tel:+84795681568">+84 79 568 1 568</a>
                    </div>
                    <div class="mice-brief-contact-item">
                        <small><?= esc(lang('Frontend.footer.email', [], $locale)) ?></small>
                        <a href="mailto:info@travelplusvn.com">info@travelplusvn.com</a>
                    </div>
                    <a class="primary-btn1 two" href="<?= esc($contactUrl) ?>">
                        <span><?= esc($c['brief_submit'] ?? '') ?></span>
                        <span><?= esc($c['brief_submit'] ?? '') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
