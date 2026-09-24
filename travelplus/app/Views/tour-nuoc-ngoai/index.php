<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?= $this->include('sections/tour-list-heading') ?>
<?= $this->include('sections/tour-list-filter') ?>
<?= $this->include('sections/tour-list-show') ?>



<?= $this->endSection() ?>
