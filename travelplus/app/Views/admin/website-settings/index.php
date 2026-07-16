<?php
$settings = is_array($settings ?? null) ? $settings : [];
$value = static fn (string $key): string => (string) old($key, (string) ($settings[$key] ?? ''));
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Cấu hình website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(frontend_asset_url('assets/css/admin.css'), 'attr') ?>" rel="stylesheet">
    <style>
        body { background:#f5f7fa; color:#172033; }
        .settings-page { max-width:1080px; margin:28px auto; padding:0 16px; display:grid; gap:18px; }
        .settings-panel { background:#fff; border:1px solid #dfe6ee; border-radius:8px; box-shadow:0 8px 24px rgba(24,39,75,.05); }
        .settings-hero { padding:24px; }
        .settings-hero__eyebrow { display:block; margin-bottom:8px; color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; }
        .settings-hero h1 { margin:0 0 8px; color:#0b1f38; font-size:28px; font-weight:800; line-height:1.2; }
        .settings-hero p { max-width:760px; margin:0; color:#65748a; line-height:1.55; }
        .settings-section { padding:22px 24px 24px; }
        .settings-section + .settings-section { border-top:1px solid #e5ebf2; }
        .settings-section__head { margin-bottom:18px; }
        .settings-section__head h2 { margin:0 0 5px; color:#0b1f38; font-size:20px; font-weight:800; }
        .settings-section__head p { margin:0; color:#69788d; }
        .settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .settings-field--full { grid-column:1 / -1; }
        .settings-field label { display:block; margin-bottom:7px; color:#34465d; font-size:13px; font-weight:800; }
        .settings-field small { display:block; margin-top:6px; color:#758398; line-height:1.45; }
        .settings-field .form-control { min-height:44px; border-color:#ccd8e5; }
        .settings-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:18px 24px; border-top:1px solid #e5ebf2; background:#f9fbfd; }
        .settings-actions p { margin:0; color:#6b7a90; font-size:13px; }
        .settings-actions .btn { min-width:150px; }
        @media (max-width: 767px) {
            .settings-page { margin:16px auto; padding:0 12px; gap:14px; }
            .settings-hero, .settings-section { padding:18px; }
            .settings-hero h1 { font-size:24px; }
            .settings-grid { grid-template-columns:1fr; gap:16px; }
            .settings-field--full { grid-column:auto; }
            .settings-actions { display:grid; padding:18px; }
            .settings-actions .btn { width:100%; }
        }
    </style>
</head>
<body class="admin-app">
<?= view('admin/partials/app_start', ['adminSection' => 'website_settings']) ?>
<main class="settings-page">
    <section class="settings-panel">
        <div class="settings-hero">
            <span class="settings-hero__eyebrow">Thông tin công khai</span>
            <h1>Cấu hình liên hệ website</h1>
            <p>Các giá trị này được dùng chung cho header, footer, hộp liên hệ, trang dịch vụ và email tự động. Không lưu mật khẩu hoặc API key tại đây.</p>
        </div>
    </section>

    <?php if (! empty($success)): ?><div class="alert alert-success mb-0"><?= esc((string) $success) ?></div><?php endif; ?>
    <?php if (! empty($error)): ?><div class="alert alert-danger mb-0"><?= esc((string) $error) ?></div><?php endif; ?>

    <form class="settings-panel" method="post" action="<?= site_url('admin/website-settings') ?>">
        <?= csrf_field() ?>
        <section class="settings-section">
            <div class="settings-section__head">
                <h2>Hotline và email</h2>
                <p>Tách số dùng để gọi với cách hiển thị cho từng ngôn ngữ.</p>
            </div>
            <div class="settings-grid">
                <div class="settings-field settings-field--full">
                    <label for="hotlineE164">Số dùng cho liên kết gọi điện</label>
                    <input class="form-control" id="hotlineE164" name="hotline_e164" value="<?= esc($value('hotline_e164'), 'attr') ?>" maxlength="16" inputmode="tel" required>
                    <small>Dạng quốc tế không có khoảng trắng, ví dụ +84795681568.</small>
                </div>
                <div class="settings-field">
                    <label for="hotlineVi">Hiển thị tiếng Việt</label>
                    <input class="form-control" id="hotlineVi" name="hotline_vi" value="<?= esc($value('hotline_vi'), 'attr') ?>" maxlength="40" required>
                </div>
                <div class="settings-field">
                    <label for="hotlineEn">Hiển thị tiếng Anh</label>
                    <input class="form-control" id="hotlineEn" name="hotline_en" value="<?= esc($value('hotline_en'), 'attr') ?>" maxlength="40" required>
                </div>
                <div class="settings-field settings-field--full">
                    <label for="contactEmail">Email liên hệ</label>
                    <input class="form-control" id="contactEmail" type="email" name="email" value="<?= esc($value('email'), 'attr') ?>" maxlength="160" required>
                </div>
            </div>
        </section>

        <section class="settings-section">
            <div class="settings-section__head">
                <h2>Kênh mạng xã hội</h2>
                <p>Dùng URL HTTPS đầy đủ để các nút liên hệ mở đúng kênh.</p>
            </div>
            <div class="settings-grid">
                <div class="settings-field">
                    <label for="facebookUrl">Facebook</label>
                    <input class="form-control" id="facebookUrl" type="url" name="facebook_url" value="<?= esc($value('facebook_url'), 'attr') ?>" maxlength="500" required>
                </div>
                <div class="settings-field">
                    <label for="messengerUrl">Messenger</label>
                    <input class="form-control" id="messengerUrl" type="url" name="messenger_url" value="<?= esc($value('messenger_url'), 'attr') ?>" maxlength="500" required>
                </div>
                <div class="settings-field">
                    <label for="zaloUrl">Zalo</label>
                    <input class="form-control" id="zaloUrl" type="url" name="zalo_url" value="<?= esc($value('zalo_url'), 'attr') ?>" maxlength="500" required>
                </div>
                <div class="settings-field">
                    <label for="youtubeUrl">YouTube</label>
                    <input class="form-control" id="youtubeUrl" type="url" name="youtube_url" value="<?= esc($value('youtube_url'), 'attr') ?>" maxlength="500" required>
                </div>
            </div>
        </section>

        <div class="settings-actions">
            <p>Thay đổi có hiệu lực ngay sau khi lưu và không cần xóa cache.</p>
            <button class="btn btn-primary" type="submit">Lưu cấu hình</button>
        </div>
    </form>
</main>
<?= view('admin/partials/app_end') ?>
</body>
</html>
