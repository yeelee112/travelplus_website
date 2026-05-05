<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error');
$authSuccess = $authSuccess ?? session()->getFlashdata('auth_success');
$googleEnabled = $googleEnabled ?? false;
?>
<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="contact-form-wrap">
                <div class="section-title mb-30 text-center">
                    <h2>Tạo tài khoản</h2>
                    <p>Đăng ký tài khoản để tiếp tục checkout và quản lý booking của bạn.</p>
                </div>

                <?php if (! empty($authError)): ?>
                    <div class="alert alert-danger"><?= esc($authError) ?></div>
                <?php endif; ?>

                <?php if (! empty($authSuccess)): ?>
                    <div class="alert alert-success"><?= esc($authSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= localized_url('account/register') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="form-inner">
                                <label>Họ và tên</label>
                                <input type="text" name="full_name" value="<?= esc(old('full_name')) ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-inner">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= esc(old('email')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label>Mật khẩu</label>
                                <input type="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label>Nhập lại mật khẩu</label>
                                <input type="password" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="primary-btn1 two w-100">
                                <span>Tạo tài khoản</span>
                                <span>Tạo tài khoản</span>
                            </button>
                        </div>
                        <?php if ($googleEnabled): ?>
                            <div class="col-12">
                                <a href="<?= localized_url('auth/google') ?>" class="primary-btn1 transparent w-100">
                                    <span>Đăng ký / đăng nhập với Google</span>
                                    <span>Đăng ký / đăng nhập với Google</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
