<?php $switcherLocale = service('request')->getLocale() === 'en' ? 'en' : 'vi'; ?>
<details class="site-language-switcher">
    <summary aria-label="<?= $switcherLocale === 'en' ? 'Choose language: English' : 'Chọn ngôn ngữ: Tiếng Việt' ?>">
        <img src="<?= esc(base_url('assets/images/home/' . ($switcherLocale === 'vi' ? 'vi-vn.svg' : 'en-us.svg')), 'attr') ?>" alt="" width="20" height="20">
        <span><?= $switcherLocale === 'vi' ? 'Tiếng Việt' : 'English' ?></span>
        <i class="bi bi-chevron-down" aria-hidden="true"></i>
    </summary>
    <div class="site-language-switcher__menu">
        <span class="site-language-switcher__label">Ngôn ngữ / Language</span>
        <?php foreach (['vi' => 'Tiếng Việt', 'en' => 'English'] as $language => $label): ?>
            <a href="<?= esc(switch_locale_url($language), 'attr') ?>" data-language-choice="<?= esc($language, 'attr') ?>" lang="<?= esc($language, 'attr') ?>" hreflang="<?= esc($language, 'attr') ?>" <?= $switcherLocale === $language ? 'aria-current="true"' : '' ?>>
                <img src="<?= esc(base_url('assets/images/home/' . ($language === 'vi' ? 'vi-vn.svg' : 'en-us.svg')), 'attr') ?>" alt="" width="20" height="20">
                <span><?= esc($label) ?></span>
                <?php if ($switcherLocale === $language): ?><i class="bi bi-check2" aria-hidden="true"></i><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</details>
