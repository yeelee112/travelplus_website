<?php
$status = is_array($status ?? null) ? $status : [];
$readyToConnect = ! empty($status['secret_configured']) && ! empty($status['storage_ready']);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Kết nối Zalo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        .zalo-page { max-width:1080px; margin:28px auto; padding:0 16px; display:grid; gap:18px; }
        .zalo-panel { background:#fff; border:1px solid #dfe8f1; border-radius:12px; padding:24px; box-shadow:0 12px 30px rgba(15,23,42,.05); }
        .zalo-hero { display:flex; justify-content:space-between; align-items:flex-start; gap:24px; }
        .zalo-eyebrow { display:block; margin-bottom:8px; color:#007fba; font-size:12px; font-weight:800; text-transform:uppercase; }
        .zalo-hero h1 { margin:0 0 8px; color:#071a33; font-size:28px; font-weight:800; }
        .zalo-hero p { max-width:700px; margin:0; color:#64748b; line-height:1.6; }
        .zalo-state { flex:0 0 auto; border-radius:999px; padding:8px 12px; font-size:13px; font-weight:800; }
        .zalo-state--ok { color:#087443; background:#eaf8f1; border:1px solid #bce9d1; }
        .zalo-state--pending { color:#8a5800; background:#fff8e6; border:1px solid #f1d99a; }
        .zalo-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-top:22px; }
        .zalo-field { min-width:0; padding:16px; border:1px solid #e3eaf2; border-radius:10px; background:#f8fbff; }
        .zalo-field span { display:block; margin-bottom:6px; color:#64748b; font-size:11px; font-weight:800; text-transform:uppercase; }
        .zalo-field strong, .zalo-field code { color:#172033; overflow-wrap:anywhere; }
        .zalo-actions { display:flex; align-items:center; justify-content:space-between; gap:18px; margin-top:20px; padding-top:20px; border-top:1px solid #e5ebf2; }
        .zalo-actions p { margin:0; color:#64748b; font-size:13px; }
        .zalo-steps { margin:0; padding-left:20px; color:#334155; line-height:1.8; }
        .zalo-code { display:block; margin-top:14px; padding:14px; border-radius:8px; background:#0f172a; color:#e2e8f0; white-space:pre-wrap; overflow-wrap:anywhere; }
        @media (max-width:767px) {
            .zalo-page { margin:16px auto; padding:0 12px; }
            .zalo-panel { padding:18px; }
            .zalo-hero, .zalo-actions { display:grid; }
            .zalo-grid { grid-template-columns:1fr; }
            .zalo-actions .btn { width:100%; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'zalo']) ?>
<main class="zalo-page">
    <?php if (! empty($success)): ?><div class="alert alert-success mb-0"><?= esc((string) $success) ?></div><?php endif; ?>
    <?php if (! empty($error)): ?><div class="alert alert-danger mb-0"><?= esc((string) $error) ?></div><?php endif; ?>

    <section class="zalo-panel">
        <div class="zalo-hero">
            <div>
                <span class="zalo-eyebrow">Zalo Official Account</span>
                <h1>Kết nối OA với Travel Plus</h1>
                <p>Cấp quyền cho ứng dụng quản lý token OA dùng để gửi OTP, thông báo booking và điểm thành viên. Token được mã hóa trước khi lưu.</p>
            </div>
            <span class="zalo-state <?= ! empty($status['connected']) ? 'zalo-state--ok' : 'zalo-state--pending' ?>">
                <?= ! empty($status['connected']) ? 'Đã kết nối' : 'Chưa kết nối' ?>
            </span>
        </div>

        <div class="zalo-grid">
            <div class="zalo-field"><span>App ID</span><strong><?= esc((string) ($status['app_id'] ?? '')) ?></strong></div>
            <div class="zalo-field"><span>Callback URL</span><code><?= esc((string) ($status['callback_url'] ?? '')) ?></code></div>
            <div class="zalo-field"><span>Official Account</span><strong><?= esc((string) (($status['oa_name'] ?? '') ?: ($status['oa_id'] ?? 'Chưa có'))) ?></strong></div>
            <div class="zalo-field"><span>Token hết hạn</span><strong><?= esc((string) (($status['expires_at'] ?? '') ?: 'Chưa có')) ?></strong></div>
            <div class="zalo-field"><span>Mẫu OTP ZBS</span><strong><?= esc((string) (($status['otp_template_id'] ?? '') ?: 'Chưa cấu hình')) ?></strong></div>
            <div class="zalo-field"><span>Tham số OTP</span><strong><?= esc((string) (($status['otp_field'] ?? '') ?: 'Chưa cấu hình')) ?></strong></div>
        </div>

        <div class="zalo-actions">
            <p>Kết nối lại OA sẽ thay token hiện tại và không làm mất dữ liệu booking hoặc thành viên.</p>
            <a class="btn btn-primary btn-lg<?= $readyToConnect ? '' : ' disabled' ?>" href="<?= site_url('admin/zalo/connect') ?>"<?= $readyToConnect ? '' : ' aria-disabled="true"' ?>>
                <?= ! empty($status['connected']) ? 'Kết nối lại OA' : 'Kết nối Zalo OA' ?>
            </a>
        </div>
    </section>

    <?php if (empty($status['secret_configured']) || empty($status['storage_ready'])): ?>
        <section class="zalo-panel">
            <span class="zalo-eyebrow">Cấu hình còn thiếu</span>
            <ol class="zalo-steps">
                <?php if (empty($status['storage_ready'])): ?><li>Import <code>database/sql/2026-08-03_create_zalo_oa_connections.sql</code> bằng phpMyAdmin.</li><?php endif; ?>
                <?php if (empty($status['secret_configured'])): ?><li>Thêm App Secret vào <code>.env</code> trên hosting.</li><?php endif; ?>
                <li>Đăng ký chính xác Callback URL đang hiển thị phía trên trong Zalo Developers.</li>
            </ol>
            <?php if (empty($status['secret_configured'])): ?><code class="zalo-code">zalo.appSecret = "APP_SECRET_CUA_BAN"</code><?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
