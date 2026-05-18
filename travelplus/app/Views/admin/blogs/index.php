<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Blogs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1240px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .table img { width:72px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #e5ebf2; }
        .badge-soft { background:#eef6ff; color:#0d6efd; border-radius:999px; padding:6px 10px; font-size:12px; font-weight:600; }
        .admin-toolbar { flex-wrap:wrap; justify-content:flex-end; }
        .blog-status-cell { min-width:136px; }
        .blog-status-form { margin-top:8px; }
        .blog-published { white-space:nowrap; min-width:110px; }
        .blog-actions { display:flex; justify-content:flex-end; align-items:flex-start; gap:8px; flex-wrap:wrap; min-width:190px; }
        .blog-actions form { margin:0; }
    </style>
</head>
<body>
<?php helper('display'); ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Blog management</h1>
                <p class="text-muted mb-0">Quản lý bài viết, trạng thái xuất bản, SEO và lượt xem.</p>
            </div>
            <div class="d-flex gap-2 admin-toolbar">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/bookings') ?>">Bookings</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/reviews') ?>">Reviews</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-primary" href="<?= site_url('admin/blogs/create') ?>">Create blog</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($status ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Keyword</label>
                <input name="q" class="form-control" value="<?= esc($keyword ?? '') ?>" placeholder="Title, slug, category, author">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title VI</th>
                    <th>Category</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($blogs)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Chưa có bài viết nào.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($blogs as $blog): ?>
                        <?php
                        $slugVi = (string) ($blog['slug_vi'] ?? '');
                        $statusValue = (string) ($blog['status'] ?? 'draft');
                        $publishedAt = (string) ($blog['published_at'] ?? '');
                        ?>
                        <tr>
                            <td>#<?= esc((string) ($blog['id'] ?? '')) ?></td>
                            <td>
                                <?php if (! empty($blog['thumbnail'])): ?>
                                    <img src="<?= esc(base_url((string) $blog['thumbnail'])) ?>" alt="<?= esc((string) ($blog['title_vi'] ?? '')) ?>">
                                <?php else: ?>
                                    <span class="text-muted small">No image</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc((string) ($blog['title_vi'] ?? '')) ?></div>
                                <div class="text-muted small"><?= esc($slugVi) ?></div>
                                <?php if ((int) ($blog['is_featured'] ?? 0) === 1): ?>
                                    <div class="mt-2"><span class="badge-soft">Featured</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= esc((string) ($blog['category'] ?? '')) ?></div>
                                <small class="text-muted"><?= esc((string) ($blog['author_name'] ?? '')) ?></small>
                            </td>
                            <td><?= esc(number_format((int) ($blog['view_count'] ?? 0), 0, ',', '.')) ?></td>
                            <td class="blog-status-cell">
                                <span class="badge <?= $statusValue === 'published' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                    <?= esc(ucfirst($statusValue)) ?>
                                </span>
                                <form class="blog-status-form" method="post" action="<?= site_url('admin/blogs/' . (int) $blog['id'] . '/status') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="status" value="<?= $statusValue === 'published' ? 'draft' : 'published' ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        <?= $statusValue === 'published' ? 'Unpublish' : 'Publish' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="blog-published">
                                <?= $publishedAt !== '' ? esc(app_datetime($publishedAt)) : '-' ?>
                            </td>
                            <td class="text-end">
                                <div class="blog-actions">
                                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/blogs/' . (int) $blog['id'] . '/edit') ?>">Edit</a>
                                    <?php if ($slugVi !== ''): ?>
                                        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('cam-hung-du-lich/' . $slugVi) ?>" target="_blank" rel="noopener">View</a>
                                    <?php endif; ?>
                                    <form method="post" action="<?= site_url('admin/blogs/' . (int) $blog['id'] . '/delete') ?>" onsubmit="return confirm('Delete this blog?');">
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
