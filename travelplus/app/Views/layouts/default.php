<!-- <!DOCTYPE html>
<html lang="<?= service('request')->getLocale() ?? config('App')->defaultLocale ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= \helper('seo_helper'); echo seo_meta($meta ?? []) ?>

    <link rel="alternate" hreflang="en" href="<?= site_url('en') ?>" />
    <link rel="alternate" hreflang="vi" href="<?= site_url('vi') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/assets/css/theme.css') ?>">
</head>
<body>
    <?= $this->include('partials/header') ?>

    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('partials/footer') ?>

    <script src="<?= base_url('public/assets/js/theme.js') ?>"></script>
</body>
</html> -->