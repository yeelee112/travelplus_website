<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$rawTitle = trim((string) ($page_heading ?? $meta_title ?? ''));
$pageHeading = trim((string) preg_replace('/\s*\|\s*Travel Plus\s*$/iu', '', $rawTitle));
$pageDesc = trim((string) ($page_description ?? $meta_desc ?? ''));

if ($pageHeading === '') {
    $tourType = (string) (($listingSearch ?? [])['tour_type'] ?? '');
    $pageHeading = match ($tourType) {
        'domestic' => lang('Frontend.common.domesticTours', [], $locale),
        'inbound' => lang('Frontend.common.inboundTours', [], $locale),
        default => lang('Frontend.common.outboundTours', [], $locale),
    };
}

$eyebrow = $locale === 'en' ? 'Tour collection' : 'Danh sách tour';
?>

<section class="tour-list-heading" aria-labelledby="tour-list-heading-title">
    <div class="container">
        <div class="travelplus-search-head tour-list-heading__head">
            <span><?= esc($eyebrow) ?></span>
            <h1 id="tour-list-heading-title"><?= esc($pageHeading) ?></h1>
            <?php if ($pageDesc !== ''): ?>
                <p class="tour-list-heading__desc"><?= esc($pageDesc) ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
