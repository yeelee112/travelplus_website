<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('layouts/breadcrumb') ?>

<?php
$locale = service('request')->getLocale() ?: 'vi';
$contactUrl = \App\Data\LocalizedPathCatalog::url('contact', $locale);
$searchUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$privacyUrl = \App\Data\LocalizedPathCatalog::url('legal.privacy', $locale);
$termsUrl = \App\Data\LocalizedPathCatalog::url('legal.terms', $locale);
$offices = \App\Data\OfficeLocationCatalog::getAll($locale);
$repairText = static fn(string $value): string => \App\Services\TextEncodingService::repair($value);
$officeImages = [
    'assets/images/tp-ho-chi-minh.webp',
    'assets/images/destination/ha-noi.webp',
    'assets/images/destination/da-nang.jpg',
];
$labels = $locale === 'en'
    ? [
        'eyebrow' => 'Contact Travel Plus',
        'heroTitle' => 'Tell us what you need. We will shape the right travel plan.',
        'heroDesc' => 'Contact Travel Plus for tours, visa support, corporate MICE programs, incentive travel and tailor-made itineraries in Vietnam and abroad.',
        'hotlineLabel' => 'Hotline',
        'emailLabel' => 'Email',
        'responseLabel' => 'Consultation flow',
        'responseTitle' => 'What happens after you send the request?',
        'steps' => [
            ['Review request', 'Our team reviews your destination, timing, group size and service needs.'],
            ['Advise options', 'We suggest a suitable tour, visa checklist, MICE concept or custom itinerary.'],
            ['Confirm plan', 'You receive a clear plan, budget direction and next steps for booking.'],
        ],
        'formTitle' => 'Send a consultation request',
        'formDesc' => 'The more context you share, the more accurately Travel Plus can advise.',
        'officeTitle' => 'Travel Plus offices',
        'officeDesc' => 'Work with Travel Plus in Ho Chi Minh City, Hanoi or Da Nang for travel consultation, visa support and MICE operations.',
        'mapTitle' => 'Visit Travel Plus',
        'mapDesc' => 'Find our Ho Chi Minh City office on Google Maps.',
        'viewMap' => 'View map',
        'findTours' => 'Find tours',
        'seoTitle' => 'Contact Travel Plus for tours, visa and MICE services',
        'seoDesc' => 'Travel Plus supports individual travelers, families and businesses with outbound tours, domestic tours, visa consultation, MICE events, medical and pharmaceutical meetings, incentive travel and tailor-made itineraries.',
    ]
    : [
        'eyebrow' => 'Liên hệ Travel Plus',
        'heroTitle' => 'Gửi nhu cầu của bạn. Travel Plus sẽ tư vấn lịch trình phù hợp.',
        'heroDesc' => 'Liên hệ Travel Plus để được tư vấn tour, visa, chương trình MICE doanh nghiệp, incentive travel và lịch trình thiết kế riêng trong nước hoặc quốc tế.',
        'hotlineLabel' => 'Hotline',
        'emailLabel' => 'Email',
        'responseLabel' => 'Quy trình tư vấn',
        'responseTitle' => 'Sau khi gửi yêu cầu, Travel Plus xử lý thế nào?',
        'steps' => [
            ['Tiếp nhận nhu cầu', 'Đội ngũ Travel Plus xem điểm đến, thời gian, số lượng khách và dịch vụ bạn cần.'],
            ['Tư vấn phương án', 'Chúng tôi gợi ý tour, checklist visa, concept MICE hoặc lịch trình riêng phù hợp.'],
            ['Chốt kế hoạch', 'Bạn nhận định hướng lịch trình, ngân sách và các bước tiếp theo để đặt dịch vụ.'],
        ],
        'formTitle' => 'Gửi yêu cầu tư vấn',
        'formDesc' => 'Bạn mô tả càng rõ nhu cầu, Travel Plus càng tư vấn chính xác.',
        'officeTitle' => 'Văn phòng Travel Plus',
        'officeDesc' => 'Travel Plus hỗ trợ khách hàng tại TP.HCM, Hà Nội và Đà Nẵng cho tour, visa và vận hành MICE.',
        'mapTitle' => 'Ghé Travel Plus',
        'mapDesc' => 'Xem vị trí văn phòng Travel Plus TP.HCM trên Google Maps.',
        'viewMap' => 'Xem bản đồ',
        'findTours' => 'Tìm tour',
        'seoTitle' => 'Liên hệ Travel Plus để tư vấn tour, visa và MICE',
        'seoDesc' => 'Travel Plus hỗ trợ khách cá nhân, gia đình và doanh nghiệp với tour nước ngoài, tour trong nước, tư vấn visa, tổ chức MICE, hội nghị y dược, incentive travel và lịch trình thiết kế riêng.',
    ];
