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
                    <h2><?= esc($t('auth.register.title')) ?></h2>
                    <p><?= esc($t('auth.register.desc')) ?></p>
                </div>

                <?php if (! empty($authError)): ?>
                    <div class="alert alert-danger"><?= esc($authError) ?></div>
                <?php endif; ?>

                <?php if (! empty($authSuccess)): ?>
                    <div class="alert alert-success"><?= esc($authSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.register', service('request')->getLocale() === 'en' ? 'en' : 'vi') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="form-inner">
                                <label><?= esc($t('auth.register.fullName')) ?></label>
                                <input type="text" name="full_name" value="<?= esc(old('full_name')) ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.contact.email', [], $locale)) ?></label>
                                <input type="email" name="email" value="<?= esc(old('email')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.register.password')) ?></label>
                                <input type="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc($t('auth.register.passwordConfirm')) ?></label>
                                <input type="password" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="primary-btn1 two w-100">
                                <span><?= esc($t('auth.register.submit')) ?></span>
                                <span><?= esc($t('auth.register.submit')) ?></span>
                            </button>
                        </div>
                        <?php if ($googleEnabled): ?>
                            <div class="col-12">
                                <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.google', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>" class="primary-btn1 transparent w-100">
                                    <span><?= esc($t('auth.register.google')) ?></span>
                                    <span><?= esc($t('auth.register.google')) ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="col-12 text-center">
                            <p class="mb-0">
                                <?= esc($t('auth.register.hasAccount')) ?>
                                <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.login', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>"><?= esc($t('auth.register.loginLink')) ?></a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
