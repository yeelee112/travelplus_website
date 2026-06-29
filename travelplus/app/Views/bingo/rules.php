<?php
$display = ($variant ?? '') === 'display';
$rulesClass = 'bingo-rules' . ($display ? ' bingo-rules--display' : '');
$steps = [
    ['01', 'B&#7843;ng s&#7889;', 'M&#7895;i ng&#432;&#7901;i ch&#417;i c&#243; 1 b&#7843;ng 5 x 5, g&#7891;m 25 s&#7889; random t&#7915; 1 &#273;&#7871;n 90.'],
    ['02', '&#272;&#7893;i b&#7843;ng', 'C&#243; th&#7875; &#273;&#7893;i b&#7843;ng tr&#432;&#7899;c khi game b&#7855;t &#273;&#7847;u. Sau khi b&#7855;t &#273;&#7847;u th&#236; b&#7843;ng s&#7889; b&#7883; kh&#243;a.'],
    ['03', '&#272;&#225;nh d&#7845;u s&#7889;', 'Ch&#7881; &#273;&#432;&#7907;c click v&#224;o s&#7889; &#273;&#227; &#273;&#432;&#7907;c x&#7893;. S&#7889; ch&#432;a x&#7893; s&#7869; kh&#244;ng click &#273;&#432;&#7907;c.'],
    ['04', '&#272;i&#7873;u ki&#7879;n Bingo', 'Ph&#7843;i ho&#224;n th&#224;nh &#237;t nh&#7845;t 2 h&#224;ng. M&#7895;i h&#224;ng c&#243; &#273;&#7911; 5 &#244;, t&#237;nh theo ngang, d&#7885;c ho&#7863;c ch&#233;o.'],
    ['05', 'B&#7845;m Bingo', 'Khi &#273;&#7911; &#273;i&#7873;u ki&#7879;n, n&#250;t Bingo m&#7899;i m&#7903;. Ng&#432;&#7901;i ch&#417;i ph&#7843;i t&#7921; b&#7845;m Bingo &#273;&#7875; &#273;&#432;&#7907;c t&#237;nh th&#7855;ng.'],
    ['06', 'Ng&#432;&#7901;i th&#7855;ng', 'Game ghi nh&#7853;n t&#7889;i &#273;a 3 ng&#432;&#7901;i th&#7855;ng. Khi &#273;&#7911; 3 ng&#432;&#7901;i th&#7855;ng, game k&#7871;t th&#250;c.'],
];
?>
<section class="<?= esc($rulesClass) ?>">
    <div class="bingo-rules__hero">
        <div class="bingo-rules__copy">
            <div class="bingo-rules__eyebrow">TravelPlus Bingo</div>
            <h2 class="bingo-rules__title">Lu&#7853;t ch&#417;i</h2>
            <p class="bingo-rules__summary">Ho&#224;n th&#224;nh &#237;t nh&#7845;t <strong>2 h&#224;ng</strong>, m&#7895;i h&#224;ng &#273;&#7911; <strong>5 s&#7889;</strong>, sau &#273;&#243; t&#7921; b&#7845;m Bingo &#273;&#7875; x&#225;c nh&#7853;n.</p>
        </div>
        <div class="bingo-rules__win">
            <div class="bingo-rules__badge">2 h&#224;ng x 5 s&#7889;</div>
            <div class="bingo-rules__mini-board" aria-hidden="true">
                <?php for ($i = 1; $i <= 25; $i++): ?>
                    <?php $marked = in_array($i, [1, 2, 3, 4, 5, 7, 12, 17, 22], true); ?>
                    <span class="<?= $marked ? 'is-marked' : '' ?>"></span>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <div class="bingo-rules__steps">
        <?php foreach ($steps as [$number, $title, $body]): ?>
            <article class="bingo-rules__step">
                <span class="bingo-rules__step-number"><?= esc($number) ?></span>
                <div>
                    <h3><?= $title ?></h3>
                    <p><?= $body ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
