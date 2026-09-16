<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$locale = 'en';
$inboundUrl = \App\Data\LocalizedPathCatalog::url('inbound', $locale);
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$privacyUrl = \App\Data\LocalizedPathCatalog::url('legal.privacy', $locale);
$termsUrl = \App\Data\LocalizedPathCatalog::url('legal.terms', $locale);
$recaptchaSiteKey = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'");
$websiteSettings = new \App\Services\WebsiteSettingsService();
$phone = $websiteSettings->get('hotline_e164');
$phoneDisplay = $websiteSettings->phoneDisplay($locale);
$email = $websiteSettings->get('email');
$tourCount = (int) (($pagination ?? [])['total'] ?? count($tours ?? []));
$destinations = [
    [
        'name' => 'Northern Vietnam',
        'copy' => 'Hanoi, Ha Long Bay, Ninh Binh and the mountain landscapes of Sapa.',
        'image' => 'assets/images/destination/ha-noi.webp',
        'url' => $inboundUrl . '/northern-vietnam',
        'class' => 'inbound-destination--wide',
    ],
    [
        'name' => 'Central Vietnam',
        'copy' => 'Heritage towns, coastal escapes and the flavours of Hue and Hoi An.',
        'image' => 'assets/images/destination/da-nang.jpg',
        'url' => $inboundUrl . '/central-vietnam',
        'class' => '',
    ],
    [
        'name' => 'Southern Vietnam',
        'copy' => 'Ho Chi Minh City, the Mekong Delta and island time in the south.',
        'image' => 'assets/images/tp-ho-chi-minh.webp',
        'url' => $inboundUrl . '/southern-vietnam',
        'class' => '',
    ],
    [
        'name' => 'Cambodia & Laos',
        'copy' => 'Ancient temples, slow river journeys and cultural encounters across Indochina.',
        'image' => 'assets/images/landing/inbound/cambodia-laos-journey.webp',
        'url' => '#plan-my-trip',
        'class' => 'inbound-destination--wide',
    ],
];
$travelStyles = [
    ['bi-compass', 'Classic discovery', 'A balanced first journey with essential sights, local food and time to explore.'],
    ['bi-people', 'Private journeys', 'A flexible itinerary, private transport and a guide focused on your travel party.'],
    ['bi-gem', 'Luxury escapes', 'Carefully chosen stays, refined dining and seamless door-to-door arrangements.'],
    ['bi-tree', 'Nature & culture', 'National parks, villages and heritage experiences with a slower, deeper rhythm.'],
];
$faqs = [
    ['Can Travel Plus design a private itinerary?', 'Yes. Share your dates, interests, group size and preferred comfort level. Our local team will shape a route around your priorities.'],
    ['What is included in my trip?', 'Inclusions vary by itinerary. Your consultant will confirm accommodation, transport, guided experiences and any optional extras in your proposal before you book.'],
    ['Can I combine Vietnam with Cambodia or Laos?', 'Yes. We can connect Vietnam with Cambodia, Laos and selected nearby destinations in one coordinated itinerary.'],
    ['Do you support international travellers during the trip?', 'Yes. Travel Plus provides English-language consultation and coordinates local guides, transport and on-trip support for your confirmed itinerary.'],
];
?>

