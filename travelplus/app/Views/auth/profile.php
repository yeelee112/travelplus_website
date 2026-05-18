<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
helper('display');

$locale = service('request')->getLocale() ?: 'vi';
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
$authSuccess = session()->getFlashdata('auth_success');
$authError = session()->getFlashdata('auth_error');
$statusValue = strtolower(trim((string) ($user['status'] ?? 'active')));
$statusLabel = match ($statusValue) {
    'active' => $locale === 'en' ? 'Active' : 'Đang hoạt động',
    'inactive' => $locale === 'en' ? 'Inactive' : 'Ngưng hoạt động',
    'blocked' => $locale === 'en' ? 'Blocked' : 'Bị khóa',
    default => ucfirst($statusValue !== '' ? $statusValue : ($locale === 'en' ? 'Unknown' : 'Không xác định')),
};
$statusClass = match ($statusValue) {
    'active' => 'success',
    'inactive' => 'secondary',
    'blocked' => 'danger',
    default => 'dark',
};
$lastLoginLabel = app_datetime(
    (string) ($user['last_login_at'] ?? ''),
    'd/m/Y H:i',
    $locale === 'en' ? 'Not available' : 'Chưa có dữ liệu'
);
?>
<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <?php if (! empty($authSuccess)): ?>
                <div class="alert alert-success mb-4"><?= esc($authSuccess) ?></div>
            <?php endif; ?>
            <?php if (! empty($authError)): ?>
                <div class="alert alert-danger mb-4"><?= esc($authError) ?></div>
            <?php endif; ?>

            <div class="contact-form-wrap">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div class="section-title mb-0">
                        <h2><?= esc($t('auth.profile.title')) ?></h2>
                        <p><?= esc($t('auth.profile.desc')) ?></p>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.logout', $locale) ?>" class="primary-btn1 transparent">
                            <span><?= esc($t('auth.logout')) ?></span>
                            <span><?= esc($t('auth.logout')) ?></span>
                        </a>
                    </div>
                </div>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.profile', $locale) ?>">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.fullName')) ?></label>
                                <input type="text" name="full_name" value="<?= esc((string) ($user['full_name'] ?? '')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.email')) ?></label>
                                <input type="text" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.username')) ?></label>
                                <input type="text" value="<?= esc((string) ($user['username'] ?? '')) ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.phone')) ?></label>
                                <input type="text" name="phone" value="<?= esc((string) ($user['phone'] ?? '')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.status')) ?></label>
                                <div class="border rounded-3 px-3 py-3 bg-light d-flex align-items-center gap-2">
                                    <span class="badge text-bg-<?= esc($statusClass) ?> px-3 py-2"><?= esc($statusLabel) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.profile.lastLogin')) ?></label>
                                <div class="border rounded-3 px-3 py-3 bg-light text-muted"><?= esc($lastLoginLabel) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($locale === 'en' ? 'New password' : 'Mật khẩu mới') ?></label>
                                <input type="password" name="new_password">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($locale === 'en' ? 'Confirm new password' : 'Xác nhận mật khẩu mới') ?></label>
                                <input type="password" name="new_password_confirm">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.logoutAll', $locale) ?>" onsubmit="return confirm('<?= esc($locale === 'en' ? 'This will sign you out on all remembered devices. Continue?' : 'Thao tác này sẽ đăng xuất bạn trên tất cả thiết bị đã ghi nhớ. Tiếp tục?') ?>');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger">
                                        <?= esc($locale === 'en' ? 'Log out all devices' : 'Đăng xuất mọi thiết bị') ?>
                                    </button>
                                </form>
                                <button type="submit" class="primary-btn1 two">
                                    <span><?= esc($locale === 'en' ? 'Save changes' : 'Lưu thay đổi') ?></span>
                                    <span><?= esc($locale === 'en' ? 'Save changes' : 'Lưu thay đổi') ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
