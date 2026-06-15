<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1320px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .stat-card { background:#fbfcfe; border:1px solid #e5ebf2; border-radius:16px; padding:20px; height:100%; }
        .stat-label { color:#6b778c; font-size:13px; margin-bottom:8px; }
        .stat-value { font-size:32px; font-weight:700; line-height:1; }
        .table td,.table th { vertical-align:middle; }
        .dashboard-toolbar { flex-wrap:wrap; justify-content:flex-end; }
    </style>
</head>
<body>
<main class="admin-shell">
    <div class="admin-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h1 class="h3 mb-1">Admin dashboard</h1>
                <p class="text-muted mb-0">Tổng quan bookings, reviews, tours, blogs và lượt xem.</p>
            </div>
            <div class="d-flex gap-2 dashboard-toolbar">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/media-audit') ?>">Media audit</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/promotion-codes') ?>">Promotion codes</a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Bookings</div><div class="stat-value"><?= esc((string) ($stats['bookings_total'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Chờ chuyển khoản</div><div class="stat-value"><?= esc((string) ($stats['bookings_pending_transfer'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Reviews chờ duyệt</div><div class="stat-value"><?= esc((string) ($stats['reviews_pending'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Tours</div><div class="stat-value"><?= esc((string) ($stats['tours_total'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Blogs</div><div class="stat-value"><?= esc((string) ($stats['blogs_total'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Lượt xem tour</div><div class="stat-value"><?= esc(number_format((int) ($stats['tour_views_total'] ?? 0), 0, ',', '.')) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Lượt xem blog</div><div class="stat-value"><?= esc(number_format((int) ($stats['blog_views_total'] ?? 0), 0, ',', '.')) ?></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="admin-card">
                <h2 class="h5 mb-3">Booking mới</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Mã</th><th>Khách</th><th>Trạng thái</th><th class="text-end">Chi tiết</th></tr></thead>
                        <tbody>
                        <?php if (empty($recentBookings)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Chưa có booking.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?= esc((string) ($booking['booking_code'] ?? '')) ?></td>
                                    <td>
                                        <div><?= esc((string) ($booking['customer_name'] ?? '')) ?></div>
                                        <small class="text-muted"><?= esc((string) ($booking['tour_title'] ?? '')) ?></small>
                                    </td>
                                    <td><?= esc((string) ($booking['payment_status'] ?? '')) ?></td>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/bookings/' . (int) $booking['id']) ?>">Mở</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="admin-card mb-4">
                <h2 class="h5 mb-3">Tour được xem nhiều</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Tour</th><th>Views</th><th class="text-end">Sửa</th></tr></thead>
                        <tbody>
                        <?php if (empty($topTours)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topTours as $tour): ?>
                                <tr>
                                    <td><?= esc((string) ($tour['name'] ?? 'Tour #' . ($tour['id'] ?? ''))) ?></td>
                                    <td><?= esc(number_format((int) ($tour['view_count'] ?? 0), 0, ',', '.')) ?></td>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/tours/' . (int) $tour['id'] . '/edit') ?>">Edit</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-card">
                <h2 class="h5 mb-3">Blog được xem nhiều</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Blog</th><th>Views</th><th class="text-end">Sửa</th></tr></thead>
                        <tbody>
                        <?php if (empty($topBlogs)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topBlogs as $blog): ?>
                                <tr>
                                    <td><?= esc((string) ($blog['title'] ?? 'Blog #' . ($blog['id'] ?? ''))) ?></td>
                                    <td><?= esc(number_format((int) ($blog['view_count'] ?? 0), 0, ',', '.')) ?></td>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/blogs/' . (int) $blog['id'] . '/edit') ?>">Edit</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
