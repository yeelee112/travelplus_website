<?php
$benefit = is_array($benefit ?? null) ? $benefit : null;

if ($benefit === null) {
    return;
}

$state = in_array((string) ($benefit['state'] ?? ''), ['guest', 'locked', 'active'], true)
    ? (string) $benefit['state']
    : 'guest';
$tooltip = trim((string) ($benefit['tooltip'] ?? ''));
$eyebrow = trim((string) ($benefit['eyebrow'] ?? ''));
$tierKey = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($benefit['tier_key'] ?? '')));
$originalPrice = trim((string) ($originalPrice ?? ''));
$originalPriceLabel = trim((string) ($originalPriceLabel ?? '')) ?: 'Giá tour';
?>
<span
    class="tour-passport-price tour-passport-price--<?= esc($state, 'attr') ?>"
    <?php if ($tierKey !== ''): ?>data-tier="<?= esc($tierKey, 'attr') ?>"<?php endif; ?>
    <?php if ($tooltip !== ''): ?>title="<?= esc($tooltip, 'attr') ?>"<?php endif; ?>>
    <?php if ($state === 'active'): ?>
        <span class="tour-passport-price__topline">
            <span class="tour-passport-price__badge">
                <i class="bi bi-stars" aria-hidden="true"></i>
                <span><?= esc($eyebrow !== '' ? $eyebrow : 'Giá thành viên') ?></span>
                <b><?= esc((string) ($benefit['label'] ?? '')) ?></b>
            </span>
            <small class="tour-passport-price__saving"><?= esc((string) ($benefit['saving'] ?? '')) ?></small>
        </span>
        <strong class="tour-passport-price__current"><?= esc((string) ($benefit['price'] ?? '')) ?></strong>
        <span class="tour-passport-price__comparison">
            <?php if ($originalPrice !== ''): ?>
                <span><?= esc($originalPriceLabel) ?> <s><?= esc($originalPrice) ?></s></span>
            <?php endif; ?>
        </span>
    <?php else: ?>
        <?php if ($eyebrow !== ''): ?>
            <span class="tour-passport-price__badge">
                <i class="bi bi-stars" aria-hidden="true"></i>
                <span><?= esc($eyebrow) ?></span>
            </span>
        <?php endif; ?>
        <span class="tour-passport-price__hint">
            <?php if ($state === 'guest'): ?><i class="bi bi-stars" aria-hidden="true"></i><?php endif; ?>
            <?= esc((string) ($benefit['label'] ?? '')) ?>
            <?php if ($state === 'guest'): ?><i class="bi bi-arrow-right" aria-hidden="true"></i><?php endif; ?>
        </span>
    <?php endif; ?>
</span>