$labels += $locale === 'en'
    ? [
        'infoCtaKicker' => 'Tours, visa and MICE consultation',
        'infoCtaDesc' => 'Travel Plus receives your request, clarifies the travel objective and suggests a suitable plan for individuals, families or corporate groups.',
        'agreePrefix' => 'I agree to the',
        'agreeJoin' => 'and',
        'agreeSuffix' => 'of Travel Plus.',
        'privacyLabel' => 'Privacy Statement',
        'termsLabel' => 'Terms of Service',
    ]
    : [
        'infoCtaKicker' => 'Tư vấn tour, visa và MICE',
        'infoCtaDesc' => 'Travel Plus tiếp nhận nhu cầu, làm rõ mục tiêu chuyến đi và đề xuất phương án phù hợp cho khách cá nhân, gia đình hoặc doanh nghiệp.',
        'agreePrefix' => 'Tôi đồng ý với',
        'agreeJoin' => 'và',
        'agreeSuffix' => 'của Travel Plus.',
        'privacyLabel' => 'Chính sách bảo mật',
        'termsLabel' => 'Điều khoản sử dụng',
    ];
$recaptchaSiteKey = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'");
$phone = '+84795681568';
$phoneDisplay = '+84 79 568 1 568';
$email = 'info@travelplusvn.com';
$customTourFields = $locale === 'en'
    ? [
        'travelersLabel' => 'Group size',
        'travelersPlaceholder' => 'Example: 12 guests',
        'estimatedTimeLabel' => 'Preferred departure period',
        'estimatedTimePlaceholder' => 'Example: July 2026 or late summer',
        'tripLengthLabel' => 'Trip length',
        'tripLengthPlaceholder' => 'Example: 7 days 6 nights',
        'hotelRatingLabel' => 'Preferred hotel standard',
        'hotelOptions' => [
            '' => 'Select hotel standard',
            '3-star' => '3-star',
            '4-star' => '4-star',
            '5-star' => '5-star',
            'resort' => 'Resort / beachfront',
        ],
    ]
    : [
        'travelersLabel' => 'Số lượng khách đi',
        'travelersPlaceholder' => 'Ví dụ: 12 khách',
        'estimatedTimeLabel' => 'Thời gian dự kiến khởi hành',
        'estimatedTimePlaceholder' => 'Ví dụ: Tháng 7/2026 hoặc cuối hè',
        'tripLengthLabel' => 'Thời gian đi',
        'tripLengthPlaceholder' => 'Ví dụ: 7 ngày 6 đêm',
        'hotelRatingLabel' => 'Yêu cầu về khách sạn',
        'hotelOptions' => [
            '' => 'Chọn tiêu chuẩn khách sạn',
            '3-star' => '3 sao',
            '4-star' => '4 sao',
            '5-star' => '5 sao',
            'resort' => 'Resort / sát biển',
        ],
    ];
?>

<section class="travelplus-contact-hero" aria-labelledby="contact-page-title">
    <div class="container">
        <div class="travelplus-contact-hero-grid">
            <div class="travelplus-contact-hero-copy">
                <span><?= esc($labels['eyebrow']) ?></span>
                <h1 id="contact-page-title"><?= esc($labels['heroTitle']) ?></h1>
                <p><?= esc($labels['heroDesc']) ?></p>
                <div class="travelplus-contact-hero-actions">
                    <a href="tel:<?= esc($phone, 'attr') ?>"><i class="bi bi-telephone-fill"></i><?= esc($phoneDisplay) ?></a>
                    <a href="mailto:<?= esc($email, 'attr') ?>"><i class="bi bi-envelope-fill"></i><?= esc($email) ?></a>
                </div>
            </div>
            <div class="travelplus-contact-quick-panel" aria-label="<?= esc($labels['responseLabel'], 'attr') ?>">
                <span><?= esc($labels['responseLabel']) ?></span>
                <h2><?= esc($labels['responseTitle']) ?></h2>
                <ol>
                    <?php foreach ($labels['steps'] as $index => $step): ?>
                        <li>
                            <strong><?= esc((string) ($index + 1)) ?></strong>
                            <span>
                                <b><?= esc($step[0]) ?></b>
                                <?= esc($step[1]) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>
</section>

