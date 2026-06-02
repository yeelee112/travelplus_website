<?php
$locale = service('request')->getLocale() ?: 'vi';
$rawBreadcrumbs = is_array($breadcrumbs ?? null) ? $breadcrumbs : [];
$crumbs = [];

foreach ($rawBreadcrumbs as $crumb) {
    if (! is_array($crumb)) {
        continue;
    }

    $label = trim((string) ($crumb['label'] ?? ''));

    if ($label === '') {
        continue;
    }

    $url = trim((string) ($crumb['url'] ?? ''));
    $crumbs[] = [
        'label' => $label,
        'url' => $url !== '' ? $url : null,
    ];
}

$homeLabel = lang('Frontend.common.home', [], $locale) ?: 'Home';

if ($crumbs !== []):
    $schemaItems = [];

    foreach ($crumbs as $index => $crumb) {
        $item = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['label'],
        ];

        if (! empty($crumb['url'])) {
            $item['item'] = $crumb['url'];
        }

        $schemaItems[] = $item;
    }
?>
    <div class="container pt-3 pb-3 mt-3">
        <nav aria-label="breadcrumb" class="breadcrumb-wrapper">
            <ol class="breadcrumb">
                <?php foreach ($crumbs as $index => $crumb): ?>
                    <?php if ($index === 0): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= esc((string) ($crumb['url'] ?? localized_url('/')), 'attr') ?>" aria-label="<?= esc($homeLabel, 'attr') ?>" title="<?= esc($homeLabel, 'attr') ?>">
                                <i class="bi bi-house-fill main-color"></i>
                                <span class="visually-hidden"><?= esc($homeLabel) ?></span>
                            </a>
                        </li>
                    <?php elseif (! empty($crumb['url'])): ?>
                        <li class="breadcrumb-separator" aria-hidden="true"><i class="bi bi-chevron-right"></i></li>
                        <li class="breadcrumb-item">
                            <a href="<?= esc((string) $crumb['url'], 'attr') ?>">
                                <?= esc($crumb['label']) ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-separator" aria-hidden="true"><i class="bi bi-chevron-right"></i></li>
                        <li class="breadcrumb-item active" aria-current="page" title="<?= esc($crumb['label'], 'attr') ?>">
                            <?= esc($crumb['label']) ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>

    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $schemaItems,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<?php endif; ?>
