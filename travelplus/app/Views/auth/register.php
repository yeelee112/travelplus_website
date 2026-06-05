<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error');
$authSuccess = $authSuccess ?? session()->getFlashdata('auth_success');
$googleEnabled = $googleEnabled ?? false;
$locale = service('request')->getLocale() ?: 'vi';
$returnTo = old('return_to', $returnTo ?? '');
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
$authKicker = $locale === 'en' ? 'Travel Plus Account' : 'Tài khoản Travel Plus';
$authHighlights = $locale === 'en'
    ? ['Save traveler details for future bookings', 'Track tour payment and booking status', 'Get support faster for tours, visa and MICE']
    : ['Lưu thông tin khách cho các lần đặt sau', 'Theo dõi thanh toán và trạng thái booking', 'Nhận hỗ trợ tour, visa và MICE nhanh hơn'];
?>
<section class="travelplus-auth-page">
    <div class="container">
        <div class="travelplus-auth-shell">
            <aside class="travelplus-auth-intro">
                <span><?= esc($authKicker) ?></span>
                <h1><?= esc($t('auth.register.title')) ?></h1>
                <p><?= esc($t('auth.register.desc')) ?></p>
                <ul>
                    <?php foreach ($authHighlights as $highlight): ?>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><?= esc($highlight) ?></li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="travelplus-auth-card">
                <div class="travelplus-auth-card-head">
                    <span><?= esc($authKicker) ?></span>
                    <h2><?= esc($t('auth.register.submit')) ?></h2>
                </div>

                <?php if (! empty($authError)): ?>
                    <div class="alert alert-danger"><?= esc($authError) ?></div>
                <?php endif; ?>

                <?php if (! empty($authSuccess)): ?>
                    <div class="alert alert-success"><?= esc($authSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.register', $locale) ?>" class="travelplus-auth-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
                    <label class="travelplus-auth-field">
                        <span><?= esc($t('auth.register.fullName')) ?></span>
                        <input type="text" name="full_name" value="<?= esc(old('full_name')) ?>" autocomplete="name" required>
                    </label>
                    <label class="travelplus-auth-field">
                        <span><?= esc(lang('Frontend.contact.email', [], $locale)) ?></span>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required>
                    </label>
                    <div class="travelplus-auth-field-grid">
                        <label class="travelplus-auth-field">
                            <span><?= esc($t('auth.register.password')) ?></span>
                            <input type="password" name="password" autocomplete="new-password" required>
                        </label>
                        <label class="travelplus-auth-field">
                            <span><?= esc($t('auth.register.passwordConfirm')) ?></span>
                            <input type="password" name="password_confirm" autocomplete="new-password" required>
                        </label>
                    </div>
                    <button type="submit" class="primary-btn1 two travelplus-auth-submit">
                        <span><?= esc($t('auth.register.submit')) ?></span>
                        <span><?= esc($t('auth.register.submit')) ?></span>
                    </button>
                    <?php if ($googleEnabled): ?>
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.google', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>" class="travelplus-auth-google-btn">
                            <img class="travelplus-auth-google-icon" src="<?= esc(base_url('assets/images/google-2025.png')) ?>" alt="" loading="lazy" decoding="async" width="20" height="20">
                            <span><?= esc($t('auth.register.google')) ?></span>
                        </a>
                    <?php endif; ?>
                    <p class="travelplus-auth-switch">
                        <?= esc($t('auth.register.hasAccount')) ?>
                        <a href="<?= \App\Data\LocalizedPathCatalog::url('auth.login', $locale) ?><?= $returnTo !== '' ? '?return_to=' . rawurlencode($returnTo) : '' ?>">
                            <?= esc($t('auth.register.loginLink')) ?>
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
