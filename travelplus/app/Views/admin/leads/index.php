<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - CRM Leads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1380px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .stat-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; margin-bottom:22px; }
        .stat-card { display:block; border:1px solid #e4eaf0; border-radius:16px; padding:16px; color:inherit; text-decoration:none; background:#fbfcfe; }
        .stat-card:hover, .stat-card.is-active { border-color:#0d6efd; background:#f3f8ff; }
        .stat-card small { display:block; color:#687386; font-weight:700; margin-bottom:8px; }
        .stat-card strong { font-size:28px; line-height:1; }
        .stage-badge, .source-badge, .priority-badge { display:inline-flex; border-radius:999px; padding:5px 10px; font-size:12px; font-weight:800; }
        .stage-new { background:#e8f1ff; color:#0d5bd7; }
        .stage-consulting { background:#fff4d6; color:#9f6b00; }
        .stage-won { background:#dff7e8; color:#0f8a4b; }
        .stage-lost { background:#ffe2e0; color:#c23d33; }
        .source-badge { background:#eef2f7; color:#4b5565; }
        .priority-high { background:#ffe2e0; color:#c23d33; }
        .priority-normal { background:#e8f1ff; color:#0d5bd7; }
        .priority-low { background:#e9eef5; color:#516173; }
        .lead-card { border:1px solid #e6ebf0; border-radius:16px; padding:18px; background:#fff; }
        .lead-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(260px,.8fr); gap:18px; }
        .lead-meta { display:flex; flex-wrap:wrap; gap:8px; margin:10px 0 12px; }
        .lead-message { color:#5f6b7a; line-height:1.6; white-space:pre-wrap; }
        .lead-actions { background:#fbfcfe; border:1px solid #edf1f5; border-radius:14px; padding:14px; }
        @media (max-width: 991px) {
            .stat-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .lead-grid { grid-template-columns:1fr; }
        }
        @media (max-width: 575px) {
            .stat-grid { grid-template-columns:1fr; }
            .admin-card { padding:20px; }
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
    'contact_form' => 'Contact form',
    'tour_enquiry' => 'Tư vấn tour',
    'ai_chat' => 'AI chat',
    'booking' => 'Booking',
    'manual' => 'Manual',
];
$priorityLabels = [
    'low' => 'Thấp',
    'normal' => 'Bình thường',
    'high' => 'Cao',
];
$filterUrl = static function (array $overrides = []) use ($stage, $source, $keyword): string {
    return site_url('admin/leads?' . http_build_query(array_filter(array_merge([
        'stage' => $stage,
        'source' => $source,
        'q' => $keyword,
    ], $overrides), static fn ($value) => $value !== '' && $value !== null)));
};
?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">CRM leads</h1>
                <p class="text-muted mb-0">Gom contact form, tư vấn tour, AI chat có thông tin liên hệ và booking chưa thanh toán.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('/') ?>">Home</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="stat-grid">
            <a class="stat-card <?= $stage === '' ? 'is-active' : '' ?>" href="<?= $filterUrl(['stage' => '']) ?>">
                <small>Tổng lead</small>
                <strong><?= esc((string) ($stats['total'] ?? 0)) ?></strong>
            </a>
            <?php foreach ($stageOptions as $stageOption): ?>
                <a class="stat-card <?= $stage === $stageOption ? 'is-active' : '' ?>" href="<?= $filterUrl(['stage' => $stageOption]) ?>">
                    <small><?= esc($stageLabels[$stageOption] ?? $stageOption) ?></small>
                    <strong><?= esc((string) ($stats[$stageOption] ?? 0)) ?></strong>
                </a>
            <?php endforeach; ?>
        </div>

        <form class="row g-3 mb-4" method="get" action="<?= site_url('admin/leads') ?>">
            <div class="col-lg-5 col-md-6">
                <input class="form-control" type="text" name="q" value="<?= esc($keyword) ?>" placeholder="Tìm tên, email, SĐT, tour, điểm đến, mã booking">
            </div>
            <div class="col-lg-2 col-md-3">
                <select class="form-select" name="stage">
                    <option value="">Tất cả stage</option>
                    <?php foreach ($stageOptions as $stageOption): ?>
                        <option value="<?= esc($stageOption) ?>" <?= $stage === $stageOption ? 'selected' : '' ?>><?= esc($stageLabels[$stageOption] ?? $stageOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-3">
                <select class="form-select" name="source">
                    <option value="">Tất cả nguồn</option>
                    <?php foreach ($sourceOptions as $sourceOption): ?>
                        <option value="<?= esc($sourceOption) ?>" <?= $source === $sourceOption ? 'selected' : '' ?>><?= esc($sourceLabels[$sourceOption] ?? $sourceOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/leads') ?>">Reset</a>
            </div>
        </form>

        <div class="d-grid gap-3">
            <?php if (empty($leads)): ?>
                <div class="text-center text-muted py-4">Chưa có lead phù hợp. Nếu vừa thêm CRM, hãy chạy migration trước.</div>
            <?php endif; ?>

            <?php foreach ($leads as $lead): ?>
                <?php
                $leadStage = (string) ($lead['stage'] ?? 'new');
                $leadSource = (string) ($lead['source'] ?? 'manual');
                $leadPriority = (string) ($lead['priority'] ?? 'normal');
                ?>
                <div class="lead-card">
                    <div class="lead-grid">
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="h5 mb-1"><?= esc((string) ($lead['customer_name'] ?? 'Chưa có tên')) ?></h2>
                                    <div class="text-muted">
                                        <?= esc((string) ($lead['customer_email'] ?? '')) ?>
                                        <?= ! empty($lead['customer_phone']) ? ' · ' . esc((string) $lead['customer_phone']) : '' ?>
                                    </div>
                                </div>
                                <span class="stage-badge stage-<?= esc($leadStage) ?>"><?= esc($stageLabels[$leadStage] ?? $leadStage) ?></span>
                            </div>

                            <div class="lead-meta">
                                <span class="source-badge"><?= esc($sourceLabels[$leadSource] ?? $leadSource) ?></span>
                                <span class="priority-badge priority-<?= esc($leadPriority) ?>"><?= esc($priorityLabels[$leadPriority] ?? $leadPriority) ?></span>
                                <?php if (! empty($lead['booking_code'])): ?>
                                    <a class="source-badge" href="<?= site_url('admin/bookings?q=' . rawurlencode((string) $lead['booking_code'])) ?>">Booking <?= esc((string) $lead['booking_code']) ?></a>
                                <?php endif; ?>
                                <?php if (! empty($lead['last_contacted_at'])): ?>
                                    <span class="source-badge">Đã liên hệ <?= esc(app_datetime((string) $lead['last_contacted_at'])) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (! empty($lead['interest_title'])): ?>
                                <div class="fw-semibold mb-1">
                                    <?php if (! empty($lead['interest_url'])): ?>
                                        <a href="<?= esc((string) $lead['interest_url'], 'attr') ?>" target="_blank" rel="noopener"><?= esc((string) $lead['interest_title']) ?></a>
                                    <?php else: ?>
                                        <?= esc((string) $lead['interest_title']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="text-muted small mb-2">
                                <?= ! empty($lead['destination']) ? 'Điểm đến: ' . esc((string) $lead['destination']) . ' · ' : '' ?>
                                <?= ! empty($lead['travel_date']) ? 'Thời gian: ' . esc((string) $lead['travel_date']) . ' · ' : '' ?>
                                <?= ! empty($lead['travelers']) ? 'Khách: ' . esc((string) $lead['travelers']) : '' ?>
                            </div>

                            <?php if (! empty($lead['message'])): ?>
                                <div class="lead-message"><?= esc((string) $lead['message']) ?></div>
                            <?php endif; ?>

                            <?php if (! empty($lead['internal_note'])): ?>
                                <div class="alert alert-light border mt-3 mb-0"><?= nl2br(esc((string) $lead['internal_note'])) ?></div>
                            <?php endif; ?>
                        </div>

                        <form class="lead-actions" method="post" action="<?= site_url('admin/leads/' . (int) $lead['id']) ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Stage</label>
                                <select class="form-select" name="stage">
                                    <?php foreach ($stageOptions as $stageOption): ?>
                                        <option value="<?= esc($stageOption) ?>" <?= $leadStage === $stageOption ? 'selected' : '' ?>><?= esc($stageLabels[$stageOption] ?? $stageOption) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Độ ưu tiên</label>
                                <select class="form-select" name="priority">
                                    <?php foreach ($priorityLabels as $priorityValue => $priorityLabel): ?>
                                        <option value="<?= esc($priorityValue) ?>" <?= $leadPriority === $priorityValue ? 'selected' : '' ?>><?= esc($priorityLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú tư vấn</label>
                                <textarea class="form-control" name="internal_note" rows="4" placeholder="VD: Đã gọi lần 1, khách cần báo giá tour gia đình..."><?= esc((string) ($lead['internal_note'] ?? '')) ?></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="contacted-<?= (int) $lead['id'] ?>" name="mark_contacted">
                                <label class="form-check-label" for="contacted-<?= (int) $lead['id'] ?>">Đánh dấu đã liên hệ hôm nay</label>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Cập nhật lead</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($pager)): ?>
            <div class="mt-4"><?= $pager->links() ?></div>
        <?php endif; ?>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
