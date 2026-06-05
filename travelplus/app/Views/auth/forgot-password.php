<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error');
$authSuccess = session()->getFlashdata('auth_success');
$locale = service('request')->getLocale() ?: 'vi';
$title = $locale === 'en' ? 'Forgot password' : 'Quên mật khẩu';
$desc = $locale === 'en'
    ? 'Enter your email address. If the account exists, Travel Plus will send a reset link to your inbox.'
    : 'Nhập email của bạn. Nếu tài khoản tồn tại, Travel Plus sẽ gửi liên kết đặt lại mật khẩu vào hộp thư.';
$submit = $locale === 'en' ? 'Send reset link' : 'Gửi liên kết đặt lại';
$authKicker = $locale === 'en' ? 'Account recovery' : 'Khôi phục tài khoản';
$authHighlights = $locale === 'en'
    ? ['Reset links expire after 60 minutes', 'Your current password stays unchanged until reset', 'Contact Travel Plus if you need booking support']
    : ['Liên kết đặt lại hết hạn sau 60 phút', 'Mật khẩu hiện tại vẫn giữ nguyên đến khi bạn đổi', 'Liên hệ Travel Plus nếu cần hỗ trợ booking'];
?>
<section class="travelplus-auth-page">
    <div class="container">
        <div class="travelplus-auth-shell travelplus-auth-shell--compact">
            <aside class="travelplus-auth-intro">
                <span><?= esc($authKicker) ?></span>
                <h1><?= esc($title) ?></h1>
                <p><?= esc($desc) ?></p>
                <ul>
                    <?php foreach ($authHighlights as $highlight): ?>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><?= esc($highlight) ?></li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="travelplus-auth-card">
                <div class="travelplus-auth-card-head">
                    <span><?= esc($authKicker) ?></span>
                    <h2><?= esc($submit) ?></h2>
                </div>
                <?php if (! empty($authError)): ?><div class="alert alert-danger"><?= esc($authError) ?></div><?php endif; ?>
                <?php if (! empty($authSuccess)): ?><div class="alert alert-success"><?= esc($authSuccess) ?></div><?php endif; ?>
                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.forgotPassword', $locale) ?>" class="travelplus-auth-form">
                    <?= csrf_field() ?>
                    <label class="travelplus-auth-field">
                        <span><?= esc(lang('Frontend.contact.email', [], $locale)) ?></span>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required>
                    </label>
                    <button type="submit" class="primary-btn1 two travelplus-auth-submit">
                        <span><?= esc($submit) ?></span>
                        <span><?= esc($submit) ?></span>
                    </button>
                    <p class="travelplus-auth-switch">
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.login', $locale) ?>">
                            <?= esc($locale === 'en' ? 'Back to sign in' : 'Quay lại đăng nhập') ?>
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
