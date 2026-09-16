<?php $inboundSettings = new \App\Services\WebsiteSettingsService(); ?>
<footer class="inbound-footer">
    <div class="container inbound-footer__grid">
        <div><span class="inbound-section-kicker">Travel Plus · Vietnam &amp; Indochina</span><h2>A different place.<br>A deeper connection.</h2></div>
        <div class="inbound-footer__links">
            <a href="mailto:<?= esc($inboundSettings->get('email'), 'attr') ?>"><?= esc($inboundSettings->get('email')) ?></a>
            <a href="tel:<?= esc($inboundSettings->get('hotline_e164'), 'attr') ?>"><?= esc($inboundSettings->phoneDisplay('en')) ?></a>
            <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('about', 'en'), 'attr') ?>">Meet Travel Plus <i class="bi bi-arrow-up-right"></i></a>
        </div>
    </div>
    <div class="container inbound-footer__bottom">
        <span>© <?= date('Y') ?> Travel Plus. All rights reserved.</span>
        <div><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('legal.privacy', 'en'), 'attr') ?>">Privacy</a><a href="<?= esc(\App\Data\LocalizedPathCatalog::url('legal.terms', 'en'), 'attr') ?>">Terms</a><a href="<?= esc(localized_url('/'), 'attr') ?>">Main website ↗</a></div>
    </div>
</footer>
