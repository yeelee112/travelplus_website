<?php
$locale = service('request')->getLocale() ?: 'vi';
$websiteSettings = new \App\Services\WebsiteSettingsService();
$contactPhone = $websiteSettings->get('hotline_e164');
$contactPhoneDisplay = $websiteSettings->phoneDisplay($locale);
$contentLocale = $locale ?? (service('request')->getLocale() === 'en' ? 'en' : 'vi');
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $contentLocale);
$outboundUrl = \App\Data\LocalizedPathCatalog::url('outbound', $contentLocale);
$domesticUrl = \App\Data\LocalizedPathCatalog::url('domestic', $contentLocale);
$serviceUrls = [
    'visa' => \App\Data\LocalizedPathCatalog::url('service.visa', $contentLocale),
    'airlineTickets' => \App\Data\LocalizedPathCatalog::url('service.airlineTickets', $contentLocale),
    'hotels' => \App\Data\LocalizedPathCatalog::url('service.hotels', $contentLocale),
    'transport' => \App\Data\LocalizedPathCatalog::url('service.transport', $contentLocale),
    'domestic' => $domesticUrl,
    'outbound' => $outboundUrl,
];
$profileUrl = base_url('assets/docs/travelplus-company-profile.pdf');
$privacyUrl = \App\Data\LocalizedPathCatalog::url('legal.privacy', $contentLocale);
$termsUrl = \App\Data\LocalizedPathCatalog::url('legal.terms', $contentLocale);
$recaptchaSiteKey = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'");
$contactFormToken = trim((string) ($contact_form_token ?? ''));
$c = is_array($content ?? null) ? $content : [];
$briefForm = $locale === 'en'
    ? [
        'companyLabel' => 'Company',
        'companyPlaceholder' => 'Company name',
        'nameLabel' => 'Contact person',
        'namePlaceholder' => 'Full name',
        'phoneLabel' => 'Phone number',
        'phonePlaceholder' => '+84...',
        'emailLabel' => 'Work email',
        'emailPlaceholder' => 'name@company.com',
        'eventTypeLabel' => 'Program type',
        'eventTypes' => ['Medical Congress', 'Medical Meeting', 'Sales Conference', 'Customer Conference', 'Incentive Tour', 'Company Trip', 'Team Building', 'Gala Dinner', 'Kick Off', 'Other MICE program'],
        'conferenceNameLabel' => 'Conference / meeting name',
        'conferenceNamePlaceholder' => 'Example: APAC Medical Congress 2026',
        'guestsLabel' => 'Guest count',
        'guestsPlaceholder' => 'Example: 120 guests',
        'destinationLabel' => 'Preferred destination',
        'destinationPlaceholder' => 'Vietnam, Thailand, Singapore...',
        'timeLabel' => 'Expected timing',
        'timePlaceholder' => 'Example: Q4/2026',
        'budgetLabel' => 'Reference budget',
        'budgetPlaceholder' => 'Example: 1.5 billion VND',
        'messageLabel' => 'Brief / objective',
        'messagePlaceholder' => 'Share objective, attendee profile, must-have items, venue or proposal deadline...',
        'privacyPrefix' => 'I agree to the',
        'privacyJoin' => 'and',
        'privacySuffix' => 'of Travel Plus.',
        'submit' => 'Get proposal in 24 hours',
        'loading' => 'Sending...',
    ]
    : [
        'companyLabel' => 'Tên công ty',
        'companyPlaceholder' => 'Công ty / thương hiệu',
        'nameLabel' => 'Người phụ trách',
        'namePlaceholder' => 'Họ và tên',
        'phoneLabel' => 'Số điện thoại',
        'phonePlaceholder' => '+84...',
        'emailLabel' => 'Email công ty',
        'emailPlaceholder' => 'ten@congty.com',
        'eventTypeLabel' => 'Loại chương trình',
        'eventTypes' => ['Hội nghị y khoa', 'Họp chuyên đề y khoa', 'Hội nghị kinh doanh', 'Hội nghị khách hàng', 'Du lịch khen thưởng', 'Company Trip', 'Team building', 'Tiệc gala', 'Kick-off', 'Chương trình MICE khác'],
        'conferenceNameLabel' => 'Tên hội nghị/hội thảo',
        'conferenceNamePlaceholder' => 'Ví dụ: Hội nghị khách hàng 2026',
        'guestsLabel' => 'Số lượng khách',
        'guestsPlaceholder' => 'Ví dụ: 120 khách',
        'destinationLabel' => 'Điểm đến mong muốn',
        'destinationPlaceholder' => 'Việt Nam, Thái Lan, Singapore...',
        'timeLabel' => 'Thời gian dự kiến',
        'timePlaceholder' => 'Ví dụ: Quý 4/2026',
        'budgetLabel' => 'Ngân sách tham khảo',
        'budgetPlaceholder' => 'Ví dụ: 1,5 tỷ VNĐ',
        'messageLabel' => 'Brief / mục tiêu chương trình',
        'messagePlaceholder' => 'Mục tiêu, nhóm khách, hạng mục cần có, địa điểm mong muốn hoặc deadline nhận proposal...',
        'privacyPrefix' => 'Tôi đồng ý với',
        'privacyJoin' => 'và',
        'privacySuffix' => 'của Travel Plus.',
        'submit' => 'Gửi brief để được tư vấn',
        'loading' => 'Đang gửi...',
    ];
