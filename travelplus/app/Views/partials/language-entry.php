<?php $entryLocale = service('request')->getLocale() === 'en' ? 'en' : 'vi'; ?>
<dialog class="language-entry" data-language-entry aria-labelledby="language-entry-title" aria-describedby="language-entry-description" tabindex="-1" autofocus>
    <div class="language-entry__cover">
        <img src="<?= esc(base_url('assets/images/home/banner01-768w.webp'), 'attr') ?>" alt="" width="768" height="410">
        <span class="language-entry__welcome">WELCOME TO TRAVEL PLUS</span>
    </div>
    <button class="language-entry__close" type="button" data-language-dismiss aria-label="Đóng / Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    <div class="language-entry__body">
        <img class="language-entry__logo" src="<?= esc(base_url('assets/images/logo.svg'), 'attr') ?>" alt="Travel Plus" width="112" height="45">
        <h2 id="language-entry-title"><?= $entryLocale === 'en' ? 'Your journey starts here' : 'Hành trình bắt đầu từ đây' ?></h2>
        <p id="language-entry-description">Chọn ngôn ngữ của bạn <span aria-hidden="true">·</span> <span lang="en">Choose your language</span></p>
        <div class="language-entry__options">
            <?php foreach (['vi' => ['Tiếng Việt', 'Tiếp tục bằng tiếng Việt', 'vi-vn.svg'], 'en' => ['English', 'Continue in English', 'en-us.svg']] as $language => $option): ?>
                <a class="language-entry__option" href="<?= esc(switch_locale_url($language), 'attr') ?>" data-language-choice="<?= esc($language, 'attr') ?>" lang="<?= esc($language, 'attr') ?>" hreflang="<?= esc($language, 'attr') ?>">
                    <img src="<?= esc(base_url('assets/images/home/' . $option[2]), 'attr') ?>" alt="" width="32" height="32">
                    <strong><?= esc($option[0]) ?></strong>
                    <span class="language-entry__action"><?= esc($option[1]) ?><i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="language-entry__footer">
            <span><i class="bi bi-globe2" aria-hidden="true"></i> Đổi ngôn ngữ bất cứ lúc nào<br><span lang="en">Change language anytime</span></span>
            <button type="button" data-language-dismiss><?= $entryLocale === 'en' ? 'Explore first' : 'Khám phá trước' ?> <span aria-hidden="true">↗</span></button>
        </div>
    </div>
</dialog>
