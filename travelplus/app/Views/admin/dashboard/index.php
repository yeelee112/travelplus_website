<?php
helper('display');

$formatNumber = static fn($value): string => number_format((int) $value, 0, ',', '.');
$stats = is_array($stats ?? null) ? $stats : [];
$recentBookings = is_array($recentBookings ?? null) ? $recentBookings : [];
$topTours = is_array($topTours ?? null) ? $topTours : [];
$topBlogs = is_array($topBlogs ?? null) ? $topBlogs : [];
$analyticsReady = ! empty($analyticsReady);

$bookingTotal = (int) ($stats['bookings_total'] ?? 0);
$pendingTransfer = (int) ($stats['bookings_pending_transfer'] ?? 0);
$pendingReviews = (int) ($stats['reviews_pending'] ?? 0);
$tourTotal = (int) ($stats['tours_total'] ?? 0);
$blogTotal = (int) ($stats['blogs_total'] ?? 0);
$tourViews = (int) ($stats['tour_views_total'] ?? 0);
$blogViews = (int) ($stats['blog_views_total'] ?? 0);
$analyticsPageviews = (int) ($stats['analytics_pageviews_30d'] ?? 0);
$analyticsVisits = (int) ($stats['analytics_visits_30d'] ?? 0);
$contentTotal = $tourTotal + $blogTotal;
$totalContentViews = $tourViews + $blogViews;

$statusLabels = [
    'draft' => 'Draft',
    'pending_payment' => 'Chờ thanh toán',
    'pending_transfer' => 'Chờ chuyển khoản',
    'paid' => 'Đã thanh toán',
    'confirmed' => 'Đã xác nhận',
    'cancelled' => 'Đã hủy',
    'failed' => 'Thất bại',
];

$statusClass = static function ($status): string {
    $status = str_replace('_', '-', (string) $status);

    return $status !== '' ? 'status-' . $status : 'status-draft';
};

