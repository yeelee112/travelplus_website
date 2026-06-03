<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?php
$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
?>

<div class="container pt-100">
    <div class="section-title mb-30">
        <h2><?= esc((string) ($pageTitle ?? $t('search.resultsTitle'))) ?></h2>
    </div>
</div>

<?= $this->include('sections/tour-list-filter') ?>

<?php if (((int) (($pagination['total'] ?? 0))) > 0): ?>
    <?= $this->include('sections/tour-list-show') ?>
<?php else: ?>
    <div class="container pb-40">
        <div class="checkout-stepper-card text-center">
            <h3><?= esc($t('search.noResultsTitle')) ?></h3>
            <p><?= esc($t('search.noResultsDesc')) ?></p>
            <a href="<?= esc(\App\Data\LocalizedPathCatalog::url('contact', $locale ?? (service('request')->getLocale() === 'en' ? 'en' : 'vi'))) ?>" class="primary-btn1">
                <?= esc($t('search.customTourCta')) ?>
            </a>
        </div>
    </div>

    <?php if (! empty($fallbackTours)): ?>
        <div class="container pt-20">
            <div class="section-title mb-30">
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
<?= $this->endSection() ?>
