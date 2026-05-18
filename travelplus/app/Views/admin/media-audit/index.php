<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Media audit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1320px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .stat-card { background:#fbfcfe; border:1px solid #e5ebf2; border-radius:16px; padding:20px; height:100%; }
        .stat-label { color:#6b778c; font-size:13px; margin-bottom:8px; }
        .stat-value { font-size:30px; font-weight:700; line-height:1; }
        .dashboard-toolbar { flex-wrap:wrap; justify-content:flex-end; }
        .table td,.table th { vertical-align:middle; }
        .path-cell { font-family:Consolas,monospace; font-size:13px; word-break:break-all; }
    </style>
</head>
<body>
<main class="admin-shell">
    <div class="admin-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h1 class="h3 mb-1">Media audit</h1>
                <p class="text-muted mb-0">Quét file trong <code>uploads/blogs</code> và <code>uploads/tours</code>, đối chiếu với dữ liệu đang được tham chiếu trong database.</p>
            </div>
            <div class="d-flex gap-2 dashboard-toolbar">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin') ?>">Dashboard</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/tours') ?>">Tours</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/blogs') ?>">Blogs</a>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
            </div>
        </div>
    </div>

    <?php if (! empty($success)): ?>
        <div class="alert alert-success"><?= esc((string) $success) ?></div>
    <?php endif; ?>
    <?php if (! empty($error)): ?>
        <div class="alert alert-danger"><?= esc((string) $error) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Blog refs</div><div class="stat-value"><?= esc((string) ($report['stats']['blog_referenced'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Tour refs</div><div class="stat-value"><?= esc((string) ($report['stats']['tour_referenced'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Blog files</div><div class="stat-value"><?= esc((string) ($report['stats']['blog_on_disk'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card"><div class="stat-label">Tour files</div><div class="stat-value"><?= esc((string) ($report['stats']['tour_on_disk'] ?? 0)) ?></div></div></div>
        <div class="col-md-4 col-xl-2"><div class="stat-card border-danger-subtle bg-danger-subtle"><div class="stat-label">Orphans</div><div class="stat-value"><?= esc((string) ($report['stats']['orphan_total'] ?? 0)) ?></div></div></div>
    </div>

    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1">Orphan files</h2>
                <p class="text-muted mb-0">Danh sách này chỉ bao gồm file trong phạm vi quản lý của blog và tour.</p>
            </div>
            <?php if (! empty($report['orphans'])): ?>
                <form method="post" action="<?= site_url('admin/media-audit/delete-orphans') ?>" onsubmit="return confirm('Xóa toàn bộ file mồ côi đang hiển thị?');">
                    <?php foreach ($report['orphans'] as $orphan): ?>
                        <input type="hidden" name="files[]" value="<?= esc((string) $orphan['path']) ?>">
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-danger">Delete all orphans</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th style="width:60px">#</th>
                    <th>Path</th>
                    <th style="width:140px">Size</th>
                    <th style="width:190px">Modified</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($report['orphans'])): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Không có file mồ côi trong phạm vi audit hiện tại.</td></tr>
                <?php else: ?>
                    <?php foreach ($report['orphans'] as $index => $orphan): ?>
                        <tr>
                            <td><?= esc((string) ($index + 1)) ?></td>
                            <td class="path-cell"><?= esc((string) $orphan['path']) ?></td>
                            <td><?= esc(number_format(((int) $orphan['size']) / 1024, 1, ',', '.')) ?> KB</td>
                            <td><?= esc((string) $orphan['modified_at']) ?></td>
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
