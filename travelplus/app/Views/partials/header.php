<header class="site-header">
    <div class="container">
        <div class="brand">
            <a href="<?= site_url() ?>"><img src="<?= base_url('public/assets/images/logo.png') ?>" alt="TravelPlus"/></a>
        </div>
        <nav class="main-nav">
            <a href="<?= site_url() ?>"><?= lang('Frontend.home') ?></a>
            <a href="#"> <?= lang('Frontend.tours') ?> </a>
            <a href="#"> <?= lang('Frontend.destination') ?> </a>
            <a href="#"> <?= lang('Frontend.about') ?> </a>
            <a href="#"> <?= lang('Frontend.contact') ?> </a>
        </nav>

        <div class="locale-switcher">
            <a href="<?= site_url('en') ?>" <?= service('request')->getLocale() === 'en' ? 'class="active"' : '' ?>>EN</a>
            <a href="<?= site_url('vi') ?>" <?= service('request')->getLocale() === 'vi' ? 'class="active"' : '' ?>>VN</a>
        </div>
    </div>
</header>