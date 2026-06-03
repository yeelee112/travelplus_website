<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="home-page">
    <?= $this->include('sections/hero-search') ?>
    <?= $this->include('sections/home-promotions') ?>
    <?= $this->include('sections/home-tour') ?>
    <?= $this->include('sections/featured-destination') ?>
    <?= $this->include('sections/home-blog') ?>
    <?= $this->include('sections/testimonial') ?>
    <?= $this->include('sections/counter') ?>
    <?= $this->include('sections/gallery-home') ?>
</main>



<?= $this->endSection() ?>
