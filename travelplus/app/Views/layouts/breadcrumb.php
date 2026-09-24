<?php
$locale = service('request')->getLocale() ?: 'vi';
$rawBreadcrumbs = is_array($breadcrumbs ?? null) ? $breadcrumbs : [];
$pageSchemaGraph = is_array($schema_graph ?? null) ? $schema_graph : [];
$hasBreadcrumbSchema = false;
foreach ($pageSchemaGraph as $schemaNode) {
    if (is_array($schemaNode) && ($schemaNode['@type'] ?? null) === 'BreadcrumbList') {
        $hasBreadcrumbSchema = true;
        break;
    }
}
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
    <div class="site-breadcrumb-band">
        <nav aria-label="<?= $locale === 'en' ? 'Breadcrumb' : 'Đường dẫn' ?>" class="container site-breadcrumb">
            <ol>
                <?php foreach ($crumbs as $index => $crumb): ?>
                    <?php $last = $index === array_key_last($crumbs); ?>
                    <?php if ($index > 0): ?><li class="site-breadcrumb-separator" aria-hidden="true">/</li><?php endif; ?>
                    <?php if (! $last && ! empty($crumb['url'])): ?>
                        <li>
                            <a href="<?= esc((string) $crumb['url'], 'attr') ?>">
                                <?= esc($crumb['label']) ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li aria-current="page">
                            <?= esc($crumb['label']) ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>

    <?php if (! $hasBreadcrumbSchema): ?>
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $schemaItems,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <?php endif; ?>
<?php endif; ?>
