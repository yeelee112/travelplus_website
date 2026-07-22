<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error');
$authSuccess = session()->getFlashdata('auth_success');
$locale = service('request')->getLocale() ?: 'vi';
$title = $locale === 'en' ? 'Reset password' : 'Đặt lại mật khẩu';
$desc = $locale === 'en'
    ? 'Create a new password for your Travel Plus account.'
    : 'Tạo mật khẩu mới cho tài khoản Travel Plus của bạn.';
$submit = $locale === 'en' ? 'Update password' : 'Cập nhật mật khẩu';
$authKicker = $locale === 'en' ? 'Account recovery' : 'Khôi phục tài khoản';
$showPasswordLabel = $locale === 'en' ? 'Show password' : 'Hiện mật khẩu';
$hidePasswordLabel = $locale === 'en' ? 'Hide password' : 'Ẩn mật khẩu';
$authHighlights = $locale === 'en'
    ? ['Use at least 6 characters', 'Sign in again after updating', 'Avoid reusing an old password']
    : ['Dùng tối thiểu 6 ký tự', 'Đăng nhập lại sau khi cập nhật', 'Không nên dùng lại mật khẩu cũ'];
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
                <form method="post" action="" class="travelplus-auth-form">
                    <?= csrf_field() ?>
                    <div class="travelplus-auth-field-grid">
                        <label class="travelplus-auth-field">
                            <span><?= esc(lang('Frontend.auth.register.password', [], $locale)) ?></span>
                            <span class="travelplus-auth-password">
                                <input type="password" name="password" autocomplete="new-password" required>
                                <button
                                    type="button"
                                    data-password-toggle
                                    data-show-label="<?= esc($showPasswordLabel, 'attr') ?>"
                                    data-hide-label="<?= esc($hidePasswordLabel, 'attr') ?>"
                                    aria-label="<?= esc($showPasswordLabel, 'attr') ?>"
                                    aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </span>
                        </label>
                        <label class="travelplus-auth-field">
                            <span><?= esc(lang('Frontend.auth.register.passwordConfirm', [], $locale)) ?></span>
                            <span class="travelplus-auth-password">
                                <input type="password" name="password_confirm" autocomplete="new-password" required>
                                <button
                                    type="button"
                                    data-password-toggle
                                    data-show-label="<?= esc($showPasswordLabel, 'attr') ?>"
                                    data-hide-label="<?= esc($hidePasswordLabel, 'attr') ?>"
                                    aria-label="<?= esc($showPasswordLabel, 'attr') ?>"
                                    aria-pressed="false">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="primary-btn1 two travelplus-auth-submit">
                        <span><?= esc($submit) ?></span>
                        <span><?= esc($submit) ?></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
