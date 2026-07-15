<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:1320px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        .table td,.table th { vertical-align:middle; }
        .admin-toolbar { flex-wrap:wrap; justify-content:flex-end; }
    </style>
</head>
<body class="admin-app">
<?php $adminSection = 'users'; ?>
<?php helper('display'); ?>
<?= view('admin/partials/app_start', ['adminSection' => $adminSection]) ?>
<main class="admin-shell">
    <div class="admin-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Quản lý người dùng</h1>
                <p class="text-muted mb-0">Quản lý tài khoản, quyền admin và trạng thái hoạt động.</p>
            </div>
            <div class="d-flex gap-2 admin-toolbar">
                <a class="btn btn-primary" href="<?= site_url('admin/users/create') ?>">Tạo tài khoản</a>
            </div>
        </div>

        <?php if (! empty($success)): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
        <?php if (! empty($error)): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

        <form method="get" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm khóa</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Từ khóa</label>
                <input name="q" class="form-control" value="<?= esc($keyword ?? '') ?>" placeholder="Tên, email, username, số điện thoại">
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit">Lọc</button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Đặt lại</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Điện thoại</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Lần đăng nhập cuối</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Chưa có tài khoản nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?= esc((string) ($user['id'] ?? '')) ?></td>
                            <td><?= esc((string) ($user['full_name'] ?? '')) ?></td>
                            <td><?= esc((string) ($user['email'] ?? '')) ?></td>
                            <td><?= esc((string) ($user['username'] ?? '')) ?></td>
                            <td><?= esc((string) ($user['phone'] ?? '')) ?></td>
                            <td><span class="badge <?= ! empty($user['is_admin']) ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= ! empty($user['is_admin']) ? 'Admin' : 'Nhân sự' ?></span></td>
                            <td>
                                <?php $userStatus = (string) ($user['status'] ?? 'inactive'); ?>
                                <span class="status-badge <?= $userStatus === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= $userStatus === 'active' ? 'Đang hoạt động' : 'Tạm khóa' ?>
                                </span>
                            </td>
                            <td><?= esc(app_datetime((string) ($user['last_login_at'] ?? ''))) ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/users/' . (int) $user['id'] . '/edit') ?>">Sửa</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