$briefOptionalTitle = $locale === 'en' ? 'Additional program details' : 'Thông tin chương trình bổ sung';
$briefOptionalDesc = $locale === 'en'
    ? 'Conference name, guest count, destination, timing, budget and objectives'
    : 'Tên chương trình, số khách, điểm đến, thời gian, ngân sách và mục tiêu';
$briefOptionalOpen = false;
foreach (['conference_name', 'travelers', 'destination', 'estimated_time', 'budget', 'message'] as $optionalField) {
    if (trim((string) old($optionalField)) !== '') {
        $briefOptionalOpen = true;
        break;
    }
}
?>

<div class="mice-page">
    <section class="mice-page__hero">
        <div class="container">
            <div class="mice-page__hero-layout">
                <div class="mice-page__hero-content">
                    <span class="mice-page__eyebrow"><?= esc($c['hero_eyebrow'] ?? '') ?></span>
                    <h1><?= esc($c['hero_title'] ?? '') ?></h1>
                    <p><?= esc($c['hero_desc'] ?? '') ?></p>
                    <div class="mice-page__hero-actions">
                        <a class="primary-btn1 two" href="#mice-brief-request">
                            <span><?= esc($c['hero_cta_primary'] ?? '') ?></span>
                            <span><?= esc($c['hero_cta_primary'] ?? '') ?></span>
                        </a>
                        <a class="primary-btn1 two transparent" href="<?= esc($profileUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">
                            <span><?= esc($c['hero_cta_secondary'] ?? '') ?></span>
                            <span><?= esc($c['hero_cta_secondary'] ?? '') ?></span>
                        </a>
                    </div>
                    <?php if (! empty($c['hero_note'])): ?>
                        <p class="mice-page__hero-note"><?= esc($c['hero_note']) ?></p>
                    <?php endif; ?>
                    <div class="mice-page__hero-metrics">
                        <?php foreach (($c['metrics'] ?? []) as $metric): ?>
                            <div class="mice-page__metric-card">
                                <strong><?= esc($metric['title']) ?></strong>
                                <span><?= esc($metric['text']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mice-page__hero-media">
                    <div class="mice-page__hero-main-media">
                        <img src="<?= base_url('assets/images/mice-1.webp') ?>" alt="<?= esc(lang('Frontend.common.alt.travelPlusMice', [], $locale)) ?>" loading="eager" fetchpriority="high" decoding="async" width="680" height="520">
                    </div>
                    <div class="mice-page__hero-side-grid">
                        <div class="mice-page__hero-side-card">
                            <img src="<?= base_url('assets/images/mice-2.webp') ?>" alt="<?= esc(lang('Frontend.common.alt.corporateConference', [], $locale)) ?>" loading="lazy" fetchpriority="low" decoding="async" width="320" height="240">
                        </div>
                        <div class="mice-page__hero-side-card">
                            <img src="<?= base_url('assets/images/mice-3.webp') ?>" alt="<?= esc(lang('Frontend.common.alt.teamBuildingProgram', [], $locale)) ?>" loading="lazy" fetchpriority="low" decoding="async" width="320" height="240">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mice-page__intro pt-100 pb-100">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <div class="section-title">
                        <span><?= esc($c['intro_eyebrow'] ?? '') ?></span>
                        <h2><?= esc($c['intro_title'] ?? '') ?></h2>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="mice-page__copy-card">
                        <p><?= esc($c['intro_p1'] ?? '') ?></p>
                        <p><?= esc($c['intro_p2'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (! empty($c['pain_items'])): ?>
        <section class="mice-page__pain pb-100">
            <div class="container">
                <div class="section-title text-center mb-60">
                    <span><?= esc($c['pain_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['pain_title'] ?? '') ?></h2>
                    <p><?= esc($c['pain_desc'] ?? '') ?></p>
                </div>
                <div class="mice-page__pain-grid">
                    <?php foreach (($c['pain_items'] ?? []) as $item): ?>
                        <article class="mice-page__pain-card">
                            <h3><?= esc($item['title'] ?? '') ?></h3>
                            <p><?= esc($item['text'] ?? '') ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="mice-page__services pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($c['services_eyebrow'] ?? '') ?></span>
                <h2><?= esc($c['services_title'] ?? '') ?></h2>
                <p><?= esc($c['services_desc'] ?? '') ?></p>
            </div>
            <div class="row g-4">
                <?php foreach (($c['service_cards'] ?? []) as $card): ?>
                    <div class="col-lg-3 col-md-6">
                        <article class="mice-page__service-card">
                            <h3><?= esc($card['title']) ?></h3>
                            <p><?= esc($card['text']) ?></p>
                            <ul>
                                <?php foreach (($card['bullets'] ?? []) as $bullet): ?>
                                    <li><?= esc($bullet) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mice-page__solutions pb-100">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="mice-page__solution-panel is-dark">
                        <span><?= esc($c['solution_eyebrow'] ?? '') ?></span>
                        <h2><?= esc($c['solution_title'] ?? '') ?></h2>
                        <p><?= esc($c['solution_text'] ?? '') ?></p>
                        <div class="mice-page__solution-links">
                            <a href="<?= esc($domesticUrl) ?>"><?= esc($c['solution_links']['domestic'] ?? '') ?></a>
                            <a href="<?= esc($outboundUrl) ?>"><?= esc($c['solution_links']['outbound'] ?? '') ?></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mice-page__solution-list">
                        <?php foreach (($c['solution_items'] ?? []) as $item): ?>
                            <article class="mice-page__solution-item">
                                <h3><?= esc($item['title']) ?></h3>
                                <p><?= esc($item['text']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mice-page__why pb-100">
        <div class="container">
            <div class="section-title text-center mb-60">
                <span><?= esc($c['why_eyebrow'] ?? '') ?></span>
                <h2><?= esc($c['why_title'] ?? '') ?></h2>
            </div>
            <div class="row g-4">
                <?php foreach (($c['why_items'] ?? []) as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="mice-page__why-card">
                            <h3><?= esc($item['title']) ?></h3>
                            <p><?= esc($item['text']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="mice-page__process pb-100">
        <div class="container">
            <div class="mice-page__process-layout">
                <div class="mice-page__process-overview">
                    <span><?= esc($c['process_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['process_title'] ?? '') ?></h2>
                    <p><?= esc($c['process_desc'] ?? '') ?></p>
                    <?php if (! empty($c['process_tags'])): ?>
                        <div class="mice-page__process-tags">
                            <?php foreach (($c['process_tags'] ?? []) as $tag): ?>
                                <small><?= esc($tag) ?></small>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mice-page__process-flow">
                    <?php foreach (($c['process'] ?? []) as $index => $step): ?>
                        <article class="mice-page__process-step">
                            <span class="mice-page__process-number"><?= esc(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                            <div class="mice-page__process-copy">
                                <h3><?= esc($step['title']) ?></h3>
                                <p><?= esc($step['text']) ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if (! empty($c['seo_title']) || ! empty($c['seo_paragraphs'])): ?>
    <section class="mice-page__seo pb-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    <div class="section-title text-center mb-40">
                        <h2><?= esc($c['seo_title'] ?? '') ?></h2>
                    </div>
                    <div class="mice-page__copy-card mice-page__seo-copy">
                        <?php foreach (($c['seo_paragraphs'] ?? []) as $paragraph): ?>
                            <p><?= esc($paragraph) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (! empty($c['internal_links'])): ?>
        <section class="mice-page__internal pb-100">
            <div class="container">
                <div class="section-title text-center mb-50">
                    <span><?= esc($c['internal_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['internal_title'] ?? '') ?></h2>
                    <p><?= esc($c['internal_desc'] ?? '') ?></p>
                </div>
                <div class="mice-page__internal-grid">
                    <?php foreach (($c['internal_links'] ?? []) as $link): ?>
                        <?php $href = $serviceUrls[$link['key'] ?? ''] ?? '#mice-brief-request'; ?>
                        <a class="mice-page__internal-card" href="<?= esc($href, 'attr') ?>">
                            <h3><?= esc($link['title'] ?? '') ?></h3>
                            <p><?= esc($link['text'] ?? '') ?></p>
                            <span><?= esc($locale === 'en' ? 'View service' : 'Xem dịch vụ') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="mice-page__profile pb-100">
        <div class="container">
            <div class="mice-page__profile-card">
                <div>
                    <span><?= esc($c['profile_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['profile_title'] ?? '') ?></h2>
                    <p><?= esc($c['profile_desc'] ?? '') ?></p>
                </div>
                <a class="mice-page__profile-button" href="<?= esc($profileUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer">
                    <?= esc($c['profile_button'] ?? '') ?>
                </a>
            </div>
        </div>
    </section>

    <?php if (! empty($c['faqs'])): ?>
        <section class="mice-page__faq pb-100">
            <div class="container">
                <div class="section-title text-center mb-50">
                    <span><?= esc($c['faq_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['faq_title'] ?? '') ?></h2>
                    <p><?= esc($c['faq_desc'] ?? '') ?></p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-xl-9 col-lg-10">
                        <div class="faq-wrap three mice-page__faq-wrap">
                            <div class="accordion accordion-flush" id="miceFaqAccordion">
                                <?php foreach (($c['faqs'] ?? []) as $index => $faq): ?>
                                    <?php $id = 'mice-faq-' . ($index + 1); ?>
                                    <div class="accordion-item">
                                        <h5 class="accordion-header" id="heading-<?= esc($id) ?>">
                                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($id) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= esc($id) ?>">
                                                <?= esc($faq['q'] ?? '') ?>
                                            </button>
                                        </h5>
                                        <div id="<?= esc($id) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading-<?= esc($id) ?>" data-bs-parent="#miceFaqAccordion">
                                            <div class="accordion-body"><?= esc($faq['a'] ?? '') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="mice-page__brief mb-100" id="mice-brief-request">
        <div class="container">
            <div class="mice-page__brief-card">
                <div class="mice-page__brief-copy">
                    <span><?= esc($c['brief_eyebrow'] ?? '') ?></span>
                    <h2><?= esc($c['brief_title'] ?? '') ?></h2>
                    <p><?= esc($c['brief_text'] ?? '') ?></p>
                    <div class="mice-page__brief-quick">
                        <a href="tel:<?= esc($contactPhone, 'attr') ?>"><?= esc($locale === 'en' ? 'Call now' : 'Gọi ngay') ?>: <?= esc($contactPhoneDisplay) ?></a>
                        <a href="<?= esc($profileUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($c['profile_button'] ?? '') ?></a>
                    </div>
                </div>
                <form
                    method="POST"
                    id="contactForm"
                    class="mice-page__brief-form travelplus-contact-form"
                    action="<?= esc($contactUrl, 'attr') ?>"
                    data-recaptcha-site-key="<?= esc($recaptchaSiteKey, 'attr') ?>"
                    data-recaptcha-error="<?= esc(lang('Frontend.contact.recaptchaFailed', [], $locale), 'attr') ?>"
                    data-name-error="<?= esc($locale === 'en' ? 'Please enter your full name.' : 'Vui lòng nhập họ và tên.', 'attr') ?>"
                    data-email-required="<?= esc($locale === 'en' ? 'Please enter your email address.' : 'Vui lòng nhập email.', 'attr') ?>"
                    data-email-invalid="<?= esc($locale === 'en' ? 'Please enter a valid email address.' : 'Vui lòng nhập email hợp lệ.', 'attr') ?>"
                    data-phone-required="<?= esc($locale === 'en' ? 'Please enter your phone number.' : 'Vui lòng nhập số điện thoại.', 'attr') ?>"
                    data-phone-invalid="<?= esc($locale === 'en' ? 'Please enter a valid Vietnamese phone number.' : 'Vui lòng nhập số điện thoại Việt Nam hợp lệ.', 'attr') ?>"
                    data-message-error="<?= esc($locale === 'en' ? 'Brief must be at least 10 characters when provided.' : 'Brief cần tối thiểu 10 ký tự nếu có nhập.', 'attr') ?>"
                    data-message-optional="true"
                    data-privacy-error="<?= esc($locale === 'en' ? 'Please agree to the privacy statement and terms of service.' : 'Vui lòng đồng ý với Chính sách bảo mật và Điều khoản sử dụng.', 'attr') ?>"
                    novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="contact_form_token" value="<?= esc($contactFormToken, 'attr') ?>">
                    <input type="hidden" name="redirect_to" value="<?= esc(current_url() . '#mice-brief-request', 'attr') ?>">
                    <input type="hidden" name="service_type" value="mice">
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                    <?php $contactError = session()->getFlashdata('error'); ?>
                    <?php if ($contactError): ?>
                        <div class="alert alert-danger">
                            <?= nl2br(esc((string) $contactError)) ?>
                        </div>
                    <?php endif; ?>

                    <?php $contactSuccess = session()->getFlashdata('success'); ?>
                    <?php if ($contactSuccess): ?>
                        <div class="alert alert-success">
                            <?= esc((string) $contactSuccess) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mice-page__brief-form-grid">
                        <label>
                            <span><?= esc($briefForm['companyLabel']) ?></span>
                            <input type="text" name="company_name" value="<?= esc((string) old('company_name'), 'attr') ?>" placeholder="<?= esc($briefForm['companyPlaceholder'], 'attr') ?>">
                        </label>
                        <label>
                            <span><?= esc($briefForm['nameLabel']) ?></span>
                            <input type="text" name="name" value="<?= esc((string) old('name'), 'attr') ?>" placeholder="<?= esc($briefForm['namePlaceholder'], 'attr') ?>" autocomplete="name" required>
                        </label>
                        <label>
                            <span><?= esc($briefForm['phoneLabel']) ?></span>
                            <input type="tel" name="phone" value="<?= esc((string) old('phone'), 'attr') ?>" placeholder="<?= esc($briefForm['phonePlaceholder'], 'attr') ?>" autocomplete="tel" required>
                        </label>
                        <label>
                            <span><?= esc($briefForm['emailLabel']) ?></span>
                            <input type="email" name="email" value="<?= esc((string) old('email'), 'attr') ?>" placeholder="<?= esc($briefForm['emailPlaceholder'], 'attr') ?>" autocomplete="email" required>
                        </label>
                        <label>
                            <span><?= esc($briefForm['eventTypeLabel']) ?></span>
                            <select name="event_type">
                                <?php foreach ($briefForm['eventTypes'] as $eventType): ?>
                                    <option value="<?= esc((string) $eventType, 'attr') ?>" <?= old('event_type') === (string) $eventType ? 'selected' : '' ?>>
                                        <?= esc((string) $eventType) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <details class="mice-page__brief-optional" <?= $briefOptionalOpen ? 'open' : '' ?>>
                        <summary>
                            <span><?= esc($briefOptionalTitle) ?></span>
                            <small><?= esc($briefOptionalDesc) ?></small>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <div class="mice-page__brief-optional-grid">
                            <label>
                            <span><?= esc($briefForm['conferenceNameLabel']) ?></span>
                            <input type="text" name="conference_name" value="<?= esc((string) old('conference_name'), 'attr') ?>" placeholder="<?= esc($briefForm['conferenceNamePlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                            <span><?= esc($briefForm['guestsLabel']) ?></span>
                            <input type="text" name="travelers" value="<?= esc((string) old('travelers'), 'attr') ?>" placeholder="<?= esc($briefForm['guestsPlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                            <span><?= esc($briefForm['destinationLabel']) ?></span>
                            <input type="text" name="destination" value="<?= esc((string) old('destination'), 'attr') ?>" placeholder="<?= esc($briefForm['destinationPlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                            <span><?= esc($briefForm['timeLabel']) ?></span>
                            <input type="text" name="estimated_time" value="<?= esc((string) old('estimated_time'), 'attr') ?>" placeholder="<?= esc($briefForm['timePlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                            <span><?= esc($briefForm['budgetLabel']) ?></span>
                            <input type="text" name="budget" value="<?= esc((string) old('budget'), 'attr') ?>" placeholder="<?= esc($briefForm['budgetPlaceholder'], 'attr') ?>">
                            </label>
                            <label class="mice-page__brief-message">
                            <span><?= esc($briefForm['messageLabel']) ?></span>
                            <textarea name="message" rows="4" placeholder="<?= esc($briefForm['messagePlaceholder'], 'attr') ?>"><?= esc((string) old('message')) ?></textarea>
                            </label>
                        </div>
                    </details>

                    <label class="mice-page__brief-check" for="miceBriefPrivacyAgree">
                        <input type="checkbox" name="privacy_agree" value="1" id="miceBriefPrivacyAgree" <?= old('privacy_agree') ? 'checked' : '' ?> required>
                        <span>
                            <?= esc($briefForm['privacyPrefix']) ?>
                            <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc(lang('Frontend.footer.link.privacy', [], $locale)) ?></a>
                            <?= esc($briefForm['privacyJoin']) ?>
                            <a href="<?= esc($termsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc(lang('Frontend.footer.link.terms', [], $locale)) ?></a>
                            <?= esc($briefForm['privacySuffix']) ?>
                        </span>
                    </label>

                    <button
                        type="submit"
                        class="primary-btn1 two mice-page__brief-submit"
                        id="contactSubmitBtn"
                        data-default-text="<?= esc($briefForm['submit'], 'attr') ?>"
                        data-loading-text="<?= esc($briefForm['loading'], 'attr') ?>">
                        <span data-contact-submit-label><?= esc($briefForm['submit']) ?><i class="bi bi-arrow-up-right"></i></span>
                        <span><?= esc($briefForm['submit']) ?><i class="bi bi-arrow-up-right"></i></span>
                    </button>
                </form>
            </div>
        </div>
    </section>
    <a class="mice-page__sticky-cta" href="#mice-brief-request">
        <span><?= esc($locale === 'en' ? 'Get proposal' : 'Gửi brief') ?></span>
        <i class="bi bi-arrow-up-right"></i>
    </a>
</div>
