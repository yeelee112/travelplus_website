<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= $this->include('blog/listing') ?>
<?= $this->endSection() ?>
