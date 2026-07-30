<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$authError = session()->getFlashdata('auth_error') ?? session()->getFlashdata('error');
$authSuccess = $authSuccess ?? session()->getFlashdata('auth_success');
$googleEnabled = $googleEnabled ?? false;
$locale = service('request')->getLocale() ?: 'vi';
$returnTo = old('return_to', $returnTo ?? '');
$administrativeProvinces = is_array($administrativeProvinces ?? null) ? $administrativeProvinces : [];
$addressDataUrl = (string) ($addressDataUrl ?? '');
$recaptchaSiteKey = trim((string) ($recaptchaSiteKey ?? ''));
$selectedProvinceCode = (string) old('province_code');
$selectedWardCode = (string) old('ward_code');
$t = static fn(string $key, array $args = []) => lang('Frontend.' . $key, $args, $locale);
if (is_string($authError) && stripos($authError, 'csrf') !== false) {
    $authError = $locale === 'en'
        ? 'Your session has expired. Please try again.'
        : 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
}
$authKicker = $locale === 'en' ? 'Travel Plus Account' : 'Tài khoản Travel Plus';
$registerLoadingLabel = $locale === 'en' ? 'Creating account...' : 'Đang tạo tài khoản...';
$showPasswordLabel = $locale === 'en' ? 'Show password' : 'Hiện mật khẩu';
$hidePasswordLabel = $locale === 'en' ? 'Hide password' : 'Ẩn mật khẩu';
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

                <form
                    method="post"
                    action="<?= \App\Data\LocalizedPathCatalog::url('auth.register', $locale) ?>"
                    class="travelplus-auth-form"
                    data-register-form
                    data-recaptcha-site-key="<?= esc($recaptchaSiteKey, 'attr') ?>"
                    data-recaptcha-error="<?= esc($locale === 'en' ? 'Bot verification failed. Please try again.' : 'Xác minh chống bot không thành công. Vui lòng thử lại.', 'attr') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="return_to" value="<?= esc($returnTo) ?>">
                    <input type="hidden" name="recaptcha_token" data-register-recaptcha-token>
                    <div data-register-client-error class="alert alert-danger" role="alert" hidden></div>
                    <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">
                        <label for="register-company-website">Website</label>
                        <input id="register-company-website" type="text" name="company_website" value="" tabindex="-1" autocomplete="off">
                    </div>
                    <label class="travelplus-auth-field">
                        <span><?= esc($t('auth.register.fullName')) ?></span>
                        <input type="text" name="full_name" value="<?= esc(old('full_name')) ?>" autocomplete="name" required>
                    </label>
                    <label class="travelplus-auth-field">
                        <span><?= esc(lang('Frontend.contact.email', [], $locale)) ?></span>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required>
                    </label>
                    <label class="travelplus-auth-field">
                        <span><?= esc($t('auth.register.phone')) ?></span>
                        <input
                            type="tel"
                            name="phone"
                            value="<?= esc(old('phone')) ?>"
                            autocomplete="tel"
                            inputmode="tel"
                            placeholder="<?= esc($locale === 'en' ? 'E.g. 079 568 1568' : 'VD: 079 568 1568', 'attr') ?>"
                            required>
                    </label>
                    <div
                        class="travelplus-address-selector"
                        data-address-selector
                        data-address-source="<?= esc($addressDataUrl, 'attr') ?>"
                        data-selected-ward="<?= esc($selectedWardCode, 'attr') ?>"
                        data-ward-first="<?= esc($t('auth.register.wardFirst'), 'attr') ?>"
                        data-ward-placeholder="<?= esc($t('auth.register.wardPlaceholder'), 'attr') ?>"
                        data-loading="<?= esc($t('auth.register.addressLoading'), 'attr') ?>"
                        data-error="<?= esc($t('auth.register.addressLoadError'), 'attr') ?>">
                        <div class="travelplus-auth-field-grid">
                            <label class="travelplus-auth-field">
                                <span><?= esc($t('auth.register.province')) ?></span>
                                <select name="province_code" required data-address-province>
                                    <option value=""><?= esc($t('auth.register.provincePlaceholder')) ?></option>
                                    <?php foreach ($administrativeProvinces as $province): ?>
                                        <option
                                            value="<?= esc((string) ($province['code'] ?? ''), 'attr') ?>"
                                            <?= $selectedProvinceCode === (string) ($province['code'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc((string) ($province['name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="travelplus-auth-field">
                                <span><?= esc($t('auth.register.ward')) ?></span>
                                <select name="ward_code" required disabled data-address-ward>
                                    <option value=""><?= esc($t('auth.register.wardFirst')) ?></option>
                                </select>
                            </label>
                        </div>
                        <label class="travelplus-auth-field">
                            <span><?= esc($t('auth.register.addressLine')) ?></span>
                            <input
                                type="text"
                                name="address_line"
                                value="<?= esc(old('address_line')) ?>"
                                autocomplete="street-address"
                                maxlength="255"
                                placeholder="<?= esc($t('auth.register.addressLinePlaceholder'), 'attr') ?>"
                                required
                                data-address-line>
                        </label>
                        <small class="travelplus-address-status" data-address-status aria-live="polite"></small>
                    </div>
                    <div class="travelplus-auth-field-grid">
                        <label class="travelplus-auth-field">
                            <span><?= esc($t('auth.register.password')) ?></span>
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
                            <span><?= esc($t('auth.register.passwordConfirm')) ?></span>
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
                    <button
                        type="submit"
                        class="primary-btn1 two travelplus-auth-submit"
                        data-register-submit
                        data-default-label="<?= esc($t('auth.register.submit'), 'attr') ?>"
                        data-loading-label="<?= esc($registerLoadingLabel, 'attr') ?>">
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
<?php if ($recaptchaSiteKey !== ''): ?>
<script defer src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey, 'url') ?>"></script>
<?php endif; ?>
<script defer src="<?= esc(frontend_asset_url('assets/js/address-selector.js'), 'attr') ?>"></script>
<script>
(() => {
    const form = document.querySelector('[data-register-form]');
    const submitButton = form?.querySelector('[data-register-submit]');
    if (!(form instanceof HTMLFormElement) || !(submitButton instanceof HTMLButtonElement)) {
        return;
    }

    const labels = Array.from(submitButton.querySelectorAll(':scope > span'));
    const recaptchaInput = form.querySelector('[data-register-recaptcha-token]');
    const clientError = form.querySelector('[data-register-client-error]');
    const defaultLabel = submitButton.dataset.defaultLabel || 'Register';
    const loadingLabel = submitButton.dataset.loadingLabel || 'Processing...';
    const recaptchaSiteKey = form.dataset.recaptchaSiteKey || '';
    const recaptchaError = form.dataset.recaptchaError || 'Bot verification failed. Please try again.';
    let submitting = false;

    const renderLabel = (label, loading) => {
        label.textContent = '';
        if (loading) {
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm';
            spinner.setAttribute('aria-hidden', 'true');
            label.append(spinner, document.createTextNode(loadingLabel));
            return;
        }
        label.textContent = defaultLabel;
    };

    const setSubmitting = (loading) => {
        submitting = loading;
        submitButton.disabled = loading;
        submitButton.setAttribute('aria-disabled', loading ? 'true' : 'false');
        form.setAttribute('aria-busy', loading ? 'true' : 'false');
        submitButton.style.pointerEvents = loading ? 'none' : '';
        submitButton.style.cursor = loading ? 'wait' : '';
        submitButton.style.opacity = loading ? '.78' : '';
        labels.forEach((label) => renderLabel(label, loading));
    };

    const showClientError = (message) => {
        if (!(clientError instanceof HTMLElement)) {
            window.alert(message);
            return;
        }
        clientError.textContent = message;
        clientError.hidden = false;
        clientError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (submitting) {
            return;
        }
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (clientError instanceof HTMLElement) {
            clientError.hidden = true;
            clientError.textContent = '';
        }
        setSubmitting(true);

        if (
            recaptchaSiteKey === ''
            || !(recaptchaInput instanceof HTMLInputElement)
            || typeof window.grecaptcha === 'undefined'
        ) {
            setSubmitting(false);
            showClientError(recaptchaError);
            return;
        }

        window.grecaptcha.ready(() => {
            window.grecaptcha.execute(recaptchaSiteKey, { action: 'register' })
                .then((token) => {
                    recaptchaInput.value = token;
                    form.submit();
                })
                .catch(() => {
                    setSubmitting(false);
                    showClientError(recaptchaError);
                });
        });
    });

    window.addEventListener('pageshow', () => {
        setSubmitting(false);
        if (recaptchaInput instanceof HTMLInputElement) {
            recaptchaInput.value = '';
        }
    });
})();
</script>
<?= $this->endSection() ?>
