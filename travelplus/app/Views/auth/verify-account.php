<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$locale = service('request')->getLocale() ?: 'vi';
$isEnglish = $locale === 'en';
$isZalo = ($channel ?? '') === \App\Services\AccountVerificationService::CHANNEL_ZALO;
$authError = session()->getFlashdata('auth_error');
$authSuccess = session()->getFlashdata('auth_success');
?>
<section class="travelplus-auth-page travelplus-auth-verify-page">
    <div class="container">
        <div class="travelplus-auth-shell travelplus-auth-shell--compact">
            <aside class="travelplus-auth-intro">
                <span>Travel Plus Account</span>
                <h1><?= esc($isEnglish ? 'One last security step' : 'Còn một bước bảo mật') ?></h1>
                <p><?= esc($isEnglish
                    ? 'Verification protects your bookings, membership points, and personal details.'
                    : 'Xác thực giúp bảo vệ booking, Dặm Hành Trình và thông tin cá nhân của bạn.') ?></p>
                <ul>
                    <li><i class="bi bi-shield-check" aria-hidden="true"></i><?= esc($isEnglish ? 'Verification codes expire after 5 minutes' : 'Mã xác thực hết hạn sau 5 phút') ?></li>
                    <li><i class="bi bi-lock" aria-hidden="true"></i><?= esc($isEnglish ? 'Never share your code with anyone' : 'Không cung cấp mã cho bất kỳ ai') ?></li>
                </ul>
            </aside>

            <div class="travelplus-auth-card travelplus-verify-card">
                <div class="travelplus-verify-icon" aria-hidden="true">
                    <i class="bi <?= $isZalo ? 'bi-chat-dots' : 'bi-envelope-check' ?>"></i>
                </div>
                <div class="travelplus-auth-card-head travelplus-verify-head">
                    <span><?= esc($isZalo ? 'Zalo OTP' : 'Email OTP') ?></span>
                    <h2><?= esc(! empty($deliveryFailed)
                        ? ($isEnglish ? 'Message not delivered yet' : 'Chưa gửi được xác thực')
                        : ($isEnglish ? 'Enter your verification code' : 'Nhập mã xác thực')) ?></h2>
                    <p><?php if (! empty($deliveryFailed)): ?>
                        <?= esc($isEnglish ? 'Request another message for ' : 'Hãy yêu cầu gửi lại tới ') ?><strong><?= esc($recipient ?? '') ?></strong>.
                    <?php else: ?>
                        <?= esc($isEnglish ? 'We sent a 6-digit code to ' : 'Mã gồm 6 chữ số đã được gửi tới ') ?><strong><?= esc($recipient ?? '') ?></strong>.
                    <?php endif; ?></p>
                </div>

                <?php if (! empty($authError)): ?>
                    <div class="alert alert-danger"><?= esc($authError) ?></div>
                <?php endif; ?>
                <?php if (! empty($authSuccess)): ?>
                    <div class="alert alert-success"><?= esc($authSuccess) ?></div>
                <?php endif; ?>
                <?php if (! empty($deliveryFailed)): ?>
                    <div class="travelplus-verify-note">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <span><?= esc($isEnglish ? 'The previous message was not delivered. Request another message below.' : 'Lần gửi trước chưa thành công. Bạn có thể yêu cầu gửi lại bên dưới.') ?></span>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.verify', $locale) ?>" class="travelplus-auth-form">
                    <?= csrf_field() ?>
                    <label class="travelplus-auth-field travelplus-otp-field">
                        <span><?= esc($isEnglish ? 'Verification code' : 'Mã xác thực') ?></span>
                        <input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="000000" aria-describedby="otp-expiry-note" required autofocus>
                        <small id="otp-expiry-note"><?= esc($isEnglish ? 'The newest code is valid for 5 minutes.' : 'Mã mới nhất có hiệu lực trong 5 phút.') ?></small>
                    </label>
                    <button type="submit" class="primary-btn1 two travelplus-auth-submit">
                        <span><?= esc($isEnglish ? 'Verify account' : 'Xác thực tài khoản') ?></span>
                        <span><?= esc($isEnglish ? 'Verify account' : 'Xác thực tài khoản') ?></span>
                    </button>
                </form>

                <div class="travelplus-verify-actions">
                    <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.verifyResend', $locale) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="travelplus-verify-link-btn">
                            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                            <?= esc($isEnglish ? 'Send again' : 'Gửi lại') ?>
                        </button>
                    </form>
                    <?php if ($isZalo): ?>
                        <form method="post" action="<?= \App\Data\LocalizedPathCatalog::url('auth.verifyUseEmail', $locale) ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="travelplus-verify-link-btn">
                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                <?= esc($isEnglish ? 'Use email instead' : 'Xác thực bằng email') ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
