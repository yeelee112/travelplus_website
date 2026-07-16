<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - CRM Leads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1380px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .crm-head { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:20px; }
        .crm-head p { max-width:760px; }
        .crm-stage-tabs { display:flex; flex-wrap:wrap; gap:8px; padding:6px; border:1px solid #dfe7f0; border-radius:16px; background:#f8fafc; margin-bottom:18px; }
        .crm-stage-tab { display:inline-flex; align-items:center; gap:8px; min-height:38px; padding:8px 12px; border-radius:12px; color:#334155; text-decoration:none; font-weight:800; font-size:14px; }
        .crm-stage-tab:hover, .crm-stage-tab.is-active { background:#fff; color:#0f3f8c; box-shadow:0 8px 20px rgba(15,23,42,.08); }
        .crm-stage-tab span { display:inline-flex; min-width:24px; height:24px; align-items:center; justify-content:center; padding:0 7px; border-radius:999px; background:#e8eef6; color:#475569; font-size:12px; }
        .crm-filter { display:grid; grid-template-columns:minmax(260px,1fr) 180px 180px auto; gap:10px; align-items:center; margin-bottom:18px; }
        .crm-list { display:grid; gap:10px; }
        .crm-row { display:grid; grid-template-columns:minmax(250px,1.25fr) minmax(220px,1fr) minmax(155px,.65fr) minmax(190px,.75fr); gap:16px; align-items:center; padding:16px; border:1px solid #e3eaf2; border-radius:14px; background:#fff; }
        .crm-row:hover { border-color:#c8d5e4; background:#fcfdff; }
        .crm-person strong { display:block; color:#0f172a; font-size:16px; line-height:1.25; }
        .crm-person small, .crm-interest small, .crm-follow small { display:block; color:#64748b; line-height:1.45; overflow-wrap:anywhere; }
        .crm-interest a { color:#0f3f8c; font-weight:800; text-decoration:none; }
        .crm-interest p { display:-webkit-box; overflow:hidden; margin:6px 0 0; color:#64748b; font-size:13px; line-height:1.45; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
        .crm-badges { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
        .crm-badge { display:inline-flex; align-items:center; min-height:24px; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:800; line-height:1; }
        .stage-new { background:#e8f1ff; color:#0d5bd7; }
        .stage-consulting { background:#fff4d6; color:#9f6b00; }
        .stage-won { background:#dff7e8; color:#0f8a4b; }
        .stage-lost { background:#ffe2e0; color:#c23d33; }
        .source-badge { background:#eef2f7; color:#4b5565; }
        .priority-high { background:#ffe2e0; color:#c23d33; }
        .priority-normal { background:#e8f1ff; color:#0d5bd7; }
        .priority-low { background:#e9eef5; color:#516173; }
        .crm-actions details { position:relative; }
        .crm-actions summary { list-style:none; display:inline-flex; width:100%; min-height:40px; align-items:center; justify-content:center; border:1px solid #cfd8e3; border-radius:12px; background:#fff; color:#334155; font-weight:800; cursor:pointer; }
        .crm-actions summary::-webkit-details-marker { display:none; }
        .crm-actions details[open] summary { border-color:#0f3f8c; color:#0f3f8c; background:#f6f9ff; }
        .crm-update { position:absolute; right:0; top:48px; z-index:5; width:340px; padding:14px; border:1px solid #dce4ec; border-radius:16px; background:#fff; box-shadow:0 22px 55px rgba(15,23,42,.16); }
        .crm-empty { padding:46px 18px; border:1px dashed #cfd8e3; border-radius:16px; background:#fbfcfe; text-align:center; color:#64748b; }
        @media (max-width: 1199px) {
            .crm-row { grid-template-columns:minmax(240px,1fr) minmax(220px,1fr); }
            .crm-actions { grid-column:1 / -1; }
            .crm-update { left:0; right:auto; width:min(420px,100%); }
        }
        @media (max-width: 767px) {
            .crm-head { display:grid; }
            .crm-filter { grid-template-columns:1fr; }
            .crm-row { grid-template-columns:1fr; gap:12px; }
            .crm-update { position:static; width:100%; margin-top:10px; box-shadow:none; }
        }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'leads'; ?>
<?php helper('display'); ?>
<?php
$stageLabels = [
    'new' => 'Mới',
    'consulting' => 'Đang tư vấn',
    'won' => 'Đã chốt',
    'lost' => 'Thua / huỷ',
];
$sourceLabels = [
    'contact_form' => 'Contact',
    'tour_enquiry' => 'Tư vấn tour',
    'ai_chat' => 'AI chat',
    'booking' => 'Booking',
    'manual' => 'Manual',
];
$priorityLabels = [
    'low' => 'Thấp',
    'normal' => 'Thường',
    'high' => 'Cao',
];
$filterUrl = static function (array $overrides = []) use ($stage, $source, $keyword): string {
    return site_url('admin/leads?' . http_build_query(array_filter(array_merge([
        'stage' => $stage,
        'source' => $source,
        'q' => $keyword,
    ], $overrides), static fn ($value) => $value !== '' && $value !== null)));
};
$syncUrl = $filterUrl(['sync_bookings' => '1']);
$pageUrl = static function (int $page) use ($filterUrl): string {
    return $filterUrl(['page' => max(1, $page)]);
};
$totalLeads = (int) ($stats['total'] ?? 0);
$currentPage = max(1, (int) ($currentPage ?? 1));
$hasNextPage = ! empty($hasNextPage);
?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="crm-head">
            <div>
                <h1 class="h3 mb-1">CRM leads</h1>
                <p class="text-muted mb-0">Theo dõi lead từ contact form, tư vấn tour, AI chat và booking chưa thanh toán. Ưu tiên xử lý lead mới và lead đang tư vấn.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a class="btn btn-primary" href="<?= esc($syncUrl, 'attr') ?>">Đồng bộ booking</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <nav class="crm-stage-tabs" aria-label="Lead stages">
            <a class="crm-stage-tab <?= $stage === '' ? 'is-active' : '' ?>" href="<?= $filterUrl(['stage' => '']) ?>">
                Tất cả <span><?= esc((string) $totalLeads) ?></span>
            </a>
            <?php foreach ($stageOptions as $stageOption): ?>
                <a class="crm-stage-tab <?= $stage === $stageOption ? 'is-active' : '' ?>" href="<?= $filterUrl(['stage' => $stageOption]) ?>">
                    <?= esc($stageLabels[$stageOption] ?? $stageOption) ?>
                    <span><?= esc((string) ($stats[$stageOption] ?? 0)) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <form class="crm-filter" method="get" action="<?= site_url('admin/leads') ?>">
            <input class="form-control" type="text" name="q" value="<?= esc($keyword) ?>" placeholder="Tìm tên, email, SĐT, tour, điểm đến, mã booking">
            <select class="form-select" name="stage">
                <option value="">Tất cả stage</option>
                <?php foreach ($stageOptions as $stageOption): ?>
                    <option value="<?= esc($stageOption) ?>" <?= $stage === $stageOption ? 'selected' : '' ?>><?= esc($stageLabels[$stageOption] ?? $stageOption) ?></option>
                <?php endforeach; ?>
            </select>
            <select class="form-select" name="source">
                <option value="">Tất cả nguồn</option>
                <?php foreach ($sourceOptions as $sourceOption): ?>
                    <option value="<?= esc($sourceOption) ?>" <?= $source === $sourceOption ? 'selected' : '' ?>><?= esc($sourceLabels[$sourceOption] ?? $sourceOption) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/leads') ?>">Đặt lại</a>
            </div>
        </form>

        <?php if (empty($leads)): ?>
            <div class="crm-empty">Chưa có lead phù hợp. Nếu vừa thêm CRM, hãy chạy file SQL tạo bảng trước.</div>
        <?php else: ?>
            <div class="crm-list">
                <?php foreach ($leads as $lead): ?>
                    <?php
                    $leadStage = (string) ($lead['stage'] ?? 'new');
                    $leadSource = (string) ($lead['source'] ?? 'manual');
                    $leadPriority = (string) ($lead['priority'] ?? 'normal');
                    $contactParts = array_values(array_filter([
                        trim((string) ($lead['customer_email'] ?? '')),
                        trim((string) ($lead['customer_phone'] ?? '')),
                    ], static fn ($value) => $value !== ''));
                    $contextParts = array_values(array_filter([
                        ! empty($lead['destination']) ? 'Điểm đến: ' . (string) $lead['destination'] : '',
                        ! empty($lead['travel_date']) ? 'Thời gian: ' . (string) $lead['travel_date'] : '',
                        ! empty($lead['travelers']) ? 'Khách: ' . (string) $lead['travelers'] : '',
                    ], static fn ($value) => $value !== ''));
                    ?>
                    <article class="crm-row">
                        <div class="crm-person">
                            <strong><?= esc((string) ($lead['customer_name'] ?? 'Chưa có tên')) ?></strong>
                            <small><?= esc($contactParts !== [] ? implode(' · ', $contactParts) : 'Chưa có email/SĐT') ?></small>
                            <div class="crm-badges">
                                <span class="crm-badge stage-<?= esc($leadStage) ?>"><?= esc($stageLabels[$leadStage] ?? $leadStage) ?></span>
                                <span class="crm-badge source-badge"><?= esc($sourceLabels[$leadSource] ?? $leadSource) ?></span>
                                <span class="crm-badge priority-<?= esc($leadPriority) ?>"><?= esc($priorityLabels[$leadPriority] ?? $leadPriority) ?></span>
                            </div>
                        </div>

                        <div class="crm-interest">
                            <?php if (! empty($lead['interest_title'])): ?>
                                <?php if (! empty($lead['interest_url'])): ?>
                                    <a href="<?= esc((string) $lead['interest_url'], 'attr') ?>" target="_blank" rel="noopener"><?= esc((string) $lead['interest_title']) ?></a>
                                <?php else: ?>
                                    <strong><?= esc((string) $lead['interest_title']) ?></strong>
                                <?php endif; ?>
                            <?php else: ?>
                                <strong>Chưa có nhu cầu cụ thể</strong>
                            <?php endif; ?>
                            <small><?= esc($contextParts !== [] ? implode(' · ', $contextParts) : 'Chưa có thời gian/điểm đến') ?></small>
                            <?php if (! empty($lead['message'])): ?>
                                <p><?= esc((string) $lead['message']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="crm-follow">
                            <?php if (! empty($lead['booking_code'])): ?>
                                <a class="btn btn-sm btn-outline-secondary mb-2" href="<?= site_url('admin/bookings?q=' . rawurlencode((string) $lead['booking_code'])) ?>">Booking <?= esc((string) $lead['booking_code']) ?></a>
                            <?php endif; ?>
                            <small>Tạo: <?= esc(app_datetime((string) ($lead['created_at'] ?? ''))) ?></small>
                            <small><?= ! empty($lead['last_contacted_at']) ? 'Đã liên hệ: ' . esc(app_datetime((string) $lead['last_contacted_at'])) : 'Chưa đánh dấu liên hệ' ?></small>
                        </div>

                        <div class="crm-actions">
                            <details>
                                <summary>Cập nhật</summary>
                                <form class="crm-update" method="post" action="<?= site_url('admin/leads/' . (int) $lead['id']) ?>">
                                    <?= csrf_field() ?>
                                    <div class="mb-2">
                                        <label class="form-label">Stage</label>
                                        <select class="form-select" name="stage">
                                            <?php foreach ($stageOptions as $stageOption): ?>
                                                <option value="<?= esc($stageOption) ?>" <?= $leadStage === $stageOption ? 'selected' : '' ?>><?= esc($stageLabels[$stageOption] ?? $stageOption) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Ưu tiên</label>
                                        <select class="form-select" name="priority">
                                            <?php foreach ($priorityLabels as $priorityValue => $priorityLabel): ?>
                                                <option value="<?= esc($priorityValue) ?>" <?= $leadPriority === $priorityValue ? 'selected' : '' ?>><?= esc($priorityLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Ghi chú</label>
                                        <textarea class="form-control" name="internal_note" rows="3" placeholder="VD: Đã gọi lần 1, khách cần báo giá..."><?= esc((string) ($lead['internal_note'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" id="contacted-<?= (int) $lead['id'] ?>" name="mark_contacted">
                                        <label class="form-check-label" for="contacted-<?= (int) $lead['id'] ?>">Đã liên hệ hôm nay</label>
                                    </div>
                                    <button class="btn btn-primary w-100" type="submit">Lưu</button>
                                </form>
                            </details>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($currentPage > 1 || $hasNextPage): ?>
            <div class="d-flex justify-content-between align-items-center gap-2 mt-4">
                <div class="text-muted small">Trang <?= esc((string) $currentPage) ?></div>
                <div class="d-flex gap-2">
                    <?php if ($currentPage > 1): ?>
                        <a class="btn btn-outline-secondary" href="<?= esc($pageUrl($currentPage - 1), 'attr') ?>">Trước</a>
                    <?php endif; ?>
                    <?php if ($hasNextPage): ?>
                        <a class="btn btn-outline-secondary" href="<?= esc($pageUrl($currentPage + 1), 'attr') ?>">Tiếp</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
