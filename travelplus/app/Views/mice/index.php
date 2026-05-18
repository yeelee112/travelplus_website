<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= view('mice/mice-content', ['content' => $pageContent ?? []]) ?>

<?= $this->endSection() ?>
