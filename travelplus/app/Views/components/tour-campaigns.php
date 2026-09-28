<?php
$campaignLocale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$campaignAutumn = !empty($tour['is_autumn']);
$campaignPromotion = !empty($tour['promotion']['is_active']);
$campaignBadge = trim((string) ($tour['promotion']['badge'] ?? ''));
?>
<?php if ($campaignAutumn || $campaignPromotion): ?>
<div class="tour-campaigns">
    <?php if ($campaignPromotion): ?>
    <div class="tour-campaign tour-campaign--promotion">
        <span class="tour-campaign__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16v5a2 2 0 0 0 0 4v5H4v-5a2 2 0 0 0 0-4V5Z"/><path d="m9 15 6-6"/><circle cx="9" cy="9" r=".8"/><circle cx="15" cy="15" r=".8"/></svg></span>
        <span class="tour-campaign__copy"><small><?= $campaignLocale === 'en' ? 'SPECIAL OFFER' : 'ĐANG CÓ ƯU ĐÃI' ?></small><strong><?= esc($campaignBadge !== '' ? $campaignBadge : ($campaignLocale === 'en' ? 'Tour promotion' : 'Chương trình khuyến mãi')) ?></strong></span>
    </div>
    <?php endif; ?>
    <?php if ($campaignAutumn): ?>
    <div class="tour-campaign tour-campaign--autumn">
        <span class="tour-campaign__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path fill="currentColor" stroke="none" d="m12 1 2.3 5 2-1-.5 5 4-2-.5 3 3 1-5.5 5 .7 2-5-1V23h-1v-5l-5 1 .7-2L1.7 12l3-1-.5-3 4 2-.5-5 2 1Z"/></svg></span>
        <span class="tour-campaign__copy"><small><?= $campaignLocale === 'en' ? 'SEASONAL COLLECTION' : 'BỘ SƯU TẬP THEO MÙA' ?></small><strong><?= $campaignLocale === 'en' ? 'Autumn journeys' : 'Tour mùa thu' ?></strong></span>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
