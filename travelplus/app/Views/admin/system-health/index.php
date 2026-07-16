<?php
$summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
$groups = is_array($report['groups'] ?? null) ? $report['groups'] : [];
$statusLabels = [
    'ok' => 'Tốt',
    'warning' => 'Cảnh báo',
    'error' => 'Cần xử lý',
];
$overallStatus = (string) ($summary['status'] ?? 'warning');
$overallCopy = [
    'ok' => 'Hệ thống đang sẵn sàng',
    'warning' => 'Có cấu hình nên kiểm tra',
    'error' => 'Có vấn đề cần xử lý',
][$overallStatus] ?? 'Đã hoàn tất kiểm tra';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Trạng thái hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f5f7fa; color:#172033; }
        .health-page { max-width:1260px; margin:28px auto; padding:0 16px; display:grid; gap:18px; }
        .health-panel { background:#fff; border:1px solid #dfe6ee; border-radius:8px; box-shadow:0 8px 24px rgba(24,39,75,.05); }
        .health-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:24px; align-items:start; padding:24px; }
        .health-hero__eyebrow { display:block; margin-bottom:8px; color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; }
        .health-hero h1 { margin:0 0 8px; color:#0b1f38; font-size:28px; font-weight:800; line-height:1.2; }
        .health-hero p { max-width:760px; margin:0; color:#65748a; line-height:1.55; }
        .health-hero__actions { display:flex; gap:10px; align-items:center; }
        .health-summary { display:grid; grid-template-columns:minmax(220px,1.35fr) repeat(3,minmax(150px,1fr)); border-top:1px solid #e5ebf2; }
        .health-summary__overall, .health-summary__item { min-width:0; padding:18px 20px; border-right:1px solid #e5ebf2; }
        .health-summary > :last-child { border-right:0; }
        .health-summary__overall span, .health-summary__item span { display:block; margin-bottom:7px; color:#69788d; font-size:12px; font-weight:800; text-transform:uppercase; }
        .health-summary__overall strong { display:flex; align-items:center; gap:9px; color:#0b1f38; font-size:18px; }
        .health-summary__item strong { display:block; color:#0b1f38; font-size:27px; line-height:1; }
        .health-summary__item small { display:block; margin-top:7px; color:#77859a; }
        .health-summary__item.is-ok strong { color:#177245; }
        .health-summary__item.is-warning strong { color:#9a6200; }
        .health-summary__item.is-error strong { color:#b4232f; }
        .health-dot { width:10px; height:10px; flex:0 0 auto; border-radius:50%; background:#97a6b6; box-shadow:0 0 0 4px #edf1f5; }
        .health-dot.is-ok { background:#23905b; box-shadow:0 0 0 4px #e4f4eb; }
        .health-dot.is-warning { background:#d08a11; box-shadow:0 0 0 4px #fff1d5; }
        .health-dot.is-error { background:#d13b46; box-shadow:0 0 0 4px #fde9eb; }
        .health-group__head { padding:20px 22px 16px; border-bottom:1px solid #e5ebf2; }
        .health-group__head h2 { margin:0 0 5px; color:#0b1f38; font-size:20px; font-weight:800; }
        .health-group__head p { margin:0; color:#69788d; line-height:1.5; }
        .health-check { display:grid; grid-template-columns:minmax(180px,.75fr) minmax(130px,.45fr) minmax(280px,1.45fr); gap:20px; align-items:start; padding:18px 22px; border-bottom:1px solid #edf1f5; }
        .health-check:last-child { border-bottom:0; }
        .health-check__label { display:flex; align-items:center; gap:10px; min-width:0; color:#1b2b41; font-weight:800; }
        .health-check__value { color:#33445c; font-weight:800; overflow-wrap:anywhere; }
        .health-check__copy { min-width:0; }
        .health-check__copy p { margin:0; color:#53647a; line-height:1.5; }
        .health-check__action { display:flex; gap:7px; margin-top:7px; color:#8b5b05; font-size:13px; line-height:1.45; }
        .health-check__action strong { flex:0 0 auto; color:#6f4600; }
        .health-check.is-error { background:#fffafb; }
        .health-check.is-warning { background:#fffdf8; }
        .health-check.is-ok .health-check__action { display:none; }
        .health-status { display:inline-flex; align-items:center; gap:7px; width:max-content; padding:5px 9px; border:1px solid #dbe4ec; border-radius:6px; background:#f8fafc; color:#53647a; font-size:12px; font-weight:800; }
        .health-status.is-ok { border-color:#bfe3ce; background:#edf8f1; color:#176d43; }
        .health-status.is-warning { border-color:#f1d297; background:#fff7e8; color:#895500; }
        .health-status.is-error { border-color:#f1bec3; background:#fff0f1; color:#aa2632; }
        .health-footnote { color:#718096; font-size:13px; text-align:right; }
        @media (max-width: 991px) {
            .health-summary { grid-template-columns:1fr 1fr; }
            .health-summary > * { border-bottom:1px solid #e5ebf2; }
            .health-summary > :nth-child(2n) { border-right:0; }
            .health-check { grid-template-columns:minmax(170px,.7fr) minmax(0,1.3fr); gap:12px 20px; }
            .health-check__value { grid-column:1; }
            .health-check__copy { grid-column:2; grid-row:1 / span 2; }
        }
        @media (max-width: 767px) {
            .health-page { margin:16px auto; padding:0 12px; gap:14px; }
            .health-hero { grid-template-columns:1fr; padding:18px; }
            .health-hero h1 { font-size:24px; }
            .health-hero__actions .btn { width:100%; }
            .health-summary { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .health-summary__overall { grid-column:1 / -1; border-right:0; }
            .health-summary > :nth-child(2n) { border-right:1px solid #e5ebf2; }
            .health-summary > :last-child { border-right:0; }
            .health-summary__item { padding:15px 18px; }
            .health-summary__item strong { font-size:23px; }
            .health-group__head { padding:18px; }
            .health-check { grid-template-columns:1fr; gap:9px; padding:17px 18px; }
            .health-check__value, .health-check__copy { grid-column:auto; grid-row:auto; }
            .health-footnote { text-align:left; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'system_health']) ?>
<main class="health-page">
    <section class="health-panel">
        <div class="health-hero">
            <div>
                <span class="health-hero__eyebrow">Chẩn đoán chỉ đọc</span>
                <h1>Trạng thái hệ thống</h1>
                <p>Kiểm tra nhanh những cấu hình ảnh hưởng trực tiếp đến vận hành website trên shared hosting. Không mật khẩu hoặc khóa API nào được hiển thị.</p>
            </div>
            <div class="health-hero__actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/system-logs') ?>">Xem nhật ký lỗi</a>
                <a class="btn btn-primary" href="<?= site_url('admin/system-health') ?>">Kiểm tra lại</a>
            </div>
        </div>
        <div class="health-summary">
            <div class="health-summary__overall">
                <span>Kết luận</span>
                <strong><i class="health-dot is-<?= esc($overallStatus, 'attr') ?>" aria-hidden="true"></i><?= esc($overallCopy) ?></strong>
            </div>
            <div class="health-summary__item is-ok"><span>Tốt</span><strong><?= esc((string) ($summary['ok'] ?? 0)) ?></strong><small>không cần xử lý</small></div>
            <div class="health-summary__item is-warning"><span>Cảnh báo</span><strong><?= esc((string) ($summary['warning'] ?? 0)) ?></strong><small>nên kiểm tra</small></div>
            <div class="health-summary__item is-error"><span>Cần xử lý</span><strong><?= esc((string) ($summary['error'] ?? 0)) ?></strong><small>ảnh hưởng vận hành</small></div>
        </div>
    </section>

    <?php foreach ($groups as $group): ?>
        <?php $checks = is_array($group['checks'] ?? null) ? $group['checks'] : []; ?>
        <section class="health-panel" id="health-<?= esc((string) ($group['key'] ?? ''), 'attr') ?>">
            <div class="health-group__head">
                <h2><?= esc((string) ($group['title'] ?? '')) ?></h2>
                <p><?= esc((string) ($group['description'] ?? '')) ?></p>
            </div>
            <div>
                <?php foreach ($checks as $check): ?>
                    <?php $status = (string) ($check['status'] ?? 'warning'); ?>
                    <div class="health-check is-<?= esc($status, 'attr') ?>">
                        <div class="health-check__label">
                            <i class="health-dot is-<?= esc($status, 'attr') ?>" aria-hidden="true"></i>
                            <span><?= esc((string) ($check['label'] ?? '')) ?></span>
                        </div>
                        <div class="health-check__value">
                            <span class="health-status is-<?= esc($status, 'attr') ?>"><?= esc($statusLabels[$status] ?? 'Kiểm tra') ?></span>
                            <div class="mt-2"><?= esc((string) ($check['value'] ?? '')) ?></div>
                        </div>
                        <div class="health-check__copy">
                            <p><?= esc((string) ($check['detail'] ?? '')) ?></p>
                            <?php if ($status !== 'ok' && ! empty($check['action'])): ?>
                                <div class="health-check__action"><strong>Cách xử lý:</strong><span><?= esc((string) $check['action']) ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <div class="health-footnote">Kiểm tra lúc <?= esc((string) ($report['generated_at'] ?? '')) ?>. Kết quả không được lưu cache.</div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
