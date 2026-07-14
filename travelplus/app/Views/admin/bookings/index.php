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
        .reconcile-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin-bottom:22px; }
        .reconcile-card { display:block; border:1px solid #e4eaf0; border-radius:16px; padding:16px; background:#fbfcfe; color:inherit; text-decoration:none; }
        .reconcile-card:hover, .reconcile-card.is-active { border-color:#0d6efd; background:#f3f8ff; }
        .reconcile-card small { color:#687386; font-weight:700; display:block; margin-bottom:8px; }
        .reconcile-card strong { font-size:28px; line-height:1; }
        .money-stack { min-width:150px; }
        .money-stack div { line-height:1.35; }
        @media (max-width: 991px) {
            .reconcile-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        @media (max-width: 575px) {
            .reconcile-grid { grid-template-columns:1fr; }
            .admin-card { padding:20px; }
        }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'bookings'; ?>
<?php helper('display'); ?>
<?php
$statusLabels = [
    'draft' => 'Draft',
    'pending_payment' => 'Chờ thanh toán',
    'pending_transfer' => 'Chờ chuyển khoản',
    'paid' => 'Đã thanh toán',
    'cancelled' => 'Đã huỷ',
    'failed' => 'Thất bại',
];
$methodLabels = [
    'paypal' => 'PayPal',
    'vnpay' => 'VNPAY',
    'vietqr' => 'VietQR',
];
$filterBase = static function (array $overrides = []) use ($status, $method, $keyword): string {
    return site_url('admin/bookings?' . http_build_query(array_filter(array_merge([
        'status' => $status,
        'method' => $method,
        'q' => $keyword,
    ], $overrides), static fn ($value) => $value !== '' && $value !== null)));
};
$exportQuery = http_build_query(array_filter([
    'status' => $status,
    'method' => $method,
    'reconciliation' => $reconciliation,
    'q' => $keyword,
], static fn ($value) => $value !== '' && $value !== null));
?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Admin bookings</h1>
                <p class="text-muted mb-0">Theo dõi booking, lọc giao dịch cần đối soát và xác nhận thanh toán VietQR/VNPAY.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/promotion-codes') ?>">Promotion codes</a>
                <a class="btn btn-outline-primary" href="<?= site_url('admin/bookings/export' . ($exportQuery !== '' ? '?' . $exportQuery : '')) ?>">Export CSV</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('/') ?>">Home</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="reconcile-grid">
            <a class="reconcile-card <?= $reconciliation === 'needs_reconciliation' ? 'is-active' : '' ?>" href="<?= $filterBase(['reconciliation' => 'needs_reconciliation', 'status' => '', 'method' => '']) ?>">
                <small>Cần đối soát VietQR/VNPAY</small>
                <strong><?= esc((string) ($reconciliationStats['needs_reconciliation'] ?? 0)) ?></strong>
            </a>
            <a class="reconcile-card <?= $status === 'pending_transfer' ? 'is-active' : '' ?>" href="<?= $filterBase(['reconciliation' => '', 'status' => 'pending_transfer']) ?>">
                <small>Chờ chuyển khoản</small>
                <strong><?= esc((string) ($reconciliationStats['pending_transfer'] ?? 0)) ?></strong>
            </a>
            <a class="reconcile-card <?= $status === 'pending_payment' ? 'is-active' : '' ?>" href="<?= $filterBase(['reconciliation' => '', 'status' => 'pending_payment']) ?>">
                <small>Chờ thanh toán</small>
                <strong><?= esc((string) ($reconciliationStats['pending_payment'] ?? 0)) ?></strong>
            </a>
            <a class="reconcile-card <?= $reconciliation === 'failed_or_cancelled' ? 'is-active' : '' ?>" href="<?= $filterBase(['reconciliation' => 'failed_or_cancelled', 'status' => '', 'method' => '']) ?>">
                <small>Lỗi / huỷ online</small>
                <strong><?= esc((string) ($reconciliationStats['failed_or_cancelled'] ?? 0)) ?></strong>
            </a>
        </div>

        <form class="row g-3 mb-4" method="get" action="<?= site_url('admin/bookings') ?>">
            <div class="col-lg-4 col-md-6">
                <input class="form-control" type="text" name="q" value="<?= esc($keyword) ?>" placeholder="Tìm mã booking, tour, khách, email, SĐT, mã giao dịch">
            </div>
            <div class="col-lg-2 col-md-6">
                <select class="form-select" name="status">
                    <option value="">Tất cả trạng thái</option>
                    <?php foreach ($statusOptions as $statusOption): ?>
                        <option value="<?= esc($statusOption) ?>" <?= $status === $statusOption ? 'selected' : '' ?>><?= esc($statusLabels[$statusOption] ?? $statusOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <select class="form-select" name="method">
                    <option value="">Tất cả phương thức</option>
                    <?php foreach ($methodOptions as $methodOption): ?>
                        <option value="<?= esc($methodOption) ?>" <?= $method === $methodOption ? 'selected' : '' ?>><?= esc($methodLabels[$methodOption] ?? strtoupper($methodOption)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <select class="form-select" name="reconciliation">
                    <option value="">Tất cả đối soát</option>
                    <option value="needs_reconciliation" <?= $reconciliation === 'needs_reconciliation' ? 'selected' : '' ?>>Cần đối soát</option>
                    <option value="online_paid" <?= $reconciliation === 'online_paid' ? 'selected' : '' ?>>Online đã thanh toán</option>
                    <option value="failed_or_cancelled" <?= $reconciliation === 'failed_or_cancelled' ? 'selected' : '' ?>>Lỗi / huỷ</option>
                </select>
            </div>
            <div class="col-lg-2 d-flex gap-2">
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
                    <th>Mã giao dịch</th>
                    <th>Ngày tạo</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Chưa có booking phù hợp.</td></tr>
                <?php endif; ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $bookingStatus = (string) ($booking['payment_status'] ?? 'draft');
                    $statusClass = 'status-' . str_replace('_', '-', $bookingStatus);
                    $bookingMethod = (string) ($booking['payment_method'] ?? '');
                    ?>
                    <tr>
                        <td><strong><?= esc((string) ($booking['booking_code'] ?? '')) ?></strong></td>
                        <td>
                            <div class="fw-semibold"><?= esc((string) ($booking['tour_title'] ?? '')) ?></div>
                            <small class="text-muted"><?= esc((string) ($booking['departure_label'] ?? '')) ?></small>
                        </td>
                        <td>
                            <div><?= esc((string) ($booking['customer_name'] ?? '')) ?></div>
                            <small class="text-muted"><?= esc((string) ($booking['customer_email'] ?? '')) ?></small>
                        </td>
                        <td>
                            <span class="status-badge <?= esc($statusClass) ?>"><?= esc($statusLabels[$bookingStatus] ?? $bookingStatus) ?></span>
                            <div><small class="text-muted"><?= esc($methodLabels[$bookingMethod] ?? strtoupper($bookingMethod !== '' ? $bookingMethod : '-')) ?> / <?= esc((string) ($booking['payment_plan'] ?? '-')) ?></small></div>
                        </td>
                        <td class="money-stack">
                            <div><strong><?= esc(number_format((float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0), 0, ',', '.')) ?> đ</strong></div>
                            <small class="text-muted">Đã thu: <?= esc(number_format((float) ($booking['amount_paid_vnd'] ?? 0), 0, ',', '.')) ?> đ</small>
                        </td>
                        <td><small class="text-muted"><?= esc((string) ($booking['provider_reference'] ?? '-')) ?></small></td>
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
