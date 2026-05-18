<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= $this->include('blog/partials/detail-content') ?>
<?= $this->endSection() ?>
