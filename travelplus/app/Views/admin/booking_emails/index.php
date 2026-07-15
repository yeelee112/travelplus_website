<?php
$candidates = is_array($preview['candidates'] ?? null) ? $preview['candidates'] : [];
$previewErrors = is_array($preview['errors'] ?? null) ? $preview['errors'] : [];
$candidateCount = count($candidates);
$sentCount = (int) ($preview['sent'] ?? 0);
$skippedCount = (int) ($preview['skipped'] ?? 0);
$canSend = $candidateCount > 0 && $previewErrors === [];

$statusLabels = [
    'pending_payment' => 'Chờ thanh toán',
    'pending_transfer' => 'Chờ đối soát',
    'paid' => 'Đã thanh toán',
    'confirmed' => 'Đã xác nhận',
    'cancelled' => 'Đã hủy',
];

$statusLabel = static function ($status) use ($statusLabels): string {
    $status = (string) $status;

    return $statusLabels[$status] ?? ($status !== '' ? $status : 'Chưa có trạng thái');
};
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Email booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f5f7fb; color:#172033; }
        .admin-shell { max-width:1320px; margin:28px auto; padding:0 16px; }
        .email-page { display:grid; gap:18px; }
        .email-panel { background:#fff; border:1px solid #e3e9f1; border-radius:16px; box-shadow:0 14px 34px rgba(20,35,66,.06); }
        .email-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; padding:24px; }
        .email-hero h1 { margin:0 0 8px; font-size:28px; line-height:1.2; font-weight:800; color:#071a33; }
        .email-hero p { margin:0; max-width:760px; color:#64748b; line-height:1.55; }
        .email-hero__actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
        .email-flow { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); border-top:1px solid #e8eef5; }
        .email-flow__item { display:grid; grid-template-columns:36px 1fr; gap:12px; padding:18px 24px; border-right:1px solid #e8eef5; }
        .email-flow__item:last-child { border-right:0; }
        .email-flow__number { width:32px; height:32px; display:grid; place-items:center; border-radius:50%; background:#eaf4ff; color:#006bb7; font-weight:800; }
        .email-flow__item strong { display:block; margin-bottom:2px; color:#0f243f; }
        .email-flow__item span { display:block; color:#64748b; font-size:13px; line-height:1.45; }
        .email-filter { padding:20px 24px; display:grid; grid-template-columns:minmax(0,1fr) 150px auto; gap:14px; align-items:end; }
        .email-filter__type { min-height:62px; border:1px solid #dde7f2; border-radius:12px; padding:12px 14px; background:#fbfdff; }
        .email-filter__type small, .email-filter label span { display:block; margin-bottom:5px; color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .email-filter__type strong { display:block; color:#10233d; }
        .email-filter__type em { display:block; margin-top:3px; color:#64748b; font-style:normal; font-size:13px; }
        .email-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; padding:0 24px 20px; }
        .email-stat { border:1px solid #e1e8f0; border-radius:14px; padding:16px; background:#fbfcfe; }
        .email-stat span { display:block; color:#64748b; font-size:13px; font-weight:800; margin-bottom:8px; }
        .email-stat strong { display:block; color:#06172c; font-size:30px; line-height:1; }
        .email-send { padding:22px 24px 24px; }
        .email-send__head { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; margin-bottom:16px; }
        .email-send__head h2 { margin:0 0 5px; font-size:22px; line-height:1.25; font-weight:800; color:#071a33; }
        .email-send__head p { margin:0; color:#64748b; }
        .email-toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:12px; margin-bottom:14px; border:1px solid #dce8f4; border-radius:14px; background:#f8fbff; }
        .email-toolbar__selection { color:#334155; font-weight:700; }
        .email-toolbar__selection strong { color:#005fae; }
        .email-toolbar__actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
        .email-list { display:grid; gap:10px; margin-bottom:16px; }
        .email-row { display:grid; grid-template-columns:36px minmax(150px,.75fr) minmax(190px,.95fr) minmax(220px,1.2fr) minmax(130px,.65fr) minmax(130px,.65fr); gap:14px; align-items:center; padding:14px; border:1px solid #e2eaf3; border-radius:14px; background:#fff; transition:border-color .16s ease, box-shadow .16s ease, background .16s ease; }
        .email-row:hover { border-color:#b9d7f4; box-shadow:0 10px 24px rgba(20,35,66,.06); }
        .email-row.is-selected { border-color:#1682d4; background:#f5fbff; box-shadow:0 12px 26px rgba(22,130,212,.11); }
        .email-row__check { display:flex; justify-content:center; }
        .email-row__booking strong { display:block; margin-bottom:4px; color:#071a33; }
        .email-row__booking a { display:inline-flex; color:#006bb7; font-size:13px; font-weight:800; text-decoration:none; }
        .email-row__label { display:block; color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:3px; }
        .email-row__value { display:block; color:#10233d; font-weight:700; overflow-wrap:anywhere; }
        .email-row__muted { display:block; color:#64748b; font-size:13px; overflow-wrap:anywhere; }
        .email-pill { display:inline-flex; min-height:28px; align-items:center; padding:6px 10px; border-radius:999px; background:#eef6ff; color:#006bb7; font-size:12px; font-weight:800; }
        .email-confirm { display:flex; gap:10px; align-items:flex-start; padding:14px; margin:0 0 14px; border:1px solid #dce8f4; border-radius:14px; background:#fbfdff; }
        .email-confirm label { font-weight:800; color:#10233d; }
        .email-confirm small { display:block; margin-top:2px; color:#64748b; }
        .email-empty { padding:34px 18px; text-align:center; border:1px dashed #cbd8e6; border-radius:14px; color:#64748b; background:#fbfdff; }
        .email-footer { display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
        .btn-primary { background:#006bb7; border-color:#006bb7; }
        .btn-primary:hover { background:#005da0; border-color:#005da0; }
        .btn-danger { background:#d94141; border-color:#d94141; }
        @media (max-width: 1100px) {
            .email-row { grid-template-columns:36px minmax(220px,1fr) minmax(180px,1fr); }
            .email-row__tour, .email-row__date { grid-column:auto; }
        }
        @media (max-width: 767px) {
            .admin-shell { margin:16px auto; padding:0 12px; }
            .email-hero, .email-send { padding:18px; }
            .email-hero, .email-send__head { display:grid; }
            .email-hero h1 { font-size:24px; }
            .email-flow, .email-filter, .email-summary { grid-template-columns:1fr; }
            .email-flow__item { border-right:0; border-bottom:1px solid #e8eef5; padding:16px 18px; }
            .email-flow__item:last-child { border-bottom:0; }
            .email-filter { padding:18px; }
            .email-summary { padding:0 18px 18px; }
            .email-row { grid-template-columns:32px 1fr; gap:10px 12px; padding:12px; }
            .email-row__customer, .email-row__tour, .email-row__status, .email-row__date { grid-column:2; }
            .email-footer .btn, .email-toolbar__actions .btn { flex:1 1 auto; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'booking_emails']) ?>
<main class="admin-shell">
    <div class="email-page">
        <section class="email-panel">
            <div class="email-hero">
                <div>
                    <h1>Email booking tự động</h1>
                    <p>Trang này dùng để gửi email nhắc thanh toán khi hosting không chạy được command hoặc cron. Hệ thống luôn cho xem danh sách trước, ghi log vào <code>booking_email_logs</code> và không gửi lặp cùng một email cho cùng booking.</p>
                </div>
                <div class="email-hero__actions">
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Quay lại bookings</a>
                </div>
            </div>
            <div class="email-flow">
                <div class="email-flow__item">
                    <div class="email-flow__number">1</div>
                    <div><strong>Lọc hàng đợi</strong><span>Chọn số lượng booking cần kiểm tra trong lần này.</span></div>
                </div>
                <div class="email-flow__item">
                    <div class="email-flow__number">2</div>
                    <div><strong>Chọn booking</strong><span>Chọn từng dòng hoặc bấm Chọn tất cả nếu muốn gửi toàn bộ danh sách.</span></div>
                </div>
                <div class="email-flow__item">
                    <div class="email-flow__number">3</div>
                    <div><strong>Xác nhận gửi</strong><span>Tick xác nhận rồi bấm gửi, hệ thống sẽ tự bỏ qua email đã gửi trước đó.</span></div>
                </div>
            </div>
        </section>

        <?php if (! empty($success)): ?>
            <div class="alert alert-success mb-0"><?= esc((string) $success) ?></div>
        <?php endif; ?>
        <?php if (! empty($error)): ?>
            <div class="alert alert-danger mb-0"><?= esc((string) $error) ?></div>
        <?php endif; ?>
        <?php foreach ($previewErrors as $previewError): ?>
            <div class="alert alert-warning mb-0"><?= esc((string) $previewError) ?></div>
        <?php endforeach; ?>

        <section class="email-panel">
            <form class="email-filter" method="get" action="<?= site_url('admin/booking-emails') ?>#preview-list">
                <div class="email-filter__type">
                    <small>Loại email</small>
                    <strong>Nhắc thanh toán</strong>
                    <em>Áp dụng cho booking đang chờ thanh toán hoặc chờ đối soát.</em>
                </div>
                <label>
                    <span>Giới hạn</span>
                    <input class="form-control" type="number" min="1" max="200" name="limit" value="<?= esc((string) $limit, 'attr') ?>">
                </label>
                <button class="btn btn-primary" type="submit">Cập nhật hàng đợi</button>
            </form>

            <div class="email-summary">
                <div class="email-stat"><span>Đủ điều kiện gửi</span><strong><?= esc((string) $candidateCount) ?></strong></div>
                <div class="email-stat"><span>Đã gửi lần này</span><strong><?= esc((string) $sentCount) ?></strong></div>
                <div class="email-stat"><span>Đã gửi trước nên bỏ qua</span><strong><?= esc((string) $skippedCount) ?></strong></div>
            </div>
        </section>

        <section class="email-panel" id="preview-list">
            <form class="email-send" method="post" action="<?= site_url('admin/booking-emails/send') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="<?= esc($type, 'attr') ?>">
                <input type="hidden" name="limit" value="<?= esc((string) $limit, 'attr') ?>">

                <div class="email-send__head">
                    <div>
                        <h2>Danh sách cần gửi</h2>
                        <p>Kiểm tra đúng khách, đúng tour và đúng trạng thái trước khi gửi email nhắc thanh toán.</p>
                    </div>
                    <span class="email-pill">Nhắc thanh toán</span>
                </div>

                <div class="email-toolbar">
                    <div class="email-toolbar__selection">
                        Đã chọn <strong data-selected-count>0</strong>/<span data-total-count><?= esc((string) $candidateCount) ?></span> booking
                    </div>
                    <div class="email-toolbar__actions">
                        <button class="btn btn-outline-primary btn-sm" type="button" data-select-all-button <?= $candidateCount === 0 ? 'disabled' : '' ?>>Chọn tất cả</button>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-clear-selection-button <?= $candidateCount === 0 ? 'disabled' : '' ?>>Bỏ chọn</button>
                    </div>
                </div>

                <?php if ($candidateCount > 0): ?>
                    <div class="email-list">
                        <?php foreach ($candidates as $candidate): ?>
                            <?php
                            $bookingId = (int) ($candidate['id'] ?? 0);
                            $bookingCode = (string) ($candidate['booking_code'] ?? '');
                            ?>
                            <article class="email-row" data-booking-email-row>
                                <div class="email-row__check">
                                    <input class="form-check-input" type="checkbox" name="booking_ids[]" value="<?= esc((string) $bookingId, 'attr') ?>" data-booking-email-checkbox aria-label="Chọn booking <?= esc($bookingCode, 'attr') ?>">
                                </div>
                                <div class="email-row__booking">
                                    <span class="email-row__label">Booking</span>
                                    <strong><?= esc($bookingCode !== '' ? $bookingCode : '#' . $bookingId) ?></strong>
                                    <?php if ($bookingId > 0): ?>
                                        <a href="<?= site_url('admin/bookings/' . $bookingId) ?>" target="_blank" rel="noopener">Mở chi tiết</a>
                                    <?php endif; ?>
                                </div>
                                <div class="email-row__customer">
                                    <span class="email-row__label">Khách hàng</span>
                                    <span class="email-row__value"><?= esc((string) ($candidate['customer'] ?? '')) ?></span>
                                    <span class="email-row__muted"><?= esc((string) ($candidate['email'] ?? '')) ?></span>
                                </div>
                                <div class="email-row__tour">
                                    <span class="email-row__label">Tour</span>
                                    <span class="email-row__value"><?= esc((string) ($candidate['tour'] ?? '')) ?></span>
                                </div>
                                <div class="email-row__status">
                                    <span class="email-row__label">Trạng thái</span>
                                    <span class="email-row__value"><?= esc($statusLabel($candidate['status'] ?? '')) ?></span>
                                </div>
                                <div class="email-row__date">
                                    <span class="email-row__label">Khởi hành</span>
                                    <span class="email-row__value"><?= esc((string) ($candidate['departure'] ?? '')) ?></span>
                                    <?php if (! empty($candidate['extra'])): ?>
                                        <span class="email-row__muted"><?= esc((string) $candidate['extra']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="email-empty">Không có booking nào cần gửi email theo điều kiện hiện tại.</div>
                <?php endif; ?>

                <div class="email-confirm">
                    <input class="form-check-input mt-1" type="checkbox" value="1" id="confirm_send" name="confirm_send" data-confirm-send <?= $canSend ? '' : 'disabled' ?>>
                    <div>
                        <label for="confirm_send">Tôi đã kiểm tra danh sách và muốn gửi email cho các booking đã chọn.</label>
                        <small>Nút gửi chỉ bật khi đã chọn ít nhất một booking và đã tick xác nhận.</small>
                    </div>
                </div>

                <div class="email-footer">
                    <button class="btn btn-danger" type="submit" data-send-button data-base-disabled="<?= $canSend ? '0' : '1' ?>" disabled>Gửi cho booking đã chọn</button>
                </div>
            </form>
        </section>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
<script>
(function () {
    const checkboxes = Array.from(document.querySelectorAll('[data-booking-email-checkbox]'));
    const rows = Array.from(document.querySelectorAll('[data-booking-email-row]'));
    const selectedCount = document.querySelector('[data-selected-count]');
    const selectAllButton = document.querySelector('[data-select-all-button]');
    const clearButton = document.querySelector('[data-clear-selection-button]');
    const confirmInput = document.querySelector('[data-confirm-send]');
    const sendButton = document.querySelector('[data-send-button]');

    function updateState() {
        const selected = checkboxes.filter((checkbox) => checkbox.checked).length;

        if (selectedCount) {
            selectedCount.textContent = String(selected);
        }

        rows.forEach((row) => {
            const checkbox = row.querySelector('[data-booking-email-checkbox]');
            row.classList.toggle('is-selected', Boolean(checkbox && checkbox.checked));
        });

        if (sendButton) {
            const baseDisabled = sendButton.dataset.baseDisabled === '1';
            const confirmed = Boolean(confirmInput && confirmInput.checked);
            sendButton.disabled = baseDisabled || selected === 0 || !confirmed;
        }
    }

    selectAllButton?.addEventListener('click', () => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = true;
        });
        updateState();
    });

    clearButton?.addEventListener('click', () => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
        });
        updateState();
    });

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateState));
    confirmInput?.addEventListener('change', updateState);
    updateState();
})();
</script>
</body>
</html>
