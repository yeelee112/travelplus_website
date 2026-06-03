<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>
<?= $this->include('sections/tour-list-filter') ?>
<?= $this->include('sections/tour-list-show') ?>



<?= $this->endSection() ?>
