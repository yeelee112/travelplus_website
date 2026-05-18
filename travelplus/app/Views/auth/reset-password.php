<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error');
$authSuccess = session()->getFlashdata('auth_success');
$locale = service('request')->getLocale() ?: 'vi';
$title = $locale === 'en' ? 'Reset password' : 'Đặt lại mật khẩu';
$desc = $locale === 'en'
    ? 'Enter your new password for this account.'
    : 'Nhập mật khẩu mới cho tài khoản này.';
$submit = $locale === 'en' ? 'Update password' : 'Cập nhật mật khẩu';
?>
<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="contact-form-wrap">
                <div class="section-title mb-30 text-center">
                    <h2><?= esc($title) ?></h2>
                    <p><?= esc($desc) ?></p>
                </div>
                <?php if (! empty($authError)): ?><div class="alert alert-danger"><?= esc($authError) ?></div><?php endif; ?>
                <?php if (! empty($authSuccess)): ?><div class="alert alert-success"><?= esc($authSuccess) ?></div><?php endif; ?>
                <form method="post" action="">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.auth.register.password', [], $locale)) ?></label>
                                <input type="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.auth.register.passwordConfirm', [], $locale)) ?></label>
                                <input type="password" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="primary-btn1 two w-100">
                                <span><?= esc($submit) ?></span>
                                <span><?= esc($submit) ?></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
