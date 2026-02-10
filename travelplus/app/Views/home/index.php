<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

    <?= $this->include('sections/hero-search') ?>
    <?= $this->include('sections/featured-tour') ?>
    <?= $this->include('sections/featured-destination') ?>
    <?= $this->include('sections/home-tour') ?>
    <?= $this->include('sections/services') ?>
    <?= $this->include('sections/home-blog') ?>
    <?= $this->include('sections/testimonial') ?>
    <?= $this->include('sections/counter') ?>
    <?= $this->include('sections/gallery-home') ?>



<?= $this->endSection() ?>
