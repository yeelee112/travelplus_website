<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1380px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .admin-toolbar { flex-wrap:wrap; justify-content:flex-end; }
        .reviews-table { min-width:1180px; }
        .reviews-table td,
        .reviews-table th { vertical-align:top; padding-top:18px; padding-bottom:18px; }
        .review-tour-title { font-weight:700; line-height:1.45; margin-bottom:4px; }
        .review-tour-meta,
        .review-reviewer-meta,
        .review-created-meta { color:#6b778c; font-size:13px; }
        .review-reviewer-name { font-weight:600; margin-bottom:4px; }
        .rating-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px 10px; min-width:180px; }
        .rating-chip { display:flex; justify-content:space-between; gap:10px; align-items:center; border:1px solid #e6ebf0; border-radius:999px; padding:4px 10px; font-size:12px; background:#fbfcfe; }
        .rating-chip strong { font-weight:700; color:#172033; }
        .review-copy { max-width:260px; }
        .review-copy-title { font-weight:600; margin-bottom:4px; }
        .review-copy-text {
            color:#334155;
            display:-webkit-box;
            -webkit-line-clamp:3;
            -webkit-box-orient:vertical;
            overflow:hidden;
            line-height:1.6;
        }
        .review-status .badge { font-size:12px; padding:7px 10px; border-radius:999px; }
        .review-actions { display:grid; grid-template-columns:repeat(2, minmax(92px, 1fr)); gap:8px; min-width:210px; }
        .review-actions form { margin:0; }
        .review-actions .btn,
        .review-actions a { width:100%; }
        .review-actions .btn-danger-wide { grid-column:1 / -1; }
    </style>
</head>
<body>
<?php helper('display'); ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Review moderation</h1>
                <p class="text-muted mb-0">Duyệt, ẩn hoặc đưa review tour về trạng thái chờ.</p>
            </div>
            <div class="d-flex gap-2 admin-toolbar">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= ($status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="hidden" <?= ($status ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Từ khóa</label>
                <input name="q" class="form-control" value="<?= esc($keyword ?? '') ?>" placeholder="Người review, email, tour, nội dung">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table reviews-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Tour</th>
                    <th>Reviewer</th>
                    <th>Ratings</th>
                    <th>Nội dung</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($reviews)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Chưa có review nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td class="fw-semibold">#<?= esc((string) $review['id']) ?></td>
                            <td>
                                <div class="review-tour-title"><?= esc((string) ($review['tour_name'] ?? '')) ?></div>
                                <div class="review-tour-meta">Tour #<?= esc((string) ($review['tour_id'] ?? '')) ?></div>
                            </td>
                            <td>
                                <div class="review-reviewer-name"><?= esc((string) ($review['reviewer_name'] ?? '')) ?></div>
                                <div class="review-reviewer-meta"><?= esc((string) ($review['reviewer_email'] ?? '')) ?></div>
                            </td>
                            <td>
                                <div class="rating-list">
                                    <div class="rating-chip"><span>Overall</span><strong><?= esc((string) ($review['rating_overall'] ?? '0')) ?></strong></div>
                                    <div class="rating-chip"><span>Destination</span><strong><?= esc((string) ($review['rating_destination'] ?? '0')) ?></strong></div>
                                    <div class="rating-chip"><span>Transport</span><strong><?= esc((string) ($review['rating_transport'] ?? '0')) ?></strong></div>
                                    <div class="rating-chip"><span>Value</span><strong><?= esc((string) ($review['rating_value'] ?? '0')) ?></strong></div>
                                </div>
                            </td>
                            <td>
                                <div class="review-copy">
                                    <?php if (! empty($review['title'])): ?><div class="review-copy-title"><?= esc((string) $review['title']) ?></div><?php endif; ?>
                                    <div class="review-copy-text"><?= esc((string) ($review['content'] ?? '')) ?></div>
                                </div>
                            </td>
                            <td class="review-status">
                                <span class="badge <?= ($review['status'] ?? '') === 'approved' ? 'text-bg-success' : (($review['status'] ?? '') === 'hidden' ? 'text-bg-secondary' : 'text-bg-warning') ?>">
                                    <?= esc(ucfirst((string) ($review['status'] ?? 'pending'))) ?>
                                </span>
                            </td>
                            <td>
                                <div class="review-created-meta"><?= esc(app_datetime((string) ($review['created_at'] ?? ''))) ?></div>
                            </td>
                            <td class="text-end">
                                <div class="review-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/reviews/' . (int) $review['id']) ?>">Open</a>
                                    <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Approve</button>
                                    </form>
                                    <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Pending</button>
                                    </form>
                                    <form method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/status') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="status" value="hidden">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Hide</button>
                                    </form>
                                    <form class="btn-danger-wide" method="post" action="<?= site_url('admin/reviews/' . (int) $review['id'] . '/delete') ?>" onsubmit="return confirm('Delete this review?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
