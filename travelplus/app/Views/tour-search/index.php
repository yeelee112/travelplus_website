<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>

<div class="container pt-100">
    <div class="section-title mb-30">
        <h2><?= esc((string) ($pageTitle ?? 'Tour Search Results')) ?></h2>
        <p><?= esc((string) ($pageSubtitle ?? '')) ?></p>
    </div>
</div>

<?php if (((int) (($pagination['total'] ?? 0))) > 0): ?>
    <?= $this->include('sections/tour-list-show') ?>
<?php else: ?>
    <div class="container pb-40">
        <div class="checkout-stepper-card text-center">
            <h3><?= esc(service('request')->getLocale() === 'en' ? 'No matching tours found' : 'Không có tour theo ý muốn') ?></h3>
            <p><?= esc(service('request')->getLocale() === 'en' ? 'You can request a custom itinerary and we will build a suitable trip for you.' : 'Bạn có thể tạo tour theo yêu cầu, bên mình sẽ tư vấn hành trình phù hợp.') ?></p>
            <a href="<?= esc(localized_url('contact')) ?>" class="primary-btn1">
                <?= esc(service('request')->getLocale() === 'en' ? 'Create Custom Tour' : 'Tạo tour theo yêu cầu') ?>
            </a>
        </div>
    </div>

    <?php if (!empty($fallbackTours)): ?>
        <?php
        $tours = $fallbackTours;
        $pagination = ['total' => count($fallbackTours), 'page' => 1, 'lastPage' => 1];
        ?>
        <div class="container pt-20">
            <div class="section-title mb-30">
                <h2><?= esc(service('request')->getLocale() === 'en' ? 'All Tours' : 'Tất cả tour') ?></h2>
            </div>
        </div>
        <?= $this->include('sections/tour-list-show') ?>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
