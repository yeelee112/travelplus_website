<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?php
$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
?>

<section class="travelplus-search-page">
<div class="container">
    <div class="travelplus-search-head">
        <span><?= esc($locale === 'en' ? 'Tour search' : 'Tìm kiếm tour') ?></span>
        <h1><?= esc((string) ($pageTitle ?? $t('search.resultsTitle'))) ?></h1>
    </div>
</div>

<?= $this->include('sections/tour-list-filter') ?>

<?php if (((int) (($pagination['total'] ?? 0))) > 0): ?>
    <?= $this->include('sections/tour-list-show') ?>
<?php else: ?>
    <div class="container">
        <div class="travelplus-search-empty">
            <span><i class="bi bi-search" aria-hidden="true"></i></span>
            <h3><?= esc($t('search.noResultsTitle')) ?></h3>
            <p><?= esc($t('search.noResultsDesc')) ?></p>
            <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('contact', $locale ?? (service('request')->getLocale() === 'en' ? 'en' : 'vi'))) ?>" class="travelplus-search-empty__cta">
                <span class="travelplus-search-empty__cta-label">
                    <i class="bi bi-stars" aria-hidden="true"></i>
                    <span><?= esc($t('search.customTourCta')) ?></span>
                </span>
                <span class="travelplus-search-empty__cta-effect" aria-hidden="true">
                    <span></span>
                </span>
            </a>
        </div>
    </div>

    <?php if (! empty($fallbackTours)): ?>
        <div class="container">
            <div class="travelplus-search-head travelplus-search-head--compact">
                <span><?= esc($locale === 'en' ? 'Suggested tours' : 'Tour gợi ý') ?></span>
                <h2><?= esc($t('search.allTours')) ?></h2>
            </div>
        </div>
        <?= view('sections/tour-list-show', [
            'tours' => $fallbackTours,
            'pagination' => [
                'total' => count($fallbackTours),
                'page' => 1,
                'lastPage' => 1,
            ],
            'showTopArea' => false,
        ]) ?>
    <?php endif; ?>
<?php endif; ?>
</section>
<?= $this->endSection() ?>
