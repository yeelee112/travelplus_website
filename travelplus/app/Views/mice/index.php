<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= view('mice/mice-content', [
    'content' => $pageContent ?? [],
    'contact_form_token' => $contact_form_token ?? '',
]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php $recaptchaSiteKey = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'"); ?>
<?php if ($recaptchaSiteKey !== ''): ?>
<script defer src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey, 'url') ?>"></script>
<?php endif; ?>
<script type="module" src="<?= esc(frontend_asset_url('assets/js/contact-page.js'), 'attr') ?>"></script>
<?= $this->endSection() ?>