<main class="inbound-landing">
    <section class="inbound-hero" aria-labelledby="inbound-title">
        <div class="container inbound-hero__container">
            <div class="inbound-hero__copy">
                <span class="inbound-eyebrow">Vietnam &amp; Indochina, through local eyes</span>
                <h1 id="inbound-title">Discover Vietnam.<br><em>Go beyond.</em></h1>
                <p>Explore Vietnam, Cambodia and Laos with a journey shaped around your interests. From mountain villages to heritage towns and island escapes, our local team helps bring it together.</p>
                <div class="inbound-hero__actions">
                    <a class="inbound-btn inbound-btn--primary" href="#plan-my-trip">Plan my trip <i class="bi bi-arrow-up-right"></i></a>
                    <a class="inbound-btn inbound-btn--glass" href="#inbound-tours">Explore tours <i class="bi bi-arrow-down"></i></a>
                </div>
                <ul class="inbound-hero__assurances" aria-label="Travel Plus service highlights">
                    <li><i class="bi bi-chat-square-text"></i><span><strong>English-speaking team</strong><small>Clear advice before you travel</small></span></li>
                    <li><i class="bi bi-sliders"></i><span><strong>Flexible itineraries</strong><small>Private and small-group options</small></span></li>
                    <li><i class="bi bi-headset"></i><span><strong>Local support</strong><small>Help throughout your journey</small></span></li>
                </ul>
            </div>
            <div class="inbound-hero__visual">
                <figure class="inbound-hero__main-photo"><img src="<?= esc(base_url('assets/images/destination/sa-pa.webp'), 'attr') ?>" alt="Mountain landscapes of Sapa, northern Vietnam" width="960" height="1100" fetchpriority="high"><figcaption>01 / NORTHERN VIETNAM</figcaption></figure>
                <figure class="inbound-hero__inset-photo"><img src="<?= esc(base_url('assets/images/gallery-3.jpg'), 'attr') ?>" alt="A traveller exploring the lantern-filled streets of Hoi An" width="385" height="405" loading="eager"><figcaption>Little moments.<br>Lasting memories.</figcaption></figure>
                <span class="inbound-hero__seal">LOCAL ROOTS<br><i class="bi bi-flower1"></i><br>PERSONAL JOURNEYS</span>
            </div>
        </div>
    </section>

    <div class="inbound-ticker" aria-label="Destinations"><span>VIETNAM</span><i>✳</i><span>CAMBODIA</span><i>✳</i><span>LAOS</span><i>✳</i><span>YOUR WAY</span><i>✳</i></div>

    <?= view('inbound/vietnam-first-glimpse') ?>

    <?php if (! empty($featuredInboundTours)): ?>
        <?= view('inbound/featured-tours', ['featuredInboundTours' => $featuredInboundTours]) ?>
    <?php endif; ?>

    <section class="inbound-intro" aria-label="Why travel with Travel Plus">
        <div class="container">
            <div class="inbound-intro__grid">
                <div class="inbound-intro__lead">
                    <span class="inbound-section-kicker">Designed around you</span>
                    <h2>Your interests. Your pace. Our local insight.</h2>
                </div>
                <p>Choose a ready-made journey or let us adapt the pace, hotels and experiences. One team brings the details together, from your first ideas to your time on the road.</p>
                <div class="inbound-intro__facts">
                    <div><strong><?= $tourCount > 0 ? esc((string) $tourCount) : 'Curated' ?></strong><span><?= $tourCount > 0 ? 'journeys available' : 'journeys and routes' ?></span></div>
                    <div><strong>1 local team</strong><span>from planning to on-trip support</span></div>
                </div>
            </div>
        </div>
    </section>

    <section class="inbound-section inbound-destinations" id="destinations" aria-labelledby="inbound-destinations-title">
        <div class="container">
            <div class="inbound-section-head">
                <div><span class="inbound-section-kicker">Choose your direction</span><h2 id="inbound-destinations-title">Where would you like to go?</h2></div>
                <p>Begin in one region or connect several destinations into a longer Indochina itinerary.</p>
            </div>
            <div class="inbound-destinations__grid">
                <?php foreach ($destinations as $destination): ?>
                    <a class="inbound-destination <?= esc($destination['class'], 'attr') ?>" href="<?= esc($destination['url'], 'attr') ?>">
                        <img src="<?= esc(base_url($destination['image']), 'attr') ?>" alt="<?= esc($destination['name'], 'attr') ?>" width="760" height="520" loading="lazy" decoding="async">
                        <span class="inbound-destination__shade" aria-hidden="true"></span>
                        <span class="inbound-destination__content">
                            <small><?= $destination['url'] === '#plan-my-trip' ? 'Plan a combined journey' : 'Explore the region' ?></small>
                            <strong><?= esc($destination['name']) ?></strong>
                            <em><?= esc($destination['copy']) ?></em>
                            <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="inbound-tours" id="inbound-tours" aria-labelledby="inbound-tours-title">
        <div class="container">
            <div class="inbound-section-head">
                <div><span class="inbound-section-kicker">Ready to explore</span><h2 id="inbound-tours-title">Find your Vietnam &amp; Indochina tour</h2></div>
                <p>Find a route you love, explore the itinerary, or ask us to design a private journey.</p>
            </div>
        </div>
        <?= $this->include('sections/tour-list-filter') ?>
        <?php if ($tourCount > 0): ?>
            <?= view('sections/tour-list-show', [
                'tours' => $tours ?? [],
                'pagination' => $pagination ?? [],
                'showTopArea' => true,
            ]) ?>
        <?php else: ?>
            <div class="container">
                <div class="inbound-tours__empty">
                    <span><i class="bi bi-map"></i></span>
                    <div><h3>No tours to show here yet</h3><p>Try another destination or share your plans with us for a personalised itinerary.</p></div>
                    <a class="inbound-btn inbound-btn--primary" href="#plan-my-trip">Plan a private trip <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <?= view('inbound/vietnam-photo-essay') ?>

    <section class="inbound-section inbound-styles" aria-labelledby="inbound-styles-title">
        <div class="container">
            <div class="inbound-section-head inbound-section-head--center">
                <div><span class="inbound-section-kicker">Travel your way</span><h2 id="inbound-styles-title">What is your travel style?</h2></div>
                <p>Mix discovery, comfort and local experiences to suit your trip.</p>
            </div>
            <div class="inbound-styles__grid">
                <?php foreach ($travelStyles as $style): ?>
                    <article class="inbound-style-card">
                        <i class="bi <?= esc($style[0], 'attr') ?>" aria-hidden="true"></i>
                        <h3><?= esc($style[1]) ?></h3>
                        <p><?= esc($style[2]) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?= view('inbound/vietnam-culture') ?>

    <?= view('inbound/vietnam-nature') ?>

    <section class="inbound-process" id="how-it-works" aria-labelledby="inbound-process-title">
        <div class="container inbound-process__grid">
            <div class="inbound-process__copy">
                <span class="inbound-section-kicker">Simple, clear planning</span>
                <h2 id="inbound-process-title">Your trip, in four simple steps</h2>
                <p>Share your ideas. Review your itinerary and inclusions. Confirm when the details feel right.</p>
                <a href="#plan-my-trip">Plan my trip <i class="bi bi-arrow-right"></i></a>
            </div>
            <ol class="inbound-process__steps">
                <li><span>01</span><div><h3>Share your plans</h3><p>Tell us your dates, group size, interests and preferred travel pace.</p></div></li>
                <li><span>02</span><div><h3>Shape the itinerary</h3><p>Our local team proposes a practical route and adjusts it with you.</p></div></li>
                <li><span>03</span><div><h3>Confirm the details</h3><p>Review your itinerary, inclusions and payment details before booking.</p></div></li>
                <li><span>04</span><div><h3>Travel with support</h3><p>We coordinate your confirmed services and remain available during the trip.</p></div></li>
            </ol>
        </div>
    </section>

    <?= view('inbound/vietnam-route-ideas') ?>

    <section class="inbound-plan" id="plan-my-trip" aria-labelledby="inbound-plan-title">
        <div class="container">
            <div class="inbound-plan__shell">
                <div class="inbound-plan__aside">
                    <span class="inbound-section-kicker">Tailor-made travel</span>
                    <h2 id="inbound-plan-title">Let’s plan your journey</h2>
                    <p>A short brief is enough to begin. A Travel Plus consultant will review it and contact you with the next steps.</p>
                    <ul>
                        <li><i class="bi bi-check2"></i> English-language consultation</li>
                        <li><i class="bi bi-check2"></i> Itinerary matched to your travel style</li>
                        <li><i class="bi bi-check2"></i> Clear inclusions before you book</li>
                    </ul>
                    <div class="inbound-plan__contacts">
                        <a href="tel:<?= esc($phone, 'attr') ?>"><i class="bi bi-telephone"></i><span><small>Call our local team</small><?= esc($phoneDisplay) ?></span></a>
                        <a href="mailto:<?= esc($email, 'attr') ?>"><i class="bi bi-envelope"></i><span><small>Email us</small><?= esc($email) ?></span></a>
                    </div>
                </div>
                <div class="inbound-plan__form-card travelplus-contact-form-card">
                    <?php $contactError = session()->getFlashdata('error'); ?>
                    <?php $contactSuccess = session()->getFlashdata('success'); ?>
                    <?php if ($contactError): ?><div class="alert alert-danger"><?= nl2br(esc((string) $contactError)) ?></div><?php endif; ?>
                    <?php if ($contactSuccess): ?><div class="alert alert-success"><?= esc((string) $contactSuccess) ?></div><?php endif; ?>
                    <div class="inbound-plan__form-head">
                        <span>Trip request</span>
                        <h3>Tell us about your plans</h3>
                        <p>No need for a finished itinerary. Fields marked * are required.</p>
                    </div>
                    <form
                        method="POST"
                        id="contactForm"
                        class="travelplus-contact-form inbound-enquiry-form"
                        action="<?= esc($contactUrl, 'attr') ?>"
                        data-phone-mode="international"
                        data-recaptcha-site-key="<?= esc($recaptchaSiteKey, 'attr') ?>"
                        data-recaptcha-error="We could not verify the form. Please try again."
                        data-name-error="Please enter your full name."
                        data-email-required="Please enter your email address."
                        data-email-invalid="Please enter a valid email address."
                        data-phone-required="Please enter your phone or WhatsApp number."
                        data-phone-invalid="Please include a valid phone number with country code."
                        data-message-error="Please share at least 10 characters about your trip."
                        data-privacy-error="Please agree to the Privacy Statement and Terms of Service."
                        novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="contact_form_token" value="<?= esc((string) ($contact_form_token ?? ''), 'attr') ?>">
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                        <input type="hidden" name="lead_context" value="inbound">
                        <input type="hidden" name="redirect_to" value="<?= esc(current_url() . '#plan-my-trip', 'attr') ?>">
                        <div class="inbound-enquiry-form__grid">
                            <label><span>Full name *</span><input type="text" name="name" value="<?= esc((string) old('name'), 'attr') ?>" autocomplete="name" placeholder="Your name" required></label>
                            <label><span>Email address *</span><input type="email" name="email" value="<?= esc((string) old('email'), 'attr') ?>" autocomplete="email" placeholder="you@example.com" required></label>
                            <label><span>Phone / WhatsApp *</span><input type="tel" name="phone" value="<?= esc((string) old('phone'), 'attr') ?>" autocomplete="tel" inputmode="tel" placeholder="+1 202 555 0147" required></label>
                            <label><span>Where would you like to go?</span><input type="text" name="destination" value="<?= esc((string) old('destination'), 'attr') ?>" placeholder="Vietnam, Cambodia, Laos..."></label>
                            <label><span>Travel period</span><input type="text" name="estimated_time" value="<?= esc((string) old('estimated_time'), 'attr') ?>" placeholder="March 2027"></label>
                            <label><span>Number of travellers</span><input type="text" name="travelers" value="<?= esc((string) old('travelers'), 'attr') ?>" placeholder="2 adults"></label>
                            <label class="inbound-enquiry-form__message"><span>What would make this trip special? *</span><textarea name="message" rows="4" placeholder="Tell us about your interests, preferred pace or must-see places." required><?= esc((string) old('message')) ?></textarea></label>
                        </div>
                        <label class="travelplus-contact-check inbound-enquiry-form__check" for="inboundPrivacyAgree">
                            <input type="checkbox" name="privacy_agree" value="1" id="inboundPrivacyAgree" <?= old('privacy_agree') ? 'checked' : '' ?> required>
                            <span>I agree to the <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">Privacy Statement</a> and <a href="<?= esc($termsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">Terms of Service</a>.</span>
                        </label>
                        <button type="submit" class="inbound-btn inbound-btn--primary inbound-enquiry-form__submit" id="contactSubmitBtn" data-default-text="Send my trip request" data-loading-text="Sending...">
                            <span data-contact-submit-label>Send my trip request</span><i class="bi bi-arrow-up-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="inbound-faq" aria-labelledby="inbound-faq-title">
        <div class="container inbound-faq__grid">
            <div class="inbound-faq__head"><span class="inbound-section-kicker">Good to know</span><h2 id="inbound-faq-title">Planning questions, answered</h2><p>Need something more specific? Send us your trip idea and our local team will help.</p></div>
            <div class="inbound-faq__list">
                <?php foreach ($faqs as $index => $faq): ?>
                    <details<?= $index === 0 ? ' open' : '' ?>><summary><?= esc($faq[0]) ?><i class="bi bi-plus-lg"></i></summary><p><?= esc($faq[1]) ?></p></details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script defer src="<?= esc(frontend_asset_url('assets/js/inbound-gallery.js'), 'attr') ?>"></script>
<script defer src="<?= esc(frontend_asset_url('assets/js/inbound-cursor.js'), 'attr') ?>"></script>
<?php if ($recaptchaSiteKey !== ''): ?>
<script defer src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey, 'url') ?>"></script>
<?php endif; ?>
<script type="module" src="<?= esc(frontend_asset_url('assets/js/contact-page.js'), 'attr') ?>"></script>
<?= $this->endSection() ?>
