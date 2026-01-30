<div class="col-lg-4 col-md-6 mb-4">
  <div class="card border-0 shadow-sm h-100">
    <img src="<?= base_url($blog['thumbnail']) ?>" class="card-img-top">
    <div class="card-body">
      <span class="badge bg-light text-dark mb-2"><?= esc($blog['category']) ?></span>
      <h6 class="fw-semibold"><?= esc($blog['title']) ?></h6>
      <small class="text-muted">By <?= esc($blog['author']) ?></small>
    </div>
  </div>
</div>
