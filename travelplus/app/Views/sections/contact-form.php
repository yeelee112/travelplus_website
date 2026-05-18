<?php $locale = service('request')->getLocale() ?: 'vi'; ?>
<div class="contact-form">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="contact-form-wrap">
                <div class="section-title text-center mb-60">
                    <h2><?= esc(lang('Frontend.contact.title', [], $locale)) ?></h2>
                    <p><?= esc(lang('Frontend.contact.desc', [], $locale)) ?></p>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="contactForm" action="<?= \App\Data\LocalizedPathCatalog::url('contact', $locale ?? (service('request')->getLocale() === 'en' ? 'en' : 'vi')) ?>" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="contact_form_token" value="<?= esc((string) ($contact_form_token ?? '')) ?>">

                    <div class="row g-4 mb-60">
                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.contact.name', [], $locale)) ?></label>
                                <input type="text" name="name" value="<?= old('name') ?>" placeholder="<?= esc(lang('Frontend.contact.namePlaceholder', [], $locale)) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.contact.email', [], $locale)) ?></label>
                                <input type="email" name="email" value="<?= old('email') ?>" placeholder="email@domain.com" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.contact.phone', [], $locale)) ?></label>
                                <input type="text" name="phone" value="<?= old('phone') ?>" placeholder="+84..." required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.contact.destination', [], $locale)) ?></label>
                                <input type="text" name="destination" value="<?= old('destination') ?>" placeholder="<?= esc(lang('Frontend.contact.destinationPlaceholder', [], $locale)) ?>">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-inner">
                                <label><?= esc(lang('Frontend.contact.message', [], $locale)) ?></label>
                                <textarea name="message" rows="6" placeholder="<?= esc(lang('Frontend.contact.messagePlaceholder', [], $locale)) ?>"><?= old('message') ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-inner2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="privacy_agree" value="1" id="contactCheck22" <?= old('privacy_agree') ? 'checked' : '' ?> required>
                                    <label class="form-check-label" for="contactCheck22">
                                        <?= esc(lang('Frontend.contact.privacyAgree', [], $locale)) ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        </div>

                        <div class="col-md-12 d-md-flex justify-content-md-end">
                            <button
                                type="submit"
                                class="primary-btn1"
                                id="contactSubmitBtn"
                                data-default-text="<?= esc(lang('Frontend.contact.submit', [], $locale)) ?>"
                                data-loading-text="<?= esc(lang('Frontend.contact.submitting', [], $locale)) ?>">
                                <span>
                                    <?= esc(lang('Frontend.contact.submit', [], $locale)) ?>
                                    <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z"></path>
                                    </svg>
                                </span>
                                <span>
                                    <?= esc(lang('Frontend.contact.submit', [], $locale)) ?>
                                    <svg width="10" height="10" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.73535 1.14746C9.57033 1.97255 9.32924 3.26406 9.24902 4.66797C9.16817 6.08312 9.25559 7.5453 9.70214 8.73633C9.84754 9.12406 9.65129 9.55659 9.26367 9.70215C8.9001 9.83849 8.4969 9.67455 8.32812 9.33398L8.29785 9.26367L8.19921 8.98438C7.73487 7.5758 7.67054 5.98959 7.75097 4.58203C7.77875 4.09598 7.82525 3.62422 7.87988 3.17969L1.53027 9.53027C1.23738 9.82317 0.762615 9.82317 0.469722 9.53027C0.176829 9.23738 0.176829 8.76262 0.469722 8.46973L6.83593 2.10254C6.3319 2.16472 5.79596 2.21841 5.25 2.24902C3.8302 2.32862 2.2474 2.26906 0.958003 1.79102L0.704097 1.68945L0.635738 1.65527C0.303274 1.47099 0.157578 1.06102 0.310542 0.704102C0.463655 0.347333 0.860941 0.170391 1.22363 0.28418L1.29589 0.310547L1.48828 0.387695C2.47399 0.751207 3.79966 0.827571 5.16601 0.750977C6.60111 0.670504 7.97842 0.428235 8.86132 0.262695L9.95312 0.0585938L9.73535 1.14746Z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
