<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error');
$authSuccess = $authSuccess ?? session()->getFlashdata('auth_success');
$googleEnabled = $googleEnabled ?? false;
$locale = service('request')->getLocale() ?: 'vi';
$returnTo = old('return_to', $returnTo ?? '');
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
?>
<div class="container pt-100 pb-100">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
            <div class="contact-form-wrap">
                <div class="section-title mb-30 text-center">
                    <h2><?= esc($t('auth.loginPage.title')) ?></h2>
                    <p><?= esc($t('auth.loginPage.desc')) ?></p>
                </div>

                <?php if (! empty($authError)): ?>
                    <div class="alert alert-danger"><?= esc($authError) ?></div>
                <?php endif; ?>

                <?php if (! empty($authSuccess)): ?>
                    <div class="alert alert-success"><?= esc($authSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.login', $locale) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="form-inner">
                                <label><?= esc($t('auth.loginPage.identity')) ?></label>
                                <input type="text" name="identity" value="<?= esc(old('identity')) ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-inner">
                                <label><?= esc($t('auth.loginPage.password')) ?></label>
                                <input type="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="rememberMe" <?= old('remember_me') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rememberMe">
                                    <?= esc($locale === 'en' ? 'Remember me' : 'Ghi nhớ đăng nhập') ?>
                                </label>
                            </div>
                            <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.forgotPassword', $locale) ?>">
                                <?= esc($locale === 'en' ? 'Forgot password?' : 'Quên mật khẩu?') ?>
                            </a>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="primary-btn1 two w-100">
                                <span><?= esc($t('auth.loginPage.submit')) ?></span>
                                <span><?= esc($t('auth.loginPage.submit')) ?></span>
                            </button>
                        </div>
                        <?php if ($googleEnabled): ?>
                            <div class="col-12">
                                <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.google', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>" class="primary-btn w-100">
                                    <span><img src="<?= esc(base_url('assets/images/google-2025.png')) ?>" alt="Google Icon" loading="lazy" decoding="async" width="20" height="20" style="width: 20px; height: 20px; vertical-align: middle;"><?= esc($t('auth.loginPage.google')) ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="col-12 text-center">
                            <p class="mb-0">
                                <?= esc($t('auth.loginPage.noAccount')) ?>
                                <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.register', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>">
                                    <?= esc($t('auth.loginPage.registerLink')) ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
