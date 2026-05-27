<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>

<div class="contact-page pt-100 mb-100">
    <div class="container">
        <?= $this->include('sections/company-info') ?>
        <?= $this->include('sections/contact-form') ?>
    </div>
</div>

<div class="contact-map-section">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.1025878711866!2d106.68068027586887!3d10.803454358692889!2m3!1f0!2f0!3f0!2m3!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f186f084a0d%3A0xe0b586169a7017dd!2sTravel%20Plus%20Co.%2C%20Ltd!5e0!3m2!1sen!2s!4v1771928131280!5m2!1sen!2s"
        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>

<?php
$locale = service('request')->getLocale() ?: 'vi';
$recaptchaSiteKey = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'");
?>
<script>
(() => {
    const form = document.getElementById('contactForm');
    const submitButton = document.getElementById('contactSubmitBtn');

    if (!form || !submitButton) {
        return;
    }

    let isSubmitting = false;
    const defaultText = submitButton.dataset.defaultText || <?= json_encode(lang('Frontend.contact.submit', [], $locale)) ?>;
    const loadingText = submitButton.dataset.loadingText || <?= json_encode(lang('Frontend.contact.submitting', [], $locale)) ?>;
    const recaptchaError = <?= json_encode(lang('Frontend.contact.recaptchaFailed', [], $locale)) ?>;
    const recaptchaSiteKey = <?= json_encode($recaptchaSiteKey, JSON_UNESCAPED_SLASHES) ?>;

    const setSubmittingState = (submitting) => {
        isSubmitting = submitting;
        submitButton.disabled = submitting;
        submitButton.setAttribute('aria-disabled', submitting ? 'true' : 'false');

        const spans = submitButton.querySelectorAll('span');
        spans.forEach((span) => {
            const svg = span.querySelector('svg');
            if (span.childNodes[0]) {
                span.childNodes[0].textContent = submitting ? loadingText : defaultText;
            }
            if (svg) {
                svg.style.opacity = submitting ? '0.45' : '1';
            }
        });
    };

    form.addEventListener('submit', (event) => {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        setSubmittingState(true);

        if (!recaptchaSiteKey || typeof grecaptcha === 'undefined') {
            setSubmittingState(false);
            window.alert(recaptchaError);
            return;
        }

        grecaptcha.ready(() => {
            grecaptcha.execute(recaptchaSiteKey, { action: 'contact' }).then((token) => {
                document.getElementById('recaptcha_token').value = token;
                form.submit();
            }).catch(() => {
                setSubmittingState(false);
                window.alert(recaptchaError);
            });
        });
    });
})();
</script>
<?= $this->endSection() ?>
