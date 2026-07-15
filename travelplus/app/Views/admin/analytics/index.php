<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1360px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .stat-card { background:#fbfcfe; border:1px solid #e5ebf2; border-radius:16px; padding:20px; height:100%; }
        .stat-label { color:#6b778c; font-size:13px; margin-bottom:8px; }
        .stat-value { font-size:32px; font-weight:700; line-height:1; }
        .journey-card { border:1px solid #e6ebf0; border-radius:16px; padding:16px; background:#fff; }
        .journey-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:12px; }
        .journey-meta { color:#64748b; font-size:13px; }
        .journey-paths { display:flex; flex-wrap:wrap; gap:8px; }
        .journey-step { display:inline-flex; align-items:center; gap:8px; padding:7px 10px; border-radius:999px; background:#f8fafc; border:1px solid #e2e8f0; font-size:12px; color:#334155; }
        .journey-step time { color:#64748b; }
        .journey-step__query { color:#0f172a; font-weight:600; }
        .journey-step__count { color:#64748b; }
        .table td,.table th { vertical-align:middle; }
        .filter-pills { display:flex; gap:8px; flex-wrap:wrap; }
        .filter-pills a { display:inline-flex; padding:8px 12px; border-radius:999px; border:1px solid #dbe4ee; color:#334155; text-decoration:none; font-weight:600; background:#fff; }
        .filter-pills a.is-active { background:#172033; color:#fff; border-color:#172033; }
        .empty-note { color:#64748b; font-size:14px; }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'analytics'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="h3 mb-1">Analytics</h1>
                <p class="text-muted mb-0">Theo dõi trang nào được xem nhiều và khách đã đi qua những trang nào trước khi để lại hành động.</p>
            </div>
            <div class="filter-pills">
                <?php foreach ([7, 30, 90] as $daysOption): ?>
                    <a href="<?= esc(site_url('admin/analytics') . '?days=' . $daysOption, 'attr') ?>" class="<?= $days === $daysOption ? 'is-active' : '' ?>">
                        <?= esc((string) $daysOption) ?> ngày
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (! $isReady): ?>
        <div class="admin-card">
            <h2 class="h5 mb-2">Chưa có bảng analytics</h2>
            <p class="empty-note mb-0">Code đã sẵn sàng, nhưng database chưa có `analytics_visits` và `analytics_page_views`, nên hệ thống chưa bắt đầu ghi nhận hành vi khách.</p>
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-label">Page views</div><div class="stat-value"><?= esc(number_format((int) ($summary['pageviews'] ?? 0), 0, ',', '.')) ?></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-label">Visits</div><div class="stat-value"><?= esc(number_format((int) ($summary['visits'] ?? 0), 0, ',', '.')) ?></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-label">Khách duy nhất</div><div class="stat-value"><?= esc(number_format((int) ($summary['visitors'] ?? 0), 0, ',', '.')) ?></div></div></div>
            <div class="col-md-6 col-xl-3"><div class="stat-card"><div class="stat-label">Trang / visit</div><div class="stat-value"><?= esc((string) ($summary['avg_pages_per_visit'] ?? 0)) ?></div></div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="admin-card mb-4">
                    <h2 class="h5 mb-3">Trang được xem nhiều</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Đường dẫn</th><th>Loại trang</th><th>Lượt xem</th><th>Khách</th><th>Xem gần nhất</th></tr></thead>
                            <tbody>
                            <?php if (empty($topPages)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu.</td></tr>
                            <?php else: ?>
                                <?php foreach ($topPages as $page): ?>
                                    <tr>
                                        <td><code><?= esc((string) ($page['path'] ?? '/')) ?></code></td>
                                        <td><?= esc((string) ($page['page_type'] ?? 'page')) ?></td>
                                        <td><?= esc(number_format((int) ($page['views'] ?? 0), 0, ',', '.')) ?></td>
                                        <td><?= esc(number_format((int) ($page['visitors'] ?? 0), 0, ',', '.')) ?></td>
                                        <td><?= esc(app_datetime((string) ($page['last_viewed_at'] ?? ''))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="h5 mb-3">Nguồn vào</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Referrer</th><th>Visits</th></tr></thead>
                            <tbody>
                            <?php if (empty($topReferrers)): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">Chưa có dữ liệu referrer.</td></tr>
                            <?php else: ?>
                                <?php foreach ($topReferrers as $referrer): ?>
                                    <tr>
                                        <td><a href="<?= esc((string) ($referrer['referrer'] ?? ''), 'attr') ?>" target="_blank" rel="noopener"><?= esc((string) ($referrer['referrer'] ?? '')) ?></a></td>
                                        <td><?= esc(number_format((int) ($referrer['visits'] ?? 0), 0, ',', '.')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-card mt-4">
                    <h2 class="h5 mb-3">Khách search gì nhiều nhất</h2>
                    <?php if (! ($isSearchReady ?? false)): ?>
                        <div class="text-muted">Chưa có bảng log search riêng.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Từ khóa</th><th>Ngữ cảnh</th><th>Lượt search</th><th>KQ trung bình</th><th>Gần nhất</th></tr></thead>
                                <tbody>
                                <?php if (empty($topSearchTerms)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu search.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($topSearchTerms as $term): ?>
                                        <tr>
                                            <td><strong><?= esc((string) ($term['query_term'] ?? '(không nhập keyword)')) ?></strong></td>
                                            <td>
                                                <?= esc((string) (($term['tour_type'] ?? '') !== '' ? $term['tour_type'] : 'all')) ?>
                                                <?= ! empty($term['promotion_only']) ? '· promo' : '' ?>
                                            </td>
                                            <td><?= esc(number_format((int) ($term['searches'] ?? 0), 0, ',', '.')) ?></td>
                                            <td><?= esc(number_format((float) ($term['avg_results'] ?? 0), 1, ',', '.')) ?></td>
                                            <td><?= esc(app_datetime((string) ($term['last_searched_at'] ?? ''))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="admin-card">
                    <h2 class="h5 mb-3">Hành trình khách gần đây</h2>
                    <div class="d-flex flex-column gap-3">
                        <?php if (empty($recentJourneys)): ?>
                            <div class="text-muted">Chưa có dữ liệu hành trình.</div>
                        <?php else: ?>
                            <?php foreach ($recentJourneys as $journey): ?>
                                <div class="journey-card">
                                    <div class="journey-head">
                                        <div>
                                            <div class="fw-semibold">Phiên #<?= esc((string) ($journey['id'] ?? 0)) ?></div>
                                            <div class="journey-meta">
                                                <?= esc(number_format((int) ($journey['pageviews'] ?? 0), 0, ',', '.')) ?> pageviews
                                                · bắt đầu <?= esc(app_datetime((string) ($journey['started_at'] ?? ''))) ?>
                                            </div>
                                            <div class="journey-meta">
                                                Visitor: <?= esc(substr((string) ($journey['visitor_token'] ?? ''), 0, 8)) ?>
                                                <?php if (! empty($journey['user_id'])): ?>
                                                    · User #<?= esc((string) $journey['user_id']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="journey-meta text-end">
                                            <div>Landing: <code><?= esc((string) ($journey['landing_path'] ?? '/')) ?></code></div>
                                            <div>Last: <code><?= esc((string) ($journey['last_path'] ?? '/')) ?></code></div>
                                        </div>
                                    </div>
                                    <?php if (! empty($journey['referrer'])): ?>
                                        <div class="journey-meta mb-2">Referrer: <?= esc((string) $journey['referrer']) ?></div>
                                    <?php endif; ?>
                                    <div class="journey-paths">
                                        <?php foreach ((array) ($journey['pages'] ?? []) as $step): ?>
                                            <span class="journey-step">
                                                <code><?= esc((string) ($step['path'] ?? '/')) ?></code>
                                                <?php if (! empty($step['search_label'])): ?>
                                                    <span class="journey-step__query"><?= esc((string) $step['search_label']) ?></span>
                                                    <span class="journey-step__count">(<?= esc(number_format((int) ($step['results_total'] ?? 0), 0, ',', '.')) ?>)</span>
                                                <?php endif; ?>
                                                <time><?= esc(date('H:i', strtotime((string) ($step['viewed_at'] ?? '')))) ?></time>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-card mt-4">
                    <h2 class="h5 mb-3">Search gần đây</h2>
                    <?php if (! ($isSearchReady ?? false)): ?>
                        <div class="text-muted">Chưa có bảng log search riêng.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Từ khóa</th><th>Bộ lọc</th><th>KQ</th><th>Lúc</th></tr></thead>
                                <tbody>
                                <?php if (empty($recentSearches)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Chưa có search nào được ghi nhận.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentSearches as $search): ?>
                                        <tr>
                                            <td><?= esc((string) (($search['query_term'] ?? '') !== '' ? $search['query_term'] : '(không nhập keyword)')) ?></td>
                                            <td>
                                                <?php
                                                    $filters = [];
                                                    if (! empty($search['tour_type'])) {
                                                        $filters[] = (string) $search['tour_type'];
                                                    }
                                                    if (! empty($search['departure_from']) || ! empty($search['departure_to'])) {
                                                        $filters[] = trim((string) ($search['departure_from'] ?? '')) . ' → ' . trim((string) ($search['departure_to'] ?? ''));
                                                    }
                                                    if (! empty($search['promotion_only'])) {
                                                        $filters[] = 'promo';
                                                    }
                                                ?>
                                                <?= esc($filters !== [] ? implode(' · ', $filters) : '-') ?>
                                            </td>
                                            <td><?= esc(number_format((int) ($search['results_total'] ?? 0), 0, ',', '.')) ?></td>
                                            <td><?= esc(app_datetime((string) ($search['searched_at'] ?? ''))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
