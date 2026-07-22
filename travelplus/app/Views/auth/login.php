<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error') ?? session()->getFlashdata('error');
$authSuccess = $authSuccess ?? session()->getFlashdata('auth_success');
$googleEnabled = $googleEnabled ?? false;
$locale = service('request')->getLocale() ?: 'vi';
$returnTo = old('return_to', $returnTo ?? '');
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
if (is_string($authError) && stripos($authError, 'csrf') !== false) {
    $authError = $locale === 'en'
        ? 'Your session has expired. Please try signing in again.'
        : 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.';
}
$authKicker = $locale === 'en' ? 'Travel Plus Account' : 'Tài khoản Travel Plus';
$showPasswordLabel = $locale === 'en' ? 'Show password' : 'Hiện mật khẩu';
$hidePasswordLabel = $locale === 'en' ? 'Hide password' : 'Ẩn mật khẩu';
$authHighlights = $locale === 'en'
    ? ['Continue checkout without re-entering details', 'Track recent bookings and payment status', 'Receive faster support from Travel Plus']
    : ['Tiếp tục thanh toán không cần nhập lại thông tin', 'Theo dõi booking và trạng thái thanh toán', 'Nhận hỗ trợ nhanh hơn từ Travel Plus'];
?>
<section class="travelplus-auth-page">
    <div class="container">
        <div class="travelplus-auth-shell">
            <aside class="travelplus-auth-intro">
                <span><?= esc($authKicker) ?></span>
                <h1><?= esc($t('auth.loginPage.title')) ?></h1>
                <p><?= esc($t('auth.loginPage.desc')) ?></p>
                <ul>
                    <?php foreach ($authHighlights as $highlight): ?>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><?= esc($highlight) ?></li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="travelplus-auth-card">
                <div class="travelplus-auth-card-head">
                    <span><?= esc($authKicker) ?></span>
                    <h2><?= esc($t('auth.loginPage.submit')) ?></h2>
                </div>

                <?php if (! empty($authError)): ?>
                    <div class="alert alert-danger"><?= esc($authError) ?></div>
                <?php endif; ?>

                <?php if (! empty($authSuccess)): ?>
                    <div class="alert alert-success"><?= esc($authSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.login', $locale) ?>" class="travelplus-auth-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
                    <label class="travelplus-auth-field">
                        <span><?= esc($t('auth.loginPage.identity')) ?></span>
                        <input type="text" name="identity" value="<?= esc(old('identity')) ?>" autocomplete="username" required>
                    </label>
                    <label class="travelplus-auth-field">
                        <span><?= esc($t('auth.loginPage.password')) ?></span>
                        <span class="travelplus-auth-password">
                            <input type="password" name="password" autocomplete="current-password" required>
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
                    <div class="travelplus-auth-row">
                        <label class="travelplus-auth-check" for="rememberMe">
                            <input type="checkbox" name="remember_me" value="1" id="rememberMe" <?= old('remember_me') ? 'checked' : '' ?>>
                            <span><?= esc($locale === 'en' ? 'Remember me' : 'Ghi nhớ đăng nhập') ?></span>
                        </label>
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.forgotPassword', $locale) ?>">
                            <?= esc($locale === 'en' ? 'Forgot password?' : 'Quên mật khẩu?') ?>
                        </a>
                    </div>
                    <button type="submit" class="primary-btn1 two travelplus-auth-submit">
                        <span><?= esc($t('auth.loginPage.submit')) ?></span>
                        <span><?= esc($t('auth.loginPage.submit')) ?></span>
                    </button>
                    <?php if ($googleEnabled): ?>
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.google', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>" class="travelplus-auth-google-btn">
                            <img class="travelplus-auth-google-icon" src="<?= esc(base_url('assets/images/google-2025.png')) ?>" alt="" loading="lazy" decoding="async" width="20" height="20">
                            <span><?= esc($t('auth.loginPage.google')) ?></span>
                        </a>
                    <?php endif; ?>
                    <p class="travelplus-auth-switch">
                        <?= esc($t('auth.loginPage.noAccount')) ?>
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.register', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>">
                            <?= esc($t('auth.loginPage.registerLink')) ?>
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
