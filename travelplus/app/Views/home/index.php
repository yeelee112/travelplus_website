<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- HERO -->
<section class="hero-section py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold">Explore the World,<br>One Journey at a Time</h1>
        <p class="text-muted mt-3">Discover new places, create unforgettable memories.</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED TOURS -->
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between mb-4">
      <h2 class="fw-bold">Our Featured Tours</h2>
    </div>

    <div class="row">
      <?php foreach ($tours as $tour): ?>
        <?= view('components/tour-card', ['tour' => $tour]) ?>
      <?php endforeach ?>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
