<div class="col-lg-4 mb-4">
  <div class="p-4 border rounded-4 h-100">
    <p class="mb-3">“<?= esc($review['content']) ?>”</p>
    <div class="d-flex align-items-center gap-3">
      <img src="<?= base_url($review['avatar']) ?>" width="48" class="rounded-circle">
      <div>
        <strong><?= esc($review['name']) ?></strong><br>
        <small class="text-muted"><?= esc($review['role']) ?></small>
      </div>
    </div>
  </div>
</div>