<section class="travelplus-contact-main">
    <div class="container">
        <div class="travelplus-contact-layout">
            <aside class="travelplus-contact-info" aria-label="Travel Plus contact information">
                <div class="travelplus-contact-info-card">
                    <i class="bi bi-telephone-fill"></i>
                    <span><?= esc($labels['hotlineLabel']) ?></span>
                    <a href="tel:<?= esc($phone, 'attr') ?>"><?= esc($phoneDisplay) ?></a>
                </div>
                <div class="travelplus-contact-info-card">
                    <i class="bi bi-envelope-fill"></i>
                    <span><?= esc($labels['emailLabel']) ?></span>
                    <a href="mailto:<?= esc($email, 'attr') ?>"><?= esc($email) ?></a>
                </div>
                <div class="travelplus-contact-info-card travelplus-contact-info-cta">
                    <strong>Travel Plus</strong>
                    <span><?= esc($labels['infoCtaKicker']) ?></span>
                    <p><?= esc($labels['infoCtaDesc']) ?></p>
                    <a href="<?= esc($searchUrl, 'attr') ?>"><?= esc($labels['findTours']) ?></a>
                </div>
            </aside>

            <div class="travelplus-contact-form-card">
                <div class="travelplus-contact-form-head">
                    <span><?= esc($labels['eyebrow']) ?></span>
                    <h2><?= esc($labels['formTitle']) ?></h2>
                    <p><?= esc($labels['formDesc']) ?></p>
                </div>

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

                <form
                    method="POST"
                    id="contactForm"
                    class="travelplus-contact-form"
                    action="<?= esc($contactUrl, 'attr') ?>"
                    data-recaptcha-site-key="<?= esc($recaptchaSiteKey, 'attr') ?>"
                    data-recaptcha-error="<?= esc(lang('Frontend.contact.recaptchaFailed', [], $locale), 'attr') ?>"
                    data-name-error="<?= esc($locale === 'en' ? 'Please enter your full name.' : 'Vui lòng nhập họ và tên.', 'attr') ?>"
                    data-email-required="<?= esc($locale === 'en' ? 'Please enter your email address.' : 'Vui lòng nhập email.', 'attr') ?>"
                    data-email-invalid="<?= esc($locale === 'en' ? 'Please enter a valid email address.' : 'Vui lòng nhập email hợp lệ.', 'attr') ?>"
                    data-phone-required="<?= esc($locale === 'en' ? 'Please enter your phone number.' : 'Vui lòng nhập số điện thoại.', 'attr') ?>"
                    data-phone-invalid="<?= esc($locale === 'en' ? 'Please enter a valid Vietnamese phone number.' : 'Vui lòng nhập số điện thoại Việt Nam hợp lệ.', 'attr') ?>"
                    data-message-error="<?= esc($locale === 'en' ? 'Your message must be at least 10 characters.' : 'Nội dung tối thiểu 10 ký tự.', 'attr') ?>"
                    data-privacy-error="<?= esc($locale === 'en' ? 'Please agree to the privacy statement and terms of service.' : 'Vui lòng đồng ý với điều khoản.', 'attr') ?>"
                    novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="contact_form_token" value="<?= esc((string) ($contact_form_token ?? '')) ?>">
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                    <div class="travelplus-contact-form-grid">
                        <label>
                            <span><?= esc(lang('Frontend.contact.name', [], $locale)) ?></span>
                            <input type="text" name="name" value="<?= esc((string) old('name'), 'attr') ?>" placeholder="<?= esc(lang('Frontend.contact.namePlaceholder', [], $locale), 'attr') ?>" autocomplete="name" required>
                        </label>
                        <label>
                            <span><?= esc(lang('Frontend.contact.email', [], $locale)) ?></span>
                            <input type="email" name="email" value="<?= esc((string) old('email'), 'attr') ?>" placeholder="email@domain.com" autocomplete="email" required>
                        </label>
                        <label>
                            <span><?= esc(lang('Frontend.contact.phone', [], $locale)) ?></span>
                            <input type="tel" name="phone" value="<?= esc((string) old('phone'), 'attr') ?>" placeholder="+84..." autocomplete="tel" required>
                        </label>
                        <label>
                            <span><?= esc(lang('Frontend.contact.destination', [], $locale)) ?></span>
                            <input type="text" name="destination" value="<?= esc((string) old('destination'), 'attr') ?>" placeholder="<?= esc(lang('Frontend.contact.destinationPlaceholder', [], $locale), 'attr') ?>">
                        </label>
                        <label>
                            <span><?= esc($customTourFields['travelersLabel']) ?></span>
                            <input type="text" name="travelers" value="<?= esc((string) old('travelers'), 'attr') ?>" placeholder="<?= esc($customTourFields['travelersPlaceholder'], 'attr') ?>">
                        </label>
                        <label>
                            <span><?= esc($customTourFields['estimatedTimeLabel']) ?></span>
                            <input type="text" name="estimated_time" value="<?= esc((string) old('estimated_time'), 'attr') ?>" placeholder="<?= esc($customTourFields['estimatedTimePlaceholder'], 'attr') ?>">
                        </label>
                        <label>
                            <span><?= esc($customTourFields['tripLengthLabel']) ?></span>
                            <input type="text" name="trip_length" value="<?= esc((string) old('trip_length'), 'attr') ?>" placeholder="<?= esc($customTourFields['tripLengthPlaceholder'], 'attr') ?>">
                        </label>
                        <label>
                            <span><?= esc($customTourFields['hotelRatingLabel']) ?></span>
                            <select name="hotel_rating">
                                <?php foreach ($customTourFields['hotelOptions'] as $optionValue => $optionLabel): ?>
                                    <option value="<?= esc((string) $optionValue, 'attr') ?>" <?= old('hotel_rating') === (string) $optionValue ? 'selected' : '' ?>>
                                        <?= esc((string) $optionLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="travelplus-contact-form-message">
                            <span><?= esc(lang('Frontend.contact.message', [], $locale)) ?></span>
                            <textarea name="message" rows="6" placeholder="<?= esc(lang('Frontend.contact.messagePlaceholder', [], $locale), 'attr') ?>" required><?= esc((string) old('message')) ?></textarea>
                        </label>
                    </div>

                    <label class="travelplus-contact-check" for="contactPrivacyAgree">
                        <input type="checkbox" name="privacy_agree" value="1" id="contactPrivacyAgree" <?= old('privacy_agree') ? 'checked' : '' ?> required>
                        <span>
                            <?= esc($labels['agreePrefix']) ?>
                            <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($labels['privacyLabel']) ?></a>
                            <?= esc($labels['agreeJoin']) ?>
                            <a href="<?= esc($termsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($labels['termsLabel']) ?></a>
                            <?= esc($labels['agreeSuffix']) ?>
                        </span>
                    </label>

                    <button
                        type="submit"
                        class="travelplus-contact-submit"
                        id="contactSubmitBtn"
                        data-default-text="<?= esc(lang('Frontend.contact.submit', [], $locale), 'attr') ?>"
                        data-loading-text="<?= esc(lang('Frontend.contact.submitting', [], $locale), 'attr') ?>">
                        <span data-contact-submit-label><?= esc(lang('Frontend.contact.submit', [], $locale)) ?></span>
                        <i class="bi bi-arrow-up-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="travelplus-contact-offices" aria-labelledby="contact-offices-title">
    <div class="container">
        <div class="travelplus-contact-section-head">
            <span><?= esc($labels['eyebrow']) ?></span>
            <h2 id="contact-offices-title"><?= esc($labels['officeTitle']) ?></h2>
            <p><?= esc($labels['officeDesc']) ?></p>
        </div>
        <div class="travelplus-contact-office-grid">
            <?php foreach ($offices as $index => $office): ?>
                <?php
                    $officeTitle = $repairText((string) $office['title']);
                    $officeImage = $officeImages[$index] ?? $officeImages[0];
                ?>
                <article class="travelplus-contact-office-card">
                    <div class="travelplus-contact-office-media">
                        <img
                            src="<?= esc(base_url($officeImage), 'attr') ?>"
                            alt="<?= esc($officeTitle, 'attr') ?>"
                            width="420"
                            height="180"
                            loading="lazy"
                            decoding="async">
                    </div>
                    <div class="travelplus-contact-office-body">
                        <h3><?= esc($officeTitle) ?></h3>
                        <p><?= esc($repairText((string) $office['address'])) ?></p>
                        <a href="<?= esc((string) $office['map_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer">
                            <?= esc($labels['viewMap']) ?>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="travelplus-contact-seo" aria-labelledby="contact-seo-title">
    <div class="container">
        <div class="travelplus-contact-seo-card">
            <div>
                <span>Travel Plus</span>
                <h2 id="contact-seo-title"><?= esc($labels['seoTitle']) ?></h2>
                <p><?= esc($labels['seoDesc']) ?></p>
            </div>
            <a href="<?= esc($contactUrl, 'attr') ?>#contactForm"><?= esc(lang('Frontend.contact.submit', [], $locale)) ?></a>
        </div>
    </div>
</section>

<section class="travelplus-contact-map" aria-labelledby="contact-map-title">
    <div class="container">
        <div class="travelplus-contact-map-head">
            <h2 id="contact-map-title"><?= esc($labels['mapTitle']) ?></h2>
            <p><?= esc($labels['mapDesc']) ?></p>
        </div>
    </div>
    <iframe
        title="Travel Plus Google Map"
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.1025878711866!2d106.68068027586887!3d10.803454358692889!2m3!1f0!2f0!3f0!2m3!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f186f084a0d%3A0xe0b586169a7017dd!2sTravel%20Plus%20Co.%2C%20Ltd!5e0!3m2!1sen!2s!4v1771928131280!5m2!1sen!2s"
        width="600"
        height="450"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($recaptchaSiteKey !== ''): ?>
<script defer src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey, 'url') ?>"></script>
<?php endif; ?>
<script type="module" src="<?= esc(frontend_asset_url('assets/js/contact-page.js'), 'attr') ?>"></script>
<?= $this->endSection() ?>
