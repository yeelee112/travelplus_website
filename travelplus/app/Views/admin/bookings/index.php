<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1380px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .table td, .table th { vertical-align:middle; }
        .status-badge { border-radius:999px; padding:6px 12px; font-size:12px; font-weight:700; display:inline-flex; }
        .status-paid { background:#dff7e8; color:#0f8a4b; }
        .status-pending-transfer, .status-pending-payment { background:#fff4d6; color:#9f6b00; }
        .status-cancelled, .status-failed { background:#ffe2e0; color:#c23d33; }
        .status-draft { background:#e9eef5; color:#516173; }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'bookings'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Admin bookings</h1>
                <p class="text-muted mb-0">Theo dõi đơn tour, xác nhận chuyển khoản và kiểm tra trạng thái thanh toán.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/promotion-codes') ?>">Promotion codes</a>
                <a class="btn btn-outline-primary" href="<?= site_url('admin/bookings/export?' . http_build_query(array_filter(['status' => $status, 'q' => $keyword], static fn($value) => $value !== ''))) ?>">Export CSV</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('/') ?>">Home</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <form class="row g-3 mb-4" method="get" action="<?= site_url('admin/bookings') ?>">
            <div class="col-md-4">
                <input class="form-control" type="text" name="q" value="<?= esc($keyword) ?>" placeholder="Tìm theo mã booking, tour, tên khách, email">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <?php foreach (['draft','pending_payment','pending_transfer','paid','cancelled','failed'] as $statusOption): ?>
                        <option value="<?= esc($statusOption) ?>" <?= $status === $statusOption ? 'selected' : '' ?>><?= esc($statusOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Mã booking</th>
                    <th>Tour</th>
                    <th>Khách hàng</th>
                    <th>Thanh toán</th>
                    <th>Số tiền</th>
                    <th>Ngày tạo</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Chưa có booking nào.</td></tr>
                <?php endif; ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php $statusClass = 'status-' . str_replace('_', '-', (string) ($booking['payment_status'] ?? 'draft')); ?>
                    <tr>
                        <td><strong><?= esc($booking['booking_code']) ?></strong></td>
                        <td>
                            <div class="fw-semibold"><?= esc($booking['tour_title']) ?></div>
                            <small class="text-muted"><?= esc((string) ($booking['departure_label'] ?? '')) ?></small>
                        </td>
                        <td>
                            <div><?= esc($booking['customer_name']) ?></div>
                            <small class="text-muted"><?= esc($booking['customer_email']) ?></small>
                        </td>
                        <td>
                            <span class="status-badge <?= esc($statusClass) ?>"><?= esc((string) ($booking['payment_status'] ?? 'draft')) ?></span>
                            <div><small class="text-muted"><?= esc((string) ($booking['payment_method'] ?? '-')) ?> / <?= esc((string) ($booking['payment_plan'] ?? '-')) ?></small></div>
                        </td>
                        <td><?= esc(number_format((float) ($booking['grand_total'] ?? 0), 0, ',', '.')) ?> đ</td>
                        <td><?= esc(app_datetime((string) ($booking['created_at'] ?? ''))) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/bookings/' . (int) $booking['id']) ?>">Chi tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (isset($pager)): ?>
            <div class="mt-4"><?= $pager->links() ?></div>
        <?php endif; ?>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
