<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Booking Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
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
        .confirm-box { border:1px dashed #f0bd4f; border-radius:12px; padding:12px 14px; background:#fffaf0; }
        .status-badge { border-radius:999px; padding:6px 12px; font-size:12px; font-weight:700; display:inline-flex; }
        .status-paid { background:#dff7e8; color:#0f8a4b; }
        .status-pending-transfer, .status-pending-payment { background:#fff4d6; color:#9f6b00; }
        .status-cancelled, .status-failed { background:#ffe2e0; color:#c23d33; }
        .status-draft { background:#e9eef5; color:#516173; }
        pre { white-space:pre-wrap; word-break:break-word; background:#0b1220; color:#dfe7ff; border-radius:14px; padding:18px; max-height:420px; overflow:auto; }
        @media (max-width: 991px) {
            .meta-grid { grid-template-columns:1fr; }
            .history-item .top { flex-direction:column; }
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
$bookingStatus = (string) ($booking['payment_status'] ?? 'draft');
$bookingMethod = (string) ($booking['payment_method'] ?? '');
$amountDue = (float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0);
$amountPaid = (float) ($booking['amount_paid_vnd'] ?? 0);
$amountInputValue = number_format($amountPaid > 0 ? $amountPaid : $amountDue, 0, ',', '.');
$customerNote = trim((string) ($booking['customer_note'] ?? ''));
?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Booking <?= esc((string) ($booking['booking_code'] ?? '')) ?></h1>
                <p class="text-muted mb-0">Chi tiết booking, đối soát thanh toán và lịch sử cập nhật.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Quay lại bookings</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="meta-grid mb-4">
            <div class="meta-item"><small>Tour</small><div class="fw-semibold"><?= esc((string) ($booking['tour_title'] ?? '')) ?></div></div>
            <div class="meta-item"><small>Khách hàng</small><div class="fw-semibold"><?= esc((string) ($booking['customer_name'] ?? '')) ?></div><div><?= esc((string) ($booking['customer_email'] ?? '')) ?> - <?= esc((string) ($booking['customer_phone'] ?? '')) ?></div></div>
            <div class="meta-item">
                <small>Trạng thái thanh toán</small>
                <span class="status-badge status-<?= esc(str_replace('_', '-', $bookingStatus)) ?>"><?= esc($statusLabels[$bookingStatus] ?? $bookingStatus) ?></span>
            </div>
            <div class="meta-item"><small>Phương thức</small><div class="fw-semibold"><?= esc($methodLabels[$bookingMethod] ?? strtoupper($bookingMethod !== '' ? $bookingMethod : '-')) ?> / <?= esc((string) ($booking['payment_plan'] ?? '-')) ?></div></div>
            <div class="meta-item"><small>Tạm tính</small><div class="fw-semibold"><?= esc(number_format((float) ($booking['subtotal_vnd'] ?? $booking['grand_total'] ?? 0), 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Giảm giá</small><div class="fw-semibold"><?= esc(number_format((float) ($booking['discount_amount_vnd'] ?? 0), 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Mã khuyến mãi</small><div class="fw-semibold"><?= esc((string) ($booking['coupon_code'] ?? '-')) ?></div></div>
            <div class="meta-item"><small>Tổng booking</small><div class="fw-semibold"><?= esc(number_format((float) ($booking['grand_total'] ?? 0), 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Cần thu</small><div class="fw-semibold"><?= esc(number_format($amountDue, 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Đã thu</small><div class="fw-semibold"><?= esc(number_format($amountPaid, 0, ',', '.')) ?> đ</div></div>
            <div class="meta-item"><small>Mã giao dịch</small><div class="fw-semibold"><?= esc((string) ($booking['provider_reference'] ?? '-')) ?></div></div>
            <div class="meta-item"><small>Paid at</small><div class="fw-semibold"><?= esc(app_datetime((string) ($booking['paid_at'] ?? ''))) ?: '-' ?></div></div>
            <div class="meta-item"><small>Ngày đi</small><div class="fw-semibold"><?= esc((string) ($booking['departure_label'] ?? '-')) ?></div></div>
            <div class="meta-item"><small>Khách</small><div class="fw-semibold"><?= (int) ($booking['adult_quantity'] ?? 0) ?> NL / <?= (int) ($booking['child_quantity'] ?? 0) ?> TE / <?= (int) ($booking['infant_quantity'] ?? 0) ?> EB</div></div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="meta-item h-100">
                    <small>Cập nhật / đối soát thanh toán</small>
                    <form method="post" action="<?= site_url('admin/bookings/' . (int) $booking['id'] . '/status') ?>">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="payment_status">Trạng thái</label>
                                <select class="form-select" id="payment_status" name="payment_status">
                                    <?php foreach ($statusOptions as $statusOption): ?>
                                        <option value="<?= esc($statusOption) ?>" <?= $bookingStatus === $statusOption ? 'selected' : '' ?>><?= esc($statusLabels[$statusOption] ?? $statusOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="amount_paid_vnd">Số tiền thực thu</label>
                                <input class="form-control" id="amount_paid_vnd" name="amount_paid_vnd" value="<?= esc($amountInputValue) ?>" inputmode="numeric">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="provider_reference">Mã giao dịch / nội dung chuyển khoản</label>
                                <input class="form-control" id="provider_reference" name="provider_reference" value="<?= esc((string) ($booking['provider_reference'] ?? '')) ?>" placeholder="VD: VNPAY transaction no, nội dung VietQR, mã sao kê">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="status_note">Ghi chú nội bộ</label>
                                <textarea class="form-control" id="status_note" name="status_note" rows="3" placeholder="VD: Đã đối chiếu sao kê ngân hàng lúc 10:35, khớp số tiền và nội dung chuyển khoản."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="confirm-box">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="confirm_payment" name="confirm_payment">
                                        <label class="form-check-label fw-semibold" for="confirm_payment">Tôi đã đối soát giao dịch trước khi chuyển sang “Đã thanh toán”.</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="send_booking_email" name="send_booking_email" checked>
                                        <label class="form-check-label" for="send_booking_email">Gửi email cập nhật trạng thái cho khách. Khi chuyển sang đã thanh toán lần đầu, hệ thống gửi email xác nhận thanh toán.</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Lưu trạng thái</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="meta-item h-100">
                    <small>Ghi chú khách hàng</small>
                    <div><?= $customerNote !== '' ? nl2br(esc($customerNote)) : '<span class="text-muted">Không có ghi chú.</span>' ?></div>
                </div>
            </div>
        </div>

        <div class="meta-item mb-4">
            <small>Lịch sử trạng thái</small>
            <?php if (empty($statusLogs)): ?>
                <div class="text-muted">Chưa có lịch sử thay đổi trạng thái hoặc migration log chưa được chạy.</div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach ($statusLogs as $log): ?>
                        <div class="history-item">
                            <div class="top">
                                <div>
                                    <div class="status-line">
                                        <?= esc((string) ($log['from_status'] ?? '')) !== '' ? esc($statusLabels[(string) $log['from_status']] ?? (string) $log['from_status']) . ' -> ' : '' ?><?= esc($statusLabels[(string) ($log['to_status'] ?? '')] ?? (string) ($log['to_status'] ?? '')) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= esc((string) ($log['actor_name'] ?? 'System')) ?>
                                        <?= ! empty($log['actor_email']) ? ' - ' . esc((string) $log['actor_email']) : '' ?>
                                    </div>
                                </div>
                                <div class="text-muted small"><?= esc(app_datetime((string) ($log['created_at'] ?? ''))) ?></div>
                            </div>
                            <?php if (! empty($log['amount_paid_vnd']) || ! empty($log['provider_reference'])): ?>
                                <div class="small text-muted mb-2">
                                    <?php if (! empty($log['amount_paid_vnd'])): ?>
                                        Đã thu: <?= esc(number_format((float) $log['amount_paid_vnd'], 0, ',', '.')) ?> đ
                                    <?php endif; ?>
                                    <?php if (! empty($log['provider_reference'])): ?>
                                        <?= ! empty($log['amount_paid_vnd']) ? ' · ' : '' ?>Mã GD: <?= esc((string) $log['provider_reference']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
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
<?= view('admin/partials/app_end') ?>
</body>
</html>
