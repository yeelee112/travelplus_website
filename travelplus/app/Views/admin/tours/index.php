<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1280px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .table td,.table th { vertical-align:middle; }
        .tour-actions { display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap; }
        .tour-actions form { margin:0; }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'tours'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Admin tours</h1>
                <p class="text-muted mb-0">Quản lý tour, chỉnh nội dung và theo dõi lượt xem.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
                <a class="btn btn-primary" href="<?= site_url('admin/tours/create') ?>">Create tour</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Tour</th>
                    <th>Loại</th>
                    <th>Views</th>
                    <th>Trạng thái</th>
                    <th>Giá</th>
                    <th>Cập nhật</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($tours)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Chưa có tour.</td></tr>
                <?php endif; ?>
                <?php foreach ($tours as $tour): ?>
                    <tr>
                        <td>#<?= esc((string) $tour['id']) ?></td>
                        <td><?= esc((string) $tour['name']) ?></td>
                        <td><?= esc((string) $tour['tour_type']) ?></td>
                        <td><?= esc(number_format((int) ($tour['view_count'] ?? 0), 0, ',', '.')) ?></td>
                        <td><?= esc((string) $tour['status']) ?></td>
                        <td><?= esc(number_format((float) ($tour['base_price'] ?? 0), 0, ',', '.')) ?> đ</td>
                        <td><?= esc(app_datetime((string) ($tour['updated_at'] ?? ''))) ?></td>
                        <td class="text-end">
                            <div class="tour-actions">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/tours/' . (int) $tour['id'] . '/edit') ?>">Edit</a>
                                <form method="post" action="<?= site_url('admin/tours/' . (int) $tour['id'] . '/delete') ?>" onsubmit="return confirm('Delete this tour?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
