<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

    <?= $this->include('sections/hero-search') ?>
    <?= $this->include('sections/featured-tour') ?>
    <?= $this->include('sections/featured-destination') ?>
    <?= $this->include('sections/home-tour') ?>



<?= $this->endSection() ?>
