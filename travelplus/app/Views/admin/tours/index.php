<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1360px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .table td,.table th { vertical-align:middle; }
        .tour-actions { display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap; }
        .tour-actions form { margin:0; }
        .price-stack { display:flex; flex-direction:column; gap:2px; }
        .price-stack strong { font-size:15px; color:#0f172a; }
        .price-stack small { color:#64748b; }
        .departure-stack { display:flex; flex-direction:column; gap:2px; }
        .departure-stack strong { font-size:14px; color:#0f172a; }
        .departure-stack small { color:#64748b; }
        .quick-edit-row { display:none; background:#f8fbff; }
        .quick-edit-row.is-open { display:table-row; }
        .quick-edit-cell { padding:0 !important; border-top:0 !important; }
        .quick-edit-panel { border-top:1px solid #e6ebf0; padding:18px 22px 22px; }
        .quick-edit-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:16px; flex-wrap:wrap; }
        .quick-edit-title { font-size:18px; font-weight:700; margin:0; }
        .quick-edit-meta { color:#64748b; font-size:13px; margin-top:4px; }
        .quick-edit-grid { display:grid; grid-template-columns:300px 1fr; gap:18px; align-items:start; }
        .quick-card { background:#fff; border:1px solid #e6ebf0; border-radius:16px; padding:16px; }
        .quick-card h3 { font-size:15px; font-weight:700; margin:0 0 12px; }
        .quick-price-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .quick-departure-list { display:flex; flex-direction:column; gap:12px; }
        .quick-departure-item { border:1px solid #e6ebf0; border-radius:14px; padding:14px; background:#fff; }
        .quick-departure-item .row { --bs-gutter-y: 10px; }
        .quick-empty { color:#64748b; font-size:13px; margin:0 0 10px; }
        @media (max-width: 991px) {
            .quick-edit-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'tours'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Quản lý tour</h1>
                <p class="text-muted mb-0">Quản lý tour, giá bán và lịch khởi hành từ một màn hình gọn hơn.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a class="btn btn-primary" href="<?= site_url('admin/tours/create') ?>">Tạo tour</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Tour</th>
                    <th>Loại</th>
                    <th>Lượt xem</th>
                    <th>Trạng thái</th>
                    <th>Giá</th>
                    <th>Khởi hành gần nhất</th>
                    <th>Cập nhật</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tours)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Chưa có tour.</td></tr>
                <?php endif; ?>
                <?php foreach ($tours as $tour): ?>
                    <?php
                        $tourId = (int) ($tour['id'] ?? 0);
                        $departures = array_values((array) ($tour['departures'] ?? []));
                        $nextDeparture = is_array($tour['next_departure'] ?? null) ? $tour['next_departure'] : null;
                        $basePrice = (float) ($tour['base_price'] ?? 0);
                        $salePrice = (float) ($tour['sale_price'] ?? 0);
                        $displayPrice = $salePrice > 0 ? $salePrice : $basePrice;
                    ?>
                    <tr>
                        <td>#<?= esc((string) $tourId) ?></td>
                        <td><?= esc((string) $tour['name']) ?></td>
                        <td><?= esc((string) $tour['tour_type']) ?></td>
                        <td><?= esc(number_format((int) ($tour['view_count'] ?? 0), 0, ',', '.')) ?></td>
                        <td><?= esc((string) $tour['status']) ?></td>
                        <td>
                            <div class="price-stack">
                                <strong><?= esc(number_format($displayPrice, 0, ',', '.')) ?> đ</strong>
                                <small>
                                    Gốc: <?= esc(number_format($basePrice, 0, ',', '.')) ?> đ
                                    <?php if ($salePrice > 0): ?>
                                        · Sale: <?= esc(number_format($salePrice, 0, ',', '.')) ?> đ
                                    <?php endif; ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <?php if ($nextDeparture): ?>
                                <div class="departure-stack">
                                    <strong><?= esc(date('d/m/Y', strtotime((string) ($nextDeparture['departure_date'] ?? '')))) ?></strong>
                                    <small>
                                        <?= esc(number_format((float) ($nextDeparture['price'] ?? 0), 0, ',', '.')) ?> đ
                                        · <?= esc((string) ($nextDeparture['status'] ?? 'open')) ?>
                                    </small>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Chưa có lịch</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc(app_datetime((string) ($tour['updated_at'] ?? ''))) ?></td>
                        <td class="text-end">
                            <div class="tour-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-quick-edit-toggle" data-target="quick-edit-<?= $tourId ?>">Sửa nhanh</button>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/tours/' . $tourId . '/edit') ?>">Sửa</a>
                                <form method="post" action="<?= site_url('admin/tours/' . $tourId . '/delete') ?>" onsubmit="return confirm('Xóa tour này?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr id="quick-edit-<?= $tourId ?>" class="quick-edit-row">
                        <td colspan="9" class="quick-edit-cell">
                            <form method="post" action="<?= site_url('admin/tours/' . $tourId . '/quick-update') ?>" class="quick-edit-panel">
                                <?= csrf_field() ?>
                                <div class="quick-edit-head">
                                    <div>
                                        <h2 class="quick-edit-title">Sửa nhanh: <?= esc((string) $tour['name']) ?></h2>
                                        <div class="quick-edit-meta">Sửa nhanh giá và toàn bộ lịch khởi hành. Dùng trang sửa đầy đủ khi cần đổi nội dung, media, itinerary hoặc SEO.</div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary js-quick-edit-toggle" data-target="quick-edit-<?= $tourId ?>">Đóng</button>
                                        <button type="submit" class="btn btn-primary">Lưu nhanh</button>
                                    </div>
                                </div>

                                <div class="quick-edit-grid">
                                    <div class="quick-card">
                                        <h3>Giá tour</h3>
                                        <div class="quick-price-grid">
                                            <div>
                                                <label class="form-label">Giá gốc</label>
                                                <input type="number" min="0" name="base_price" class="form-control" value="<?= esc((string) ($tour['base_price'] ?? '')) ?>">
                                            </div>
                                            <div>
                                                <label class="form-label">Giá sale</label>
                                                <input type="number" min="0" name="sale_price" class="form-control" value="<?= esc((string) ($tour['sale_price'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="quick-card">
                                        <h3>Lịch khởi hành</h3>
                                        <div class="quick-departure-list">
                                            <?php if ($departures === []): ?>
                                                <div class="quick-departure-item">
                                                    <p class="quick-empty">Tour này chưa có lịch. Có thể thêm ngay một dòng cơ bản ở đây.</p>
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Ngày đi</label>
                                                            <input type="date" name="departures[0][departure_date]" class="form-control">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Slots</label>
                                                            <input type="number" min="0" name="departures[0][available_slots]" class="form-control">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Giá</label>
                                                            <input type="number" min="0" name="departures[0][price]" class="form-control" value="<?= esc((string) ($displayPrice > 0 ? (int) $displayPrice : '')) ?>">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Price up</label>
                                                            <input type="number" min="0" name="departures[0][price_up]" class="form-control">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Trạng thái</label>
                                                            <select name="departures[0][status]" class="form-select">
                                                                <option value="open">Đang mở</option>
                                                                <option value="closed">Đã đóng</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($departures as $index => $departure): ?>
                                                    <div class="quick-departure-item">
                                                        <div class="row g-3">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Ngày đi</label>
                                                                <input type="date" name="departures[<?= $index ?>][departure_date]" class="form-control" value="<?= esc((string) ($departure['departure_date'] ?? '')) ?>">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">Slots</label>
                                                                <input type="number" min="0" name="departures[<?= $index ?>][available_slots]" class="form-control" value="<?= esc((string) ($departure['available_slots'] ?? '')) ?>">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Giá</label>
                                                                <input type="number" min="0" name="departures[<?= $index ?>][price]" class="form-control" value="<?= esc((string) ($departure['price'] ?? '')) ?>">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">Price up</label>
                                                                <input type="number" min="0" name="departures[<?= $index ?>][price_up]" class="form-control" value="<?= esc((string) ($departure['price_up'] ?? '')) ?>">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">Trạng thái</label>
                                                                <select name="departures[<?= $index ?>][status]" class="form-select">
                                                                    <option value="open" <?= ($departure['status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Đang mở</option>
                                                                    <option value="closed" <?= ($departure['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Đã đóng</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
<script>
document.querySelectorAll('.js-quick-edit-toggle').forEach((button) => {
  button.addEventListener('click', () => {
    const targetId = button.getAttribute('data-target');
    const row = targetId ? document.getElementById(targetId) : null;
    if (!row) return;

    document.querySelectorAll('.quick-edit-row.is-open').forEach((openRow) => {
      if (openRow !== row) {
        openRow.classList.remove('is-open');
      }
    });

    row.classList.toggle('is-open');
  });
});
</script>
</body>
</html>