$maxTourViews = max(1, ...array_map(static fn($tour): int => (int) ($tour['view_count'] ?? 0), $topTours ?: [['view_count' => 0]]));
$maxBlogViews = max(1, ...array_map(static fn($blog): int => (int) ($blog['view_count'] ?? 0), $topBlogs ?: [['view_count' => 0]]));
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tổng quan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f5f7fb; color:#172033; }
        .dashboard-page { display:grid; gap:18px; }
        .dashboard-panel { background:#fff; border:1px solid #e3e9f1; border-radius:16px; box-shadow:0 14px 34px rgba(20,35,66,.06); }
        .dashboard-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:18px; align-items:start; padding:24px; }
        .dashboard-hero h1 { margin:0 0 8px; color:#071a33; font-size:28px; line-height:1.2; font-weight:850; }
        .dashboard-hero p { margin:0; max-width:760px; color:#64748b; line-height:1.6; }
        .dashboard-actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
        .dashboard-priority { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; padding:0 24px 24px; }
        .priority-card { display:grid; gap:8px; min-height:132px; padding:16px; border:1px solid #e1e9f2; border-radius:14px; background:#fbfdff; color:inherit; text-decoration:none; transition:border-color .16s ease, box-shadow .16s ease, transform .16s ease; }
        .priority-card:hover { border-color:#8fc5ef; box-shadow:0 12px 26px rgba(20,35,66,.08); transform:translateY(-1px); }
        .priority-card small { color:#64748b; font-size:12px; font-weight:850; text-transform:uppercase; letter-spacing:.04em; }
        .priority-card strong { color:#071a33; font-size:30px; line-height:1; }
        .priority-card span { color:#475569; font-size:13px; line-height:1.45; }
        .priority-card.is-warning { background:#fffaf0; border-color:#f3dfb7; }
        .priority-card.is-danger { background:#fff7f7; border-color:#f0c8c8; }
        .metric-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
        .metric-card { padding:18px; border:1px solid #e1e8f0; border-radius:14px; background:#fff; }
        .metric-card span { display:block; margin-bottom:8px; color:#64748b; font-size:13px; font-weight:800; }
        .metric-card strong { display:block; color:#071a33; font-size:32px; line-height:1; font-weight:850; }
        .metric-card em { display:block; margin-top:8px; color:#64748b; font-style:normal; font-size:13px; }
        .dashboard-section { padding:22px 24px; }
        .section-head { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; margin-bottom:16px; }
        .section-head h2 { margin:0 0 4px; color:#071a33; font-size:20px; line-height:1.25; font-weight:850; }
        .section-head p { margin:0; color:#64748b; }
        .dashboard-layout { display:grid; grid-template-columns:minmax(0,1.1fr) minmax(360px,.9fr); gap:18px; align-items:start; }
        .booking-list { display:grid; gap:10px; }
        .booking-row { display:grid; grid-template-columns:minmax(132px,.55fr) minmax(0,1fr) minmax(126px,.55fr) auto; gap:14px; align-items:center; padding:14px; border:1px solid #e2eaf3; border-radius:14px; background:#fff; }
        .booking-row__code strong, .booking-row__customer strong { display:block; color:#071a33; line-height:1.3; overflow-wrap:anywhere; }
        .booking-row small { color:#64748b; overflow-wrap:anywhere; }
        .status-badge { display:inline-flex; align-items:center; min-height:28px; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:850; white-space:nowrap; }
        .status-paid, .status-confirmed { background:#e5f8ed; color:#0c7c43; }
        .status-pending-transfer, .status-pending-payment { background:#fff4d6; color:#9f6500; }
        .status-cancelled, .status-failed { background:#ffe5e2; color:#bc3229; }
        .status-draft { background:#edf2f7; color:#526174; }
        .content-stack { display:grid; gap:18px; }
        .rank-list { display:grid; gap:10px; }
        .rank-row { display:grid; grid-template-columns:minmax(0,1fr) 86px; gap:12px; align-items:center; padding:12px 0; border-bottom:1px solid #edf2f7; }
        .rank-row:last-child { border-bottom:0; }
        .rank-row strong { display:block; color:#071a33; line-height:1.35; overflow-wrap:anywhere; }
        .rank-row small { color:#64748b; }
        .rank-value { text-align:right; color:#071a33; font-weight:850; }
        .rank-bar { grid-column:1 / -1; height:6px; border-radius:999px; overflow:hidden; background:#edf4fb; }
        .rank-bar span { display:block; height:100%; border-radius:inherit; background:#009cde; }
        .empty-state { padding:28px 16px; border:1px dashed #cbd8e6; border-radius:14px; color:#64748b; text-align:center; background:#fbfdff; }
        .quick-links { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; padding:0 24px 24px; }
        .quick-links a { display:flex; min-height:48px; align-items:center; justify-content:center; padding:10px 12px; border:1px solid #dbe5ef; border-radius:12px; background:#fff; color:#10233d; text-decoration:none; font-weight:800; text-align:center; }
        .quick-links a:hover { border-color:#009cde; color:#0075b8; background:#f5fbff; }
        @media (max-width: 1180px) {
            .dashboard-priority, .metric-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .dashboard-layout { grid-template-columns:1fr; }
        }
        @media (max-width: 767px) {
            .dashboard-hero, .section-head { grid-template-columns:1fr; display:grid; }
            .dashboard-actions { justify-content:flex-start; }
            .dashboard-priority, .metric-grid, .quick-links { grid-template-columns:1fr; padding-left:18px; padding-right:18px; }
            .dashboard-hero, .dashboard-section { padding:18px; }
            .dashboard-hero h1 { font-size:24px; }
            .booking-row { grid-template-columns:1fr; gap:8px; }
            .booking-row .btn { width:100%; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'dashboard']) ?>
<main class="admin-shell">
    <div class="dashboard-page">
        <section class="dashboard-panel">
            <div class="dashboard-hero">
                <div>
                    <h1>Tổng quan vận hành</h1>
                    <p>Theo dõi nhanh booking cần xử lý, review chờ duyệt, hiệu quả nội dung và truy cập 30 ngày gần nhất.</p>
                </div>
                <div class="dashboard-actions">
                    <a class="btn btn-primary" href="<?= site_url('admin/bookings') ?>">Xem bookings</a>
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/analytics') ?>">Analytics</a>
                </div>
            </div>

            <div class="dashboard-priority">
                <a class="priority-card <?= $pendingTransfer > 0 ? 'is-warning' : '' ?>" href="<?= site_url('admin/bookings?status=pending_transfer') ?>">
                    <small>Cần đối soát</small>
                    <strong><?= esc($formatNumber($pendingTransfer)) ?></strong>
                    <span>Booking chờ chuyển khoản, nên kiểm tra trước các việc khác.</span>
                </a>
                <a class="priority-card <?= $pendingReviews > 0 ? 'is-warning' : '' ?>" href="<?= site_url('admin/reviews?status=pending') ?>">
                    <small>Review chờ duyệt</small>
                    <strong><?= esc($formatNumber($pendingReviews)) ?></strong>
                    <span>Duyệt nhanh review mới để nội dung site cập nhật hơn.</span>
                </a>
                <a class="priority-card" href="<?= site_url('admin/booking-emails') ?>">
                    <small>Email booking</small>
                    <strong>Gửi</strong>
                    <span>Kiểm tra hàng đợi và gửi email nhắc thanh toán thủ công.</span>
                </a>
                <a class="priority-card <?= ! $analyticsReady ? 'is-danger' : '' ?>" href="<?= site_url('admin/analytics') ?>">
                    <small>Analytics</small>
                    <strong><?= $analyticsReady ? esc($formatNumber($analyticsVisits)) : 'Off' ?></strong>
                    <span><?= $analyticsReady ? 'Visits trong 30 ngày gần nhất.' : 'Chưa có cấu hình analytics đầy đủ.' ?></span>
                </a>
            </div>

            <div class="quick-links">
                <a href="<?= site_url('admin/tours') ?>">Tour</a>
                <a href="<?= site_url('admin/blogs') ?>">Bài viết</a>
                <a href="<?= site_url('admin/leads') ?>">CRM leads</a>
                <a href="<?= site_url('admin/promotion-codes') ?>">Mã giảm giá</a>
                <a href="<?= site_url('admin/media-audit') ?>">Media audit</a>
            </div>
        </section>

        <section class="metric-grid">
            <div class="metric-card">
                <span>Tổng booking</span>
                <strong><?= esc($formatNumber($bookingTotal)) ?></strong>
                <em>Toàn bộ booking trong hệ thống.</em>
            </div>
            <div class="metric-card">
                <span>Nội dung đang quản lý</span>
                <strong><?= esc($formatNumber($contentTotal)) ?></strong>
                <em><?= esc($formatNumber($tourTotal)) ?> tour · <?= esc($formatNumber($blogTotal)) ?> bài viết</em>
            </div>
            <div class="metric-card">
                <span>Pageviews 30 ngày</span>
                <strong><?= esc($formatNumber($analyticsPageviews)) ?></strong>
                <em><?= $analyticsReady ? 'Dữ liệu từ analytics nội bộ.' : 'Analytics chưa sẵn sàng.' ?></em>
            </div>
            <div class="metric-card">
                <span>Lượt xem nội dung</span>
                <strong><?= esc($formatNumber($totalContentViews)) ?></strong>
                <em><?= esc($formatNumber($tourViews)) ?> tour · <?= esc($formatNumber($blogViews)) ?> blog</em>
            </div>
        </section>

        <div class="dashboard-layout">
            <section class="dashboard-panel dashboard-section">
                <div class="section-head">
                    <div>
                        <h2>Booking mới nhất</h2>
                        <p>Danh sách gần đây để kiểm tra nhanh khách, tour và trạng thái thanh toán.</p>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/bookings') ?>">Xem tất cả</a>
                </div>

                <?php if (empty($recentBookings)): ?>
                    <div class="empty-state">Chưa có booking.</div>
                <?php else: ?>
                    <div class="booking-list">
                        <?php foreach ($recentBookings as $booking): ?>
                            <?php
                            $bookingStatus = (string) ($booking['payment_status'] ?? 'draft');
                            $amountDue = (float) ($booking['amount_due_vnd'] ?? $booking['grand_total'] ?? 0);
                            ?>
                            <article class="booking-row">
                                <div class="booking-row__code">
                                    <strong><?= esc((string) ($booking['booking_code'] ?? '')) ?></strong>
                                    <small><?= esc(app_datetime((string) ($booking['created_at'] ?? ''))) ?></small>
                                </div>
                                <div class="booking-row__customer">
                                    <strong><?= esc((string) ($booking['customer_name'] ?? '')) ?></strong>
                                    <small><?= esc((string) ($booking['tour_title'] ?? '')) ?></small>
                                </div>
                                <div>
                                    <span class="status-badge <?= esc($statusClass($bookingStatus)) ?>"><?= esc($statusLabels[$bookingStatus] ?? $bookingStatus) ?></span>
                                    <small class="d-block mt-1"><?= esc(number_format($amountDue, 0, ',', '.')) ?> đ</small>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/bookings/' . (int) ($booking['id'] ?? 0)) ?>">Mở</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <div class="content-stack">
                <section class="dashboard-panel dashboard-section">
                    <div class="section-head">
                        <div>
                            <h2>Tour được xem nhiều</h2>
                            <p>Ưu tiên tối ưu các tour đang có nhu cầu xem cao.</p>
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/tours') ?>">Tour</a>
                    </div>

                    <?php if (empty($topTours)): ?>
                        <div class="empty-state">Chưa có dữ liệu tour.</div>
                    <?php else: ?>
                        <div class="rank-list">
                            <?php foreach ($topTours as $tour): ?>
                                <?php $views = (int) ($tour['view_count'] ?? 0); ?>
                                <div class="rank-row">
                                    <div>
                                        <strong><?= esc((string) ($tour['name'] ?? 'Tour #' . ($tour['id'] ?? ''))) ?></strong>
                                        <small><?= esc((string) ($tour['status'] ?? '')) ?></small>
                                    </div>
                                    <div class="rank-value"><?= esc($formatNumber($views)) ?></div>
                                    <div class="rank-bar"><span style="width:<?= esc((string) max(8, min(100, round($views / $maxTourViews * 100))), 'attr') ?>%"></span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel dashboard-section">
                    <div class="section-head">
                        <div>
                            <h2>Blog được xem nhiều</h2>
                            <p>Theo dõi bài viết đang kéo traffic để cập nhật nội dung kịp thời.</p>
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/blogs') ?>">Blog</a>
                    </div>

                    <?php if (empty($topBlogs)): ?>
                        <div class="empty-state">Chưa có dữ liệu blog.</div>
                    <?php else: ?>
                        <div class="rank-list">
                            <?php foreach ($topBlogs as $blog): ?>
                                <?php $views = (int) ($blog['view_count'] ?? 0); ?>
                                <div class="rank-row">
                                    <div>
                                        <strong><?= esc((string) ($blog['title'] ?? 'Blog #' . ($blog['id'] ?? ''))) ?></strong>
                                        <small><?= esc((string) ($blog['status'] ?? '')) ?></small>
                                    </div>
                                    <div class="rank-value"><?= esc($formatNumber($views)) ?></div>
                                    <div class="rank-bar"><span style="width:<?= esc((string) max(8, min(100, round($views / $maxBlogViews * 100))), 'attr') ?>%"></span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
