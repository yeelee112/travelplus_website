<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - User Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f4f6f8; color:#172033; }
        .admin-shell { max-width:980px; margin:32px auto; padding:0 16px; }
        .admin-card { background:#fff; border:1px solid #e6ebf0; border-radius:18px; box-shadow:0 16px 40px rgba(23,32,51,.06); padding:28px; }
        label { font-weight:600; margin-bottom:6px; }
        .history-list { display:grid; gap:12px; }
        .history-item { border:1px solid #edf1f5; border-radius:14px; padding:14px 16px; background:#fbfcfe; }
        .history-item .top { display:flex; justify-content:space-between; gap:12px; margin-bottom:8px; }
        .history-item pre { margin:0; font-size:12px; background:#0b1220; color:#dfe7ff; border-radius:12px; padding:12px; white-space:pre-wrap; }
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
                <h1 class="h3 mb-1"><?= esc($pageTitle ?? 'User form') ?></h1>
                <p class="text-muted mb-0"><?= esc($pageDesc ?? '') ?></p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-end">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Users</a>
            </div>
        </div>

        <?php if (! empty(session()->getFlashdata('success'))): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
        <?php if (! empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?><div><?= esc($error) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= esc($formAction ?? site_url('admin/users')) ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Họ và tên</label>
                    <input class="form-control" name="full_name" value="<?= esc(old('full_name', (string) ($user['full_name'] ?? ''))) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="<?= esc(old('email', (string) ($user['email'] ?? ''))) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Username</label>
                    <input class="form-control" name="username" value="<?= esc(old('username', (string) ($user['username'] ?? ''))) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Số điện thoại</label>
                    <input class="form-control" name="phone" value="<?= esc(old('phone', (string) ($user['phone'] ?? ''))) ?>">
                </div>
                <div class="col-md-6">
                    <label>Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="active" <?= old('status', (string) ($user['status'] ?? 'active')) === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                        <option value="inactive" <?= old('status', (string) ($user['status'] ?? 'active')) === 'inactive' ? 'selected' : '' ?>>Tạm khóa</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_admin" value="1" <?= (int) old('is_admin', (int) ($user['is_admin'] ?? 0)) === 1 ? 'checked' : '' ?>>
                        <span class="form-check-label">Tài khoản admin</span>
                    </label>
                </div>
                <?php $isEdit = isset($user['id']) && (int) $user['id'] > 0; ?>
                <div class="col-12">
                    <label><?= $isEdit ? 'Mật khẩu mới' : 'Mật khẩu' ?></label>
                    <input type="password" class="form-control" name="<?= $isEdit ? 'new_password' : 'password' ?>" placeholder="<?= $isEdit ? 'Để trống nếu không đổi' : 'Ít nhất 6 ký tự' ?>">
                </div>
                <div class="col-12 d-flex gap-2 pt-2">
                    <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Lưu thay đổi' : 'Tạo tài khoản' ?></button>
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/users') ?>">Quay lại</a>
                </div>
            </div>
        </form>

        <?php if ($isEdit): ?>
            <hr class="my-4">
            <h2 class="h5 mb-3">Lịch sử thay đổi</h2>
            <?php if (empty($logs ?? [])): ?>
                <div class="text-muted">Chưa có log thay đổi hoặc bảng log chưa được tạo.</div>
            <?php else: ?>
                <div class="history-list">
                    <?php foreach (($logs ?? []) as $log): ?>
                        <div class="history-item">
                            <div class="top">
                                <div>
                                    <div class="fw-semibold"><?= esc((string) ($log['action'] ?? 'updated')) ?></div>
                                    <div class="text-muted small">
                                        <?= esc((string) ($log['actor_name'] ?? 'System')) ?>
                                        <?= ! empty($log['actor_email']) ? ' - ' . esc((string) $log['actor_email']) : '' ?>
                                    </div>
                                </div>
                                <div class="text-muted small"><?= esc(app_datetime((string) ($log['created_at'] ?? ''))) ?></div>
                            </div>
                            <?php if (! empty($log['changes_json'])): ?>
                                <pre><?= esc((string) $log['changes_json']) ?></pre>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
