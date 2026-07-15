<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Mã khuyến mãi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1360px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:20px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .promo-hero { display:flex; justify-content:space-between; gap:20px; flex-wrap:wrap; margin-bottom:24px; }
        .promo-hero__copy { max-width:720px; }
        .promo-hero__eyebrow { font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#0ea5e9; margin-bottom:8px; }
        .promo-actions { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-start; }
        .promo-stats { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:14px; margin-bottom:20px; }
        .promo-stat { background:#fbfcfe; border:1px solid #e5ebf2; border-radius:16px; padding:18px; }
        .promo-stat small { display:block; color:#6b778c; margin-bottom:6px; }
        .promo-stat strong { font-size:28px; line-height:1; }
        .promo-filter { margin-bottom:22px; }
        .promo-filter .form-label { font-size:13px; font-weight:600; color:#516173; }
        .promo-table td, .promo-table th { vertical-align:middle; }
        .promo-badge { display:inline-flex; align-items:center; border-radius:999px; padding:6px 10px; font-size:12px; font-weight:700; }
        .promo-badge--all { background:#e8f7ef; color:#127a47; }
        .promo-badge--specific { background:#eef6ff; color:#0b79d0; }
        .promo-badge--active { background:#dcfce7; color:#166534; }
        .promo-badge--inactive { background:#f1f5f9; color:#475569; }
        .promo-actions-inline { display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap; }
        .promo-muted { color:#6b778c; font-size:13px; }
        @media (max-width: 991px) {
            .promo-stats { grid-template-columns:repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .promo-stats { grid-template-columns:1fr; }
        }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'promotion_codes'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="promo-hero">
            <div class="promo-hero__copy">
                <div class="promo-hero__eyebrow">Mã khuyến mãi</div>
                <h1 class="h3 mb-2">Quản lý mã khuyến mãi</h1>
                <p class="text-muted mb-0">Theo dõi mã đang bật, phạm vi áp dụng toàn site hay theo tour, và thao tác nhanh ngay trên một màn hình.</p>
            </div>
            <div class="promo-actions">
                <a class="btn btn-primary" href="<?= site_url('admin/promotion-codes/create') ?>">Tạo mã mới</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="promo-stats">
            <div class="promo-stat"><small>Tổng mã</small><strong><?= esc((string) ($stats['total'] ?? 0)) ?></strong></div>
            <div class="promo-stat"><small>Đang bật</small><strong><?= esc((string) ($stats['active'] ?? 0)) ?></strong></div>
            <div class="promo-stat"><small>Toàn site</small><strong><?= esc((string) ($stats['all'] ?? 0)) ?></strong></div>
            <div class="promo-stat"><small>Theo tour</small><strong><?= esc((string) ($stats['specific'] ?? 0)) ?></strong></div>
        </div>

        <form class="promo-filter" method="get" action="<?= site_url('admin/promotion-codes') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label">Tìm mã</label>
                    <input type="text" class="form-control" name="q" value="<?= esc((string) ($keyword ?? '')) ?>" placeholder="Code, tên hiển thị, mô tả">
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <option value="active"<?= ($status ?? '') === 'active' ? ' selected' : '' ?>>Đang bật</option>
                        <option value="inactive"<?= ($status ?? '') === 'inactive' ? ' selected' : '' ?>>Tạm dừng</option>
                    </select>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Phạm vi</label>
                    <select class="form-select" name="scope">
                        <option value="">Tất cả</option>
                        <option value="all"<?= ($scope ?? '') === 'all' ? ' selected' : '' ?>>Toàn site</option>
                        <option value="specific"<?= ($scope ?? '') === 'specific' ? ' selected' : '' ?>>Theo tour</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="<?= site_url('admin/promotion-codes') ?>" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover promo-table">
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Mã giảm</th>
                    <th>Phạm vi</th>
                    <th>Điều kiện</th>
                    <th>Đã dùng</th>
                    <th>Hiệu lực</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($codes)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Chưa có mã khuyến mãi phù hợp bộ lọc.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($codes as $code): ?>
                    <?php
                    $discountType = (string) ($code['discount_type'] ?? 'fixed');
                    $discountLabel = $discountType === 'percent'
                        ? rtrim(rtrim(number_format((float) ($code['discount_value'] ?? 0), 2, '.', ''), '0'), '.') . '%'
                        : number_format((float) ($code['discount_value'] ?? 0), 0, ',', '.') . ' đ';
                    if ($discountType === 'percent' && ! empty($code['max_discount_amount'])) {
                        $discountLabel .= ' · tối đa ' . number_format((float) $code['max_discount_amount'], 0, ',', '.') . ' đ';
                    }
                    $usageLimit = (int) ($code['usage_limit'] ?? 0);
                    $usageLabel = (int) ($code['used_count'] ?? 0) . ($usageLimit > 0 ? ' / ' . $usageLimit : ' / không giới hạn');
                    $assignedTourCount = (int) ($code['assigned_tour_count'] ?? 0);
                    $bookingUsageCount = (int) ($code['booking_usage_count'] ?? 0);
                    $startsAt = trim((string) ($code['starts_at'] ?? ''));
                    $endsAt = trim((string) ($code['ends_at'] ?? ''));
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc((string) ($code['code'] ?? '')) ?></div>
                            <div class="promo-muted"><?= esc((string) ($code['name'] ?? '')) ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= esc($discountLabel) ?></div>
                            <?php if (! empty($code['description'])): ?>
                                <div class="promo-muted"><?= esc((string) $code['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($assignedTourCount > 0): ?>
                                <span class="promo-badge promo-badge--specific">Theo <?= esc((string) $assignedTourCount) ?> tour</span>
                            <?php else: ?>
                                <span class="promo-badge promo-badge--all">Toàn site</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>Đơn tối thiểu: <strong><?= esc(number_format((float) ($code['min_order_amount'] ?? 0), 0, ',', '.')) ?> đ</strong></div>
                            <div class="promo-muted"><?= $usageLimit > 0 ? 'Giới hạn ' . esc((string) $usageLimit) . ' lượt' : 'Không giới hạn lượt' ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= esc($usageLabel) ?></div>
                            <div class="promo-muted"><?= esc((string) $bookingUsageCount) ?> booking có dùng mã</div>
                        </td>
                        <td>
                            <div><?= $startsAt !== '' ? esc(app_datetime($startsAt)) : 'Dùng ngay' ?></div>
                            <div class="promo-muted"><?= $endsAt !== '' ? esc(app_datetime($endsAt)) : 'Không giới hạn' ?></div>
                        </td>
                        <td>
                            <?php if ((int) ($code['is_active'] ?? 0) === 1): ?>
                                <span class="promo-badge promo-badge--active">Đang bật</span>
                            <?php else: ?>
                                <span class="promo-badge promo-badge--inactive">Tạm dừng</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="promo-actions-inline">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/promotion-codes/' . (int) $code['id'] . '/edit') ?>">Sửa</a>
                                <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/promotion-codes/' . (int) $code['id'] . '/clone') ?>">Nhân bản</a>
                                <form method="post" action="<?= site_url('admin/promotion-codes/' . (int) $code['id'] . '/toggle') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm <?= (int) ($code['is_active'] ?? 0) === 1 ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                        <?= (int) ($code['is_active'] ?? 0) === 1 ? 'Tạm dừng' : 'Bật lại' ?>
                                    </button>
                                </form>
                                <form method="post" action="<?= site_url('admin/promotion-codes/' . (int) $code['id'] . '/delete') ?>" onsubmit="return confirm('Xóa mã khuyến mãi này?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
