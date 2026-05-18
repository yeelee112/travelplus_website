<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Booking Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1180px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .meta-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .meta-item { border:1px solid #edf1f5; border-radius:14px; padding:16px; background:#fbfcfe; }
        .meta-item small { color:#6b778c; display:block; margin-bottom:4px; }
        .history-list { display:grid; gap:12px; }
        .history-item { border:1px solid #edf1f5; border-radius:14px; padding:14px 16px; background:#fbfcfe; }
        .history-item .top { display:flex; justify-content:space-between; gap:12px; margin-bottom:8px; }
        .history-item .status-line { font-weight:700; }
        pre { white-space:pre-wrap; word-break:break-word; background:#0b1220; color:#dfe7ff; border-radius:14px; padding:18px; max-height:420px; overflow:auto; }
        @media (max-width: 991px) {
            .meta-grid { grid-template-columns:1fr; }
            .history-item .top { flex-direction:column; }
        }
    </style>
</head>
<body>
<?php helper('display'); ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Booking <?= esc($booking['booking_code']) ?></h1>
                <p class="text-muted mb-0">Chi tiết booking và thao tác cập nhật trạng thái thanh toán.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Back to bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="meta-grid mb-4">
            <div class="meta-item"><small>Tour</small><div class="fw-semibold"><?= esc($booking['tour_title']) ?></div></div>
            <div class="meta-item"><small>Khách hàng</small><div class="fw-semibold"><?= esc($booking['customer_name']) ?></div><div><?= esc($booking['customer_email']) ?> - <?= esc($booking['customer_phone']) ?></div></div>
            <div class="meta-item"><small>Trạng thái thanh toán</small><div class="fw-semibold"><?= esc((string) $booking['payment_status']) ?></div></div>
            <div class="meta-item"><small>Phương thức</small><div class="fw-semibold"><?= esc((string) ($booking['payment_method'] ?? '-')) ?> / <?= esc((string) ($booking['payment_plan'] ?? '-')) ?></div></div>
            <div class="meta-item"><small>Tổng booking</small><div class="fw-semibold"><?= esc(number_format((float) ($booking['grand_total'] ?? 0), 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Đã thanh toán</small><div class="fw-semibold"><?= esc(number_format((float) ($booking['amount_paid_vnd'] ?? 0), 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Ngày đi</small><div class="fw-semibold"><?= esc((string) ($booking['departure_label'] ?? '-')) ?></div></div>
            <div class="meta-item"><small>Khách</small><div class="fw-semibold"><?= (int) ($booking['adult_quantity'] ?? 0) ?> NL / <?= (int) ($booking['child_quantity'] ?? 0) ?> TE / <?= (int) ($booking['infant_quantity'] ?? 0) ?> EB</div></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="meta-item h-100">
                    <small>Cập nhật trạng thái</small>
                    <form method="post" action="<?= site_url('admin/bookings/' . (int) $booking['id'] . '/status') ?>">
                        <div class="mb-3">
                            <select class="form-select" name="payment_status">
                                <?php foreach (['pending_payment','pending_transfer','paid','cancelled','failed'] as $statusOption): ?>
                                    <option value="<?= esc($statusOption) ?>" <?= ($booking['payment_status'] ?? '') === $statusOption ? 'selected' : '' ?>><?= esc($statusOption) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" name="status_note" rows="3" placeholder="Ghi chú cho lần cập nhật này (không bắt buộc)"></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Lưu trạng thái</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="meta-item h-100">
                    <small>Ghi chú khách hàng</small>
                    <div><?= nl2br(esc((string) ($booking['customer_note'] ?? 'Không có ghi chú.'))) ?></div>
                </div>
            </div>
        </div>

        <div class="meta-item mb-4">
            <small>Lịch sử trạng thái</small>
            <?php if (empty($statusLogs)): ?>
                <div class="text-muted">Chưa có lịch sử thay đổi trạng thái hoặc bảng log chưa được tạo.</div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach ($statusLogs as $log): ?>
                        <div class="history-item">
                            <div class="top">
                                <div>
                                    <div class="status-line">
                                        <?= esc((string) ($log['from_status'] ?? '')) !== '' ? esc((string) $log['from_status']) . ' -> ' : '' ?><?= esc((string) ($log['to_status'] ?? '')) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= esc((string) ($log['actor_name'] ?? 'System')) ?>
                                        <?= ! empty($log['actor_email']) ? ' - ' . esc((string) $log['actor_email']) : '' ?>
                                    </div>
                                </div>
                                <div class="text-muted small"><?= esc(app_datetime((string) ($log['created_at'] ?? ''))) ?></div>
                            </div>
                            <?php if (! empty($log['note'])): ?>
                                <div><?= nl2br(esc((string) $log['note'])) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="meta-item">
            <small>Provider payload</small>
            <pre><?= esc((string) ($booking['provider_payload'] ?? '')) ?></pre>
        </div>
    </div>
</main>
</body>
</html>
