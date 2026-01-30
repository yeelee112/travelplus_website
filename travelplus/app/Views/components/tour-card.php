<div class="col-lg-4 col-md-6 mb-4">
  <div class="card h-100 border-0 shadow-sm">
    <img src="<?= base_url($tour['thumbnail']) ?>" class="card-img-top" alt="<?= esc($tour['title']) ?>">
    <div class="card-body">
      <h5 class="card-title"><?= esc($tour['title']) ?></h5>
      <p class="text-muted small"><?= esc($tour['location']) ?></p>
      <div class="d-flex justify-content-between align-items-center">
        <strong>$<?= number_format($tour['price']) ?></strong>
        <a href="#" class="btn btn-sm btn-outline-primary">Book Now</a>
      </div>
    </div>
  </div>
</div>
