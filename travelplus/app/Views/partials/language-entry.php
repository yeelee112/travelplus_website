<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$options = [
    [
        'locale' => 'vi',
        'url' => switch_locale_url('vi'),
        'flag' => base_url('assets/images/home/vi-vn.svg'),
        'name' => 'Tiếng Việt',
        'audience' => 'Dành cho khách Việt Nam',
        'description' => 'Tour trong nước và tour nước ngoài',
    ],
    [
        'locale' => 'en',
        'url' => switch_locale_url('en'),
        'flag' => base_url('assets/images/home/en-us.svg'),
        'name' => 'English',
        'audience' => 'For international travelers',
        'description' => 'Inbound in Vietnam & Indochina, plus outbound tours',
    ],
];
?>
<div class="language-entry" data-language-entry>
    <div class="language-entry__backdrop" aria-hidden="true"></div>
    <section class="language-entry__dialog" role="dialog" aria-modal="true" aria-labelledby="language-entry-title" aria-describedby="language-entry-description">
        <div class="language-entry__brand" aria-hidden="true">
            <img src="<?= base_url('assets/images/logo.svg') ?>" alt="" width="190" height="76">
        </div>
        <span class="language-entry__eyebrow">Welcome to Travel Plus</span>
        <h2 id="language-entry-title">Chọn ngôn ngữ <span>/</span> Choose your language</h2>
        <p id="language-entry-description">Chọn trải nghiệm phù hợp với hành trình của bạn.</p>

        <div class="language-entry__options">
            <?php foreach ($options as $option): ?>
                <a
                    class="language-entry__option<?= $option['locale'] === $locale ? ' is-current' : '' ?>"
                    href="<?= esc($option['url'], 'attr') ?>"
                    data-language-choice="<?= esc($option['locale'], 'attr') ?>">
                    <img src="<?= esc($option['flag'], 'attr') ?>" alt="" width="36" height="36">
                    <span class="language-entry__option-copy">
                        <strong><?= esc($option['name']) ?></strong>
                        <span><?= esc($option['audience']) ?></span>
                        <small><?= esc($option['description']) ?></small>
                    </span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="language-entry__note"><i class="bi bi-check2-circle" aria-hidden="true"></i> Lựa chọn sẽ được ghi nhớ / Your choice will be remembered</p>
    </section>
</div>
