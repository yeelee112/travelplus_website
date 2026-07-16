<?php
$entries = is_array($report['entries'] ?? null) ? $report['entries'] : [];
$summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
$filters = is_array($report['filters'] ?? null) ? $report['filters'] : [];
$scan = is_array($report['scan'] ?? null) ? $report['scan'] : [];
$days = (int) ($filters['days'] ?? 7);
$level = (string) ($filters['level'] ?? 'all');
$query = (string) ($filters['query'] ?? '');
$levelLabels = [
    'emergency' => 'Khẩn cấp',
    'alert' => 'Cảnh báo nghiêm trọng',
    'critical' => 'Critical',
    'error' => 'Error',
    'warning' => 'Warning',
];
$formatBytes = static function (int $bytes): string {
    if ($bytes >= 1024 * 1024) {
        return number_format($bytes / 1024 / 1024, 1, ',', '.') . ' MB';
    }

    return number_format(max(0, $bytes) / 1024, 0, ',', '.') . ' KB';
};
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Nhật ký lỗi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f5f7fa; color:#172033; }
        .logs-page { max-width:1280px; margin:28px auto; padding:0 16px; display:grid; gap:18px; }
        .logs-panel { background:#fff; border:1px solid #dfe6ee; border-radius:8px; box-shadow:0 8px 24px rgba(24,39,75,.05); }
        .logs-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:24px; align-items:start; padding:24px; }
        .logs-hero__eyebrow { display:block; margin-bottom:8px; color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; }
        .logs-hero h1 { margin:0 0 8px; color:#0b1f38; font-size:28px; font-weight:800; line-height:1.2; }
        .logs-hero p { max-width:780px; margin:0; color:#65748a; line-height:1.55; }
        .logs-hero__actions { display:flex; gap:10px; flex-wrap:wrap; }
        .logs-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border-top:1px solid #e5ebf2; }
        .logs-stat { min-width:0; padding:18px 20px; border-right:1px solid #e5ebf2; }
        .logs-stat:last-child { border-right:0; }
        .logs-stat span { display:block; margin-bottom:7px; color:#69788d; font-size:12px; font-weight:800; text-transform:uppercase; }
        .logs-stat strong { display:block; color:#0b1f38; font-size:27px; line-height:1; }
        .logs-stat small { display:block; margin-top:7px; color:#77859a; }
        .logs-stat.is-critical strong { color:#b4232f; }
        .logs-stat.is-error strong { color:#c44b27; }
        .logs-stat.is-warning strong { color:#9a6200; }
        .logs-filter { display:grid; grid-template-columns:minmax(220px,1fr) 160px 180px auto; gap:12px; align-items:end; padding:20px 22px; }
        .logs-filter label { display:block; margin-bottom:6px; color:#42536a; font-size:12px; font-weight:800; }
        .logs-filter .form-control, .logs-filter .form-select { min-height:42px; border-color:#ccd8e5; }
        .logs-filter__actions { display:flex; gap:8px; }
        .logs-section__head { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; padding:20px 22px 16px; border-bottom:1px solid #e5ebf2; }
        .logs-section__head h2 { margin:0 0 5px; color:#0b1f38; font-size:20px; font-weight:800; }
        .logs-section__head p { margin:0; color:#69788d; }
        .logs-scan-note { flex:0 0 auto; color:#74839a; font-size:13px; text-align:right; }
        .logs-list { padding:0; }
        .log-entry { display:grid; grid-template-columns:150px minmax(0,1fr); gap:18px; padding:20px 22px; border-bottom:1px solid #e8edf3; }
        .log-entry:last-child { border-bottom:0; }
        .log-entry__meta { display:grid; align-content:start; gap:8px; }
        .log-entry__time { color:#526278; font-size:13px; font-weight:700; }
        .log-entry__level { display:inline-flex; width:max-content; padding:5px 8px; border:1px solid #edb7bc; border-radius:6px; background:#fff0f1; color:#aa2632; font-size:11px; font-weight:800; text-transform:uppercase; }
        .log-entry.is-error .log-entry__level { border-color:#efc4b7; background:#fff3ee; color:#a63e21; }
        .log-entry.is-warning .log-entry__level { border-color:#efd39c; background:#fff8e9; color:#865400; }
        .log-entry__body { min-width:0; }
        .log-entry__body h3 { margin:0; color:#17283f; font-size:16px; font-weight:800; line-height:1.45; overflow-wrap:anywhere; }
        .log-entry__context { display:flex; gap:8px; flex-wrap:wrap; margin-top:9px; }
        .log-entry__context span { display:inline-flex; max-width:100%; padding:4px 7px; border-radius:5px; background:#eef3f8; color:#465970; font-family:Consolas,monospace; font-size:12px; overflow-wrap:anywhere; }
        .log-entry details { margin-top:13px; border-top:1px solid #edf1f5; padding-top:11px; }
        .log-entry summary { width:max-content; max-width:100%; cursor:pointer; color:#096a9f; font-size:13px; font-weight:800; }
        .log-entry pre { max-height:420px; margin:12px 0 0; padding:14px; overflow:auto; border:1px solid #dbe4ec; border-radius:6px; background:#f7f9fb; color:#34465d; font:12px/1.55 Consolas,monospace; white-space:pre-wrap; overflow-wrap:anywhere; }
        .logs-empty { padding:44px 22px; text-align:center; }
        .logs-empty strong { display:block; margin-bottom:7px; color:#20334c; font-size:18px; }
        .logs-empty span { color:#718096; }
        .logs-limit-note { padding:12px 22px; border-top:1px solid #e5ebf2; background:#fff9eb; color:#835600; font-size:13px; }
        .logs-footnote { color:#718096; font-size:13px; text-align:right; }
        @media (max-width: 991px) {
            .logs-summary { grid-template-columns:1fr 1fr; }
            .logs-stat { border-bottom:1px solid #e5ebf2; }
            .logs-stat:nth-child(2n) { border-right:0; }
            .logs-filter { grid-template-columns:1fr 1fr; }
            .logs-filter__query { grid-column:1 / -1; }
        }
        @media (max-width: 767px) {
            .logs-page { margin:16px auto; padding:0 12px; gap:14px; }
            .logs-hero { grid-template-columns:1fr; padding:18px; }
            .logs-hero h1 { font-size:24px; }
            .logs-hero__actions, .logs-hero__actions .btn { width:100%; }
            .logs-summary { grid-template-columns:1fr 1fr; }
            .logs-stat { padding:15px 18px; }
            .logs-stat strong { font-size:23px; }
            .logs-filter { grid-template-columns:1fr; padding:18px; }
            .logs-filter__query { grid-column:auto; }
            .logs-filter__actions { display:grid; grid-template-columns:1fr 1fr; }
            .logs-section__head { display:grid; padding:18px; }
            .logs-scan-note { text-align:left; }
            .log-entry { grid-template-columns:1fr; gap:12px; padding:18px; }
            .log-entry__meta { display:flex; align-items:center; justify-content:space-between; gap:10px; }
            .logs-footnote { text-align:left; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'system_logs']) ?>
<main class="logs-page">
    <section class="logs-panel">
        <div class="logs-hero">
            <div>
                <span class="logs-hero__eyebrow">Theo dõi chỉ đọc</span>
                <h1>Nhật ký lỗi hệ thống</h1>
                <p>Xem lỗi CodeIgniter gần đây ngay trong admin. Mật khẩu, token, email và số điện thoại trong nội dung log được che trước khi hiển thị.</p>
            </div>
            <div class="logs-hero__actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/system-health') ?>">Trạng thái hệ thống</a>
            </div>
        </div>
        <div class="logs-summary">
            <div class="logs-stat"><span>Tổng sự cố</span><strong><?= esc((string) ($summary['total'] ?? 0)) ?></strong><small>trong phạm vi đã chọn</small></div>
            <div class="logs-stat is-critical"><span>Critical</span><strong><?= esc((string) ($summary['critical'] ?? 0)) ?></strong><small>cần ưu tiên kiểm tra</small></div>
            <div class="logs-stat is-error"><span>Error</span><strong><?= esc((string) ($summary['error'] ?? 0)) ?></strong><small>lỗi vận hành</small></div>
            <div class="logs-stat is-warning"><span>Warning</span><strong><?= esc((string) ($summary['warning'] ?? 0)) ?></strong><small>cảnh báo theo dõi</small></div>
        </div>
    </section>

    <section class="logs-panel">
        <form class="logs-filter" method="get" action="<?= site_url('admin/system-logs') ?>">
            <div class="logs-filter__query">
                <label for="logQuery">Tìm trong nội dung hoặc route</label>
                <input class="form-control" id="logQuery" type="search" name="q" value="<?= esc($query, 'attr') ?>" maxlength="80" placeholder="Ví dụ: booking, email, database">
            </div>
            <div>
                <label for="logLevel">Mức lỗi</label>
                <select class="form-select" id="logLevel" name="level">
                    <option value="all" <?= $level === 'all' ? 'selected' : '' ?>>Tất cả sự cố</option>
                    <option value="critical" <?= $level === 'critical' ? 'selected' : '' ?>>Critical</option>
                    <option value="error" <?= $level === 'error' ? 'selected' : '' ?>>Error</option>
                    <option value="warning" <?= $level === 'warning' ? 'selected' : '' ?>>Warning</option>
                </select>
            </div>
            <div>
                <label for="logDays">Khoảng thời gian</label>
                <select class="form-select" id="logDays" name="days">
                    <?php foreach ([1 => 'Hôm nay', 3 => '3 ngày', 7 => '7 ngày', 14 => '14 ngày', 30 => '30 ngày'] as $option => $label): ?>
                        <option value="<?= $option ?>" <?= $days === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="logs-filter__actions">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/system-logs') ?>">Đặt lại</a>
                <button class="btn btn-primary" type="submit">Lọc log</button>
            </div>
        </form>
    </section>

    <section class="logs-panel">
        <div class="logs-section__head">
            <div>
                <h2>Kết quả gần nhất</h2>
                <p>Hiển thị tối đa 100 sự cố, mới nhất nằm trên cùng. Log cũ vẫn còn sau khi lỗi đã được sửa, vì vậy hãy đối chiếu thời gian xảy ra.</p>
            </div>
            <div class="logs-scan-note">Đã đọc <?= esc((string) ($scan['files'] ?? 0)) ?> file · <?= esc($formatBytes((int) ($scan['bytes'] ?? 0))) ?></div>
        </div>

        <?php if ($entries === []): ?>
            <div class="logs-empty">
                <strong>Không tìm thấy sự cố phù hợp</strong>
                <span>Thử tăng khoảng thời gian hoặc đặt lại bộ lọc.</span>
            </div>
        <?php else: ?>
            <div class="logs-list">
                <?php foreach ($entries as $entry): ?>
                    <?php
                    $entryLevel = (string) ($entry['level'] ?? 'warning');
                    $visualLevel = in_array($entryLevel, ['emergency', 'alert', 'critical'], true) ? 'critical' : $entryLevel;
                    ?>
                    <article class="log-entry is-<?= esc($visualLevel, 'attr') ?>">
                        <div class="log-entry__meta">
                            <span class="log-entry__level"><?= esc($levelLabels[$entryLevel] ?? strtoupper($entryLevel)) ?></span>
                            <time class="log-entry__time" datetime="<?= esc((string) ($entry['datetime'] ?? ''), 'attr') ?>"><?= esc((string) ($entry['datetime'] ?? '')) ?></time>
                        </div>
                        <div class="log-entry__body">
                            <h3><?= esc((string) ($entry['message'] ?? '')) ?></h3>
                            <?php if (! empty($entry['method']) || ! empty($entry['route'])): ?>
                                <div class="log-entry__context">
                                    <?php if (! empty($entry['method'])): ?><span><?= esc((string) $entry['method']) ?></span><?php endif; ?>
                                    <?php if (! empty($entry['route'])): ?><span><?= esc((string) $entry['route']) ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (! empty($entry['details'])): ?>
                                <details>
                                    <summary>Xem chi tiết kỹ thuật</summary>
                                    <pre><?= esc((string) $entry['details']) ?></pre>
                                </details>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($scan['truncated'])): ?>
            <div class="logs-limit-note">Danh sách đã đạt giới hạn 100 mục. Hãy thu hẹp thời gian, mức lỗi hoặc từ khóa để xem kết quả cụ thể hơn.</div>
        <?php endif; ?>
    </section>

    <div class="logs-footnote">Kiểm tra lúc <?= esc((string) ($report['generated_at'] ?? '')) ?>. Trang này không sửa hoặc xóa file log.</div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
