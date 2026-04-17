<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>

<div class="contact-page pt-100 mb-100">
    <div class="container">
        <!-- Banner -->
        <?= $this->include('sections/company-info') ?>
        <?= $this->include('sections/contact-form') ?>
    </div>
</div>
<div class="contact-map-section">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.1025878711866!2d106.68068027586887!3d10.803454358692889!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f186f084a0d%3A0xe0b586169a7017dd!2sTravel%20Plus%20Co.%2C%20Ltd!5e0!3m2!1sen!2s!4v1771928131280!5m2!1sen!2s"
        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>
<script>
document.getElementById("contactForm").addEventListener("submit", function(e) {
    e.preventDefault();

    grecaptcha.ready(function() {
        grecaptcha.execute('6LfgBncsAAAAAEmWNoT1xtCidf_t3tQEK7YkhWvw', {action: 'contact'}).then(function(token) {


            document.getElementById("recaptcha_token").value = token;

            e.target.submit();
        });
    });
});
</script>
<?= $this->endSection() ?>