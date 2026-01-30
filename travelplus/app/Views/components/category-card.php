<div class="col-lg-3 col-md-4 col-6 mb-4">
  <div class="text-center p-4 border rounded-4 h-100 category-card">
    <img src="<?= base_url($category['icon']) ?>" class="mb-3" width="48" alt="<?= esc($category['name']) ?>">
    <h6 class="fw-semibold"><?= esc($category['name']) ?></h6>
    <small class="text-muted"><?= $category['total'] ?> Tours</small>
  </div>
</div>
