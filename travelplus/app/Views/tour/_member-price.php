<?php
$locale = ($locale ?? 'vi') === 'en' ? 'en' : 'vi';
$rateLabel = rtrim(rtrim(number_format((float) $benefit['discount_rate'], 2, $locale === 'en' ? '.' : ',', ''), '0'), $locale === 'en' ? '.' : ',');
?>
<div class="tour-member-price">
    <div class="tour-member-price__head">
        <div class="tour-member-price__badge"><i class="bi bi-stars" aria-hidden="true"></i> <?= esc(($locale === 'en' ? 'Member · ' : 'Thành viên ') . $benefit['label']) ?></div>
        <div class="tour-member-price__rate">−<?= esc($rateLabel) ?>%</div>
    </div>
    <div class="tour-member-price__original"><?= esc($locale === 'en' ? 'Tour price ' : 'Giá tour ') ?><s><?= esc($originalPrice) ?></s></div>
    <div class="tour-member-price__amount"><?= esc($benefit['price']) ?><div class="tour-member-price__unit"><?= esc($locale === 'en' ? '/ person' : '/ khách') ?></div></div>
    <div class="tour-member-price__saving"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> <?= esc($benefit['saving']) ?></div>
    <div class="tour-member-price__note"><?= esc($locale === 'en' ? 'Applied automatically at booking. Voucher not included.' : 'Tự động giảm khi đặt tour. Chưa gồm voucher.') ?></div>
</div>
