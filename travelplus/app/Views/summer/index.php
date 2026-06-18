<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$copy = is_array($copy ?? null) ? $copy : [];
$heroLocations = is_array($heroLocations ?? null) ? $heroLocations : [];
$promoCollection = is_array($promoCollection ?? null) ? $promoCollection : [];
$featuredCollection = is_array($featuredCollection ?? null) ? $featuredCollection : [];
$domesticTours = is_array($domesticTours ?? null) ? $domesticTours : [];
$outboundTours = is_array($outboundTours ?? null) ? $outboundTours : [];
$primaryTour = is_array($primaryTour ?? null) ? $primaryTour : null;
$landingBannerImage = trim((string) ($landingBannerImage ?? ''));
$landingBackdropImage = trim((string) ($landingBackdropImage ?? ''));
$contactUrl = trim((string) ($contactUrl ?? ''));
$privacyUrl = trim((string) ($privacyUrl ?? ''));
$termsUrl = trim((string) ($termsUrl ?? ''));
$recaptchaSiteKey = trim((string) ($recaptchaSiteKey ?? ''));
$contactFormToken = trim((string) ($contact_form_token ?? ''));

$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$leadRedirectUrl = current_url() . '#summerLeadForm';
$summerShowcase = $promoCollection !== [] ? $promoCollection : $featuredCollection;
$summerShowcase = array_values($summerShowcase);
$heroCards = array_slice($summerShowcase, 0, 3);
$editorialPrimary = $heroCards[0] ?? $primaryTour;
$editorialSide = array_slice($summerShowcase, 1, 2);
$summerRailSource = $promoCollection !== [] ? $promoCollection : ($featuredCollection !== [] ? $featuredCollection : $summerShowcase);
$summerRail = array_slice($summerRailSource, 0, 6);
$summerFilterTours = array_slice($summerRailSource, 0, 9);
$flashSignals = array_values(array_filter((array) ($copy['signalBar'] ?? [])));
$dealTiles = array_values(array_filter((array) ($copy['dealTiles'] ?? [])));
$seasonCues = array_values(array_filter((array) ($copy['seasonCues'] ?? [])));
$fallbackSummerLabel = $locale === 'en' ? 'Summer highlight' : 'Điểm nhấn mùa hè';

$slugify = static function (string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
};

$destinationFilters = [];
foreach ($summerFilterTours as $tour) {
    $items = array_values(array_filter(array_map(
        static fn($item): string => trim((string) $item),
        (array) ($tour['destination_items'] ?? [])
    )));

    if ($items === []) {
        $fallbackLabel = trim((string) (($tour['destination_name'] ?? '') ?: ($tour['continent'] ?? '')));
        if ($fallbackLabel !== '') {
            $items = [$fallbackLabel];
        }
    }

    foreach (array_unique($items) as $label) {
        $key = 'destination-' . $slugify($label);
        if ($key === 'destination-') {
            continue;
        }

        if (! isset($destinationFilters[$key])) {
            $destinationFilters[$key] = [
                'key' => $key,
                'label' => $label,
                'count' => 0,
            ];
        }

        $destinationFilters[$key]['count']++;
    }
}

$destinationFilters = array_values($destinationFilters);

$formatPromotionDeadline = static function (?string $value) use ($locale): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return $locale === 'en'
        ? 'Ends ' . date('M d', $timestamp)
        : 'Kết thúc ' . date('d/m', $timestamp);
};

$resolveSaleBadge = static function (array $tour) use ($fallbackSummerLabel, $locale): string {
    $badge = trim((string) ($tour['promotion']['badge'] ?? ''));
    if ($badge !== '') {
        return $badge;
    }

    if (! empty($tour['promotion']['is_active'])) {
        return $locale === 'en' ? 'Flash deal' : 'Deal đang mở';
    }

    return $fallbackSummerLabel;
};

$resolvePriceLabel = static function (array $tour) use ($copy, $locale): string {
    if (! empty($tour['promotion']['is_active'])) {
        return (string) ($copy['pricePromoLabel'] ?? ($locale === 'en' ? 'Promotional price' : 'Giá ưu đãi'));
    }

    return (string) ($copy['priceDefaultLabel'] ?? ($locale === 'en' ? 'Summer tour price' : 'Giá tour hè'));
};

$resolveUrgency = static function (array $tour) use ($formatPromotionDeadline, $copy, $locale): string {
    $deadline = $formatPromotionDeadline((string) ($tour['promotion']['ends_at'] ?? ''));
    if ($deadline !== '') {
        return $deadline;
    }

    if (! empty($tour['promotion']['is_active'])) {
        return (string) ($copy['urgencyFallback'] ?? ($locale === 'en' ? 'Deal is live' : 'Deal đang mở'));
    }

    return '';
};

$resolveDeparture = static function (array $tour) use ($locale): string {
    $departure = trim((string) ($tour['departure'] ?? ''));
    if ($departure === '') {
        return '';
    }

    return $locale === 'en'
        ? 'Departure: ' . $departure
        : 'Khởi hành: ' . $departure;
};

$resolveDisplayLocation = static function (array $tour): string {
    $summary = trim((string) ($tour['destination_summary'] ?? ''));
    if ($summary !== '') {
        return $summary;
    }

    $destination = trim((string) ($tour['destination_name'] ?? ''));
    if ($destination !== '') {
        return $destination;
    }

    return trim((string) ($tour['continent'] ?? ''));
};

$leadCopy = $locale === 'en'
    ? [
        'eyebrow' => 'Summer tour consultation',
        'title' => 'Send your summer brief and get a tighter shortlist faster.',
        'desc' => 'Share destination, group size and travel period. Travel Plus will suggest a more relevant summer shortlist.',
        'point1' => 'Suitable for families, private groups and company trips',
        'point2' => 'Clearer route, duration and service-level recommendation',
        'point3' => 'Works for domestic summer tours, outbound tours and group departures',
        'hotlineLabel' => 'Hotline',
    ]
    : [
        'eyebrow' => 'Tư vấn tour hè nhanh',
        'title' => 'Gửi nhu cầu tour hè để Travel Plus gợi ý nhanh hơn.',
        'desc' => 'Điền điểm đến, số khách và thời gian dự kiến. Travel Plus sẽ gom lại nhóm tour hè phù hợp hơn cho bạn.',
        'point1' => 'Phù hợp cho gia đình, nhóm bạn, công ty và đoàn riêng',
        'point2' => 'Dễ chốt nhanh điểm đến, số ngày và mức dịch vụ',
        'point3' => 'Nhận gợi ý tour hè có giá tốt và lịch khởi hành phù hợp',
        'hotlineLabel' => 'Hotline',
    ];
$leadFormCopy = $locale === 'en'
    ? [
        'formTitle' => 'Tell us about your summer plan',
        'formDesc' => 'The clearer the brief, the faster Travel Plus can shortlist the right summer tours.',
        'nameLabel' => 'Full name',
        'namePlaceholder' => 'Your name',
        'phoneLabel' => 'Phone number',
        'phonePlaceholder' => '+84...',
        'emailLabel' => 'Email',
        'emailPlaceholder' => 'email@domain.com',
        'destinationLabel' => 'Destination of interest',
        'destinationPlaceholder' => 'Example: Da Nang, Phu Quoc, Europe, Japan',
        'travelersLabel' => 'Group size',
        'travelersPlaceholder' => 'Example: 12 guests',
        'estimatedTimeLabel' => 'Preferred travel period',
        'estimatedTimePlaceholder' => 'Example: July 2026 or late summer',
        'tripLengthLabel' => 'Trip length',
        'tripLengthPlaceholder' => 'Example: 8 days 7 nights',
        'hotelRatingLabel' => 'Preferred hotel standard',
        'messageLabel' => 'Specific requirements',
        'messagePlaceholder' => 'Share budget, departure city, traveler profile, sightseeing priority or any service request.',
        'hotelOptions' => [
            '' => 'Select hotel standard',
            '3-star' => '3-star',
            '4-star' => '4-star',
            '5-star' => '5-star',
            'flexible' => 'Flexible',
        ],
        'privacyPrefix' => 'I agree to the',
        'privacyJoin' => 'and',
    ]
    : [
        'formTitle' => 'Để lại nhu cầu tour hè của bạn',
        'formDesc' => 'Thông tin càng rõ, Travel Plus càng dễ lên shortlist tour hè sát nhu cầu hơn.',
        'nameLabel' => 'Họ và tên',
        'namePlaceholder' => 'Tên người liên hệ',
        'phoneLabel' => 'Số điện thoại',
        'phonePlaceholder' => '+84...',
        'emailLabel' => 'Email',
        'emailPlaceholder' => 'email@domain.com',
        'destinationLabel' => 'Điểm đến quan tâm',
        'destinationPlaceholder' => 'Ví dụ: Đà Nẵng, Phú Quốc, châu Âu, Nhật Bản',
        'travelersLabel' => 'Số lượng khách',
        'travelersPlaceholder' => 'Ví dụ: 12 khách',
        'estimatedTimeLabel' => 'Thời gian dự kiến',
        'estimatedTimePlaceholder' => 'Ví dụ: Tháng 7/2026 hoặc cuối hè',
        'tripLengthLabel' => 'Thời gian đi',
        'tripLengthPlaceholder' => 'Ví dụ: 8 ngày 7 đêm',
        'hotelRatingLabel' => 'Khách sạn mong muốn',
        'messageLabel' => 'Yêu cầu cụ thể',
        'messagePlaceholder' => 'Mô tả thêm ngân sách, điểm khởi hành, nhóm khách, ưu tiên tham quan hoặc yêu cầu dịch vụ riêng.',
        'hotelOptions' => [
            '' => 'Chọn tiêu chuẩn khách sạn',
            '3-star' => '3 sao',
            '4-star' => '4 sao',
            '5-star' => '5 sao',
            'flexible' => 'Linh hoạt',
        ],
        'privacyPrefix' => 'Tôi đồng ý với',
        'privacyJoin' => 'và',
    ];
$contactError = session()->getFlashdata('error');
$contactSuccess = session()->getFlashdata('success');
$phone = '+84795681568';
$phoneDisplay = '+84 79 568 1 568';
?>

<div class="summer-landing-page"<?= $landingBackdropImage !== '' ? ' style="--summer-page-backdrop: url(\'' . esc($landingBackdropImage, 'attr') . '\');"' : '' ?>>
    <?php if (! empty($copy['flashBarLabel']) || ! empty($copy['flashBarMessage'])): ?>
        <section class="summer-flash-bar" aria-label="Summer flash sale announcement">
            <div class="container">
                <div class="summer-flash-bar__inner">
                    <?php if (! empty($copy['flashBarLabel'])): ?>
                        <span class="summer-flash-bar__label"><?= esc((string) $copy['flashBarLabel']) ?></span>
                    <?php endif; ?>

                    <?php if (! empty($copy['flashBarMessage'])): ?>
                        <strong><?= esc((string) $copy['flashBarMessage']) ?></strong>
                    <?php endif; ?>

                    <?php if (! empty($copy['flashBarPoints'])): ?>
                        <div class="summer-flash-bar__points">
                            <?php foreach ((array) $copy['flashBarPoints'] as $item): ?>
                                <span><?= esc((string) $item) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="summer-hero">
        <div class="container">
            <?php if ($landingBannerImage !== ''): ?>
                <div class="summer-hero__banner-shell">
                    <img class="summer-hero__banner" src="<?= esc($landingBannerImage, 'attr') ?>" alt="<?= esc((string) ($copy['eyebrow'] ?? $copy['title'] ?? 'Tour he Travel Plus'), 'attr') ?>" loading="eager" decoding="async">
                </div>
            <?php endif; ?>

            <div class="summer-hero__summary">
                <div class="summer-hero__copy">
                    <span class="summer-hero__eyebrow"><?= esc((string) ($copy['eyebrow'] ?? '')) ?></span>
                    <h1><?= esc((string) ($copy['title'] ?? '')) ?></h1>
                    <p><?= esc((string) ($copy['desc'] ?? '')) ?></p>

                    <?php if ($heroLocations !== []): ?>
                        <div class="summer-hero__chips" aria-label="Summer destinations">
                            <?php foreach ($heroLocations as $location): ?>
                                <span><?= esc((string) $location) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($seasonCues !== []): ?>
                        <div class="summer-hero__season-grid" aria-label="Summer travel highlights">
                            <?php foreach ($seasonCues as $cue): ?>
                                <article class="summer-season-card">
                                    <i class="bi <?= esc((string) ($cue['icon'] ?? 'bi-brightness-high'), 'attr') ?>"></i>
                                    <div>
                                        <strong><?= esc((string) ($cue['title'] ?? '')) ?></strong>
                                        <span><?= esc((string) ($cue['desc'] ?? '')) ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="summer-hero__actions">
                        <a class="summer-btn summer-btn--primary" href="<?= esc((string) $searchUrl, 'attr') ?>">
                            <?= esc((string) ($copy['primaryCta'] ?? '')) ?>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                        <a class="summer-btn summer-btn--ghost" href="<?= esc((string) $contactUrl, 'attr') ?>">
                            <?= esc((string) ($copy['secondaryCta'] ?? '')) ?>
                        </a>
                    </div>

                    <?php if (! empty($copy['offerBar'])): ?>
                        <div class="summer-hero__offer-bar" aria-label="Summer booking highlights">
                            <?php foreach ((array) $copy['offerBar'] as $item): ?>
                                <span><?= esc((string) $item) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="summer-hero__panel" aria-label="Summer overview">
                    <div class="summer-hero__panel-head">
                        <?php if (! empty($copy['panelTag'])): ?>
                            <span><?= esc((string) $copy['panelTag']) ?></span>
                        <?php endif; ?>
                        <?php if (! empty($copy['panelTitle'])): ?>
                            <strong><?= esc((string) $copy['panelTitle']) ?></strong>
                        <?php endif; ?>
                    </div>

                    <div class="summer-hero__metrics">
                        <?php foreach ((array) ($copy['metrics'] ?? []) as $metric): ?>
                            <article>
                                <strong><?= esc((string) ($metric['value'] ?? '0')) ?></strong>
                                <span><?= esc((string) ($metric['label'] ?? '')) ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="summer-hero__notes">
                        <?php foreach ((array) ($copy['quickNotes'] ?? []) as $note): ?>
                            <div>
                                <i class="bi bi-check2-circle"></i>
                                <p><?= esc((string) $note) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>

            <?php if ($heroCards !== []): ?>
                <div class="summer-hero__teasers">
                    <?php foreach ($heroCards as $tour): ?>
                        <?php
                        $heroPriceLabel = ! empty($tour['price']['label']) ? (string) $tour['price']['label'] : '';
                        $heroPriceTitle = $heroPriceLabel !== '' ? $resolvePriceLabel($tour) : '';
                        $heroUrgency = $resolveUrgency($tour);
                        $heroLocation = $resolveDisplayLocation($tour);
                        ?>
                        <a class="summer-hero-teaser" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                            <div class="summer-hero-teaser__media">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <span><?= esc($resolveSaleBadge($tour)) ?></span>
                            </div>
                            <div class="summer-hero-teaser__body">
                                <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                <small>
                                    <?= esc($heroLocation) ?>
                                    <?php if (! empty($tour['duration']['label'])): ?>
                                        <em><?= esc((string) $tour['duration']['label']) ?></em>
                                    <?php endif; ?>
                                </small>
                                <?php if ($heroPriceLabel !== '' || $heroUrgency !== ''): ?>
                                    <div class="summer-hero-teaser__offer">
                                        <?php if ($heroPriceLabel !== ''): ?>
                                            <div class="summer-hero-teaser__price">
                                                <?php if ($heroPriceTitle !== ''): ?>
                                                    <small><?= esc($heroPriceTitle) ?></small>
                                                <?php endif; ?>
                                                <strong><?= esc($heroPriceLabel) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($heroUrgency !== ''): ?>
                                            <span class="summer-hero-teaser__deadline"><?= esc($heroUrgency) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($flashSignals !== []): ?>
        <section class="summer-signal-bar" aria-label="Summer campaign signals">
            <div class="container">
                <div class="summer-signal-bar__track">
                    <?php foreach (range(1, 2) as $round): ?>
                        <?php foreach ($flashSignals as $signal): ?>
                            <span><?= esc((string) $signal) ?></span>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="summer-lead" id="summerLeadForm">
        <div class="container">
            <div class="summer-lead__layout">
                <div class="summer-lead__pitch">
                    <span><?= esc($leadCopy['eyebrow']) ?></span>
                    <h2><?= esc($leadCopy['title']) ?></h2>
                    <p><?= esc($leadCopy['desc']) ?></p>
                    <div class="summer-lead__points">
                        <div><i class="bi bi-check2-circle"></i><span><?= esc($leadCopy['point1']) ?></span></div>
                        <div><i class="bi bi-check2-circle"></i><span><?= esc($leadCopy['point2']) ?></span></div>
                        <div><i class="bi bi-check2-circle"></i><span><?= esc($leadCopy['point3']) ?></span></div>
                    </div>
                    <a class="summer-lead__hotline" href="tel:<?= esc($phone, 'attr') ?>">
                        <strong><?= esc($leadCopy['hotlineLabel']) ?></strong>
                        <span><?= esc($phoneDisplay) ?></span>
                    </a>
                </div>

                <div class="summer-lead__form-card">
                    <div class="summer-lead__form-intro">
                        <strong><?= esc($leadFormCopy['formTitle']) ?></strong>
                        <p><?= esc($leadFormCopy['formDesc']) ?></p>
                    </div>

                    <?php if ($contactError): ?>
                        <div class="alert alert-danger">
                            <?= nl2br(esc((string) $contactError)) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($contactSuccess): ?>
                        <div class="alert alert-success">
                            <?= esc((string) $contactSuccess) ?>
                        </div>
                    <?php endif; ?>

                    <form
                        method="POST"
                        id="contactForm"
                        class="summer-lead__form"
                        action="<?= esc($contactUrl, 'attr') ?>"
                        data-recaptcha-site-key="<?= esc($recaptchaSiteKey, 'attr') ?>"
                        data-recaptcha-error="<?= esc(lang('Frontend.contact.recaptchaFailed', [], $locale), 'attr') ?>"
                        data-name-error="<?= esc($locale === 'en' ? 'Please enter your full name.' : 'Vui lòng nhập họ và tên.', 'attr') ?>"
                        data-email-required="<?= esc($locale === 'en' ? 'Please enter your email address.' : 'Vui lòng nhập email.', 'attr') ?>"
                        data-email-invalid="<?= esc($locale === 'en' ? 'Please enter a valid email address.' : 'Vui lòng nhập email hợp lệ.', 'attr') ?>"
                        data-phone-required="<?= esc($locale === 'en' ? 'Please enter your phone number.' : 'Vui lòng nhập số điện thoại.', 'attr') ?>"
                        data-phone-invalid="<?= esc($locale === 'en' ? 'Please enter a valid Vietnamese phone number.' : 'Vui lòng nhập số điện thoại Việt Nam hợp lệ.', 'attr') ?>"
                        data-message-error="<?= esc($locale === 'en' ? 'Your message must be at least 10 characters.' : 'Nội dung tối thiểu 10 ký tự.', 'attr') ?>"
                        data-privacy-error="<?= esc($locale === 'en' ? 'Please agree to the privacy statement and terms of service.' : 'Vui lòng đồng ý với Chính sách bảo mật và Điều khoản sử dụng.', 'attr') ?>"
                        novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="contact_form_token" value="<?= esc($contactFormToken, 'attr') ?>">
                        <input type="hidden" name="redirect_to" value="<?= esc($leadRedirectUrl, 'attr') ?>">
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                        <div class="summer-lead__form-grid">
                            <label>
                                <span><?= esc($leadFormCopy['nameLabel']) ?></span>
                                <input type="text" name="name" value="<?= esc((string) old('name'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['namePlaceholder'], 'attr') ?>" autocomplete="name" required>
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['phoneLabel']) ?></span>
                                <input type="tel" name="phone" value="<?= esc((string) old('phone'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['phonePlaceholder'], 'attr') ?>" autocomplete="tel" required>
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['emailLabel']) ?></span>
                                <input type="email" name="email" value="<?= esc((string) old('email'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['emailPlaceholder'], 'attr') ?>" autocomplete="email" required>
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['destinationLabel']) ?></span>
                                <input type="text" name="destination" value="<?= esc((string) old('destination'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['destinationPlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['travelersLabel']) ?></span>
                                <input type="text" name="travelers" value="<?= esc((string) old('travelers'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['travelersPlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['estimatedTimeLabel']) ?></span>
                                <input type="text" name="estimated_time" value="<?= esc((string) old('estimated_time'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['estimatedTimePlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['tripLengthLabel']) ?></span>
                                <input type="text" name="trip_length" value="<?= esc((string) old('trip_length'), 'attr') ?>" placeholder="<?= esc($leadFormCopy['tripLengthPlaceholder'], 'attr') ?>">
                            </label>
                            <label>
                                <span><?= esc($leadFormCopy['hotelRatingLabel']) ?></span>
                                <select name="hotel_rating">
                                    <?php foreach ((array) $leadFormCopy['hotelOptions'] as $optionValue => $optionLabel): ?>
                                        <option value="<?= esc((string) $optionValue, 'attr') ?>" <?= old('hotel_rating') === (string) $optionValue ? 'selected' : '' ?>>
                                            <?= esc((string) $optionLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label class="summer-lead__form-message">
                                <span><?= esc($leadFormCopy['messageLabel']) ?></span>
                                <textarea name="message" rows="5" placeholder="<?= esc($leadFormCopy['messagePlaceholder'], 'attr') ?>" required><?= esc((string) old('message')) ?></textarea>
                            </label>
                        </div>

                        <label class="summer-lead__check" for="summerLeadPrivacyAgree">
                            <input type="checkbox" name="privacy_agree" value="1" id="summerLeadPrivacyAgree" <?= old('privacy_agree') ? 'checked' : '' ?> required>
                            <span>
                                <?= esc($leadFormCopy['privacyPrefix']) ?>
                                &nbsp;
                                <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc(lang('Frontend.footer.link.privacy', [], $locale)) ?></a>
                                &nbsp;<?= esc($leadFormCopy['privacyJoin']) ?>&nbsp;
                                <a href="<?= esc($termsUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc(lang('Frontend.footer.link.terms', [], $locale)) ?></a>
                            </span>
                        </label>

                        <button
                            type="submit"
                            class="summer-lead__submit"
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

    <?php if ($destinationFilters !== [] && $summerFilterTours !== []): ?>
        <section class="summer-destination" id="summerDestinationPicker">
            <div class="container">
                <div class="summer-destination__head">
                    <div>
                        <span><?= esc((string) ($copy['destinationTitle'] ?? '')) ?></span>
                        <h2><?= esc((string) ($copy['destinationTitle'] ?? '')) ?></h2>
                        <p><?= esc((string) ($copy['destinationDesc'] ?? '')) ?></p>
                    </div>
                </div>

                <div class="summer-destination__filters" id="summerDestinationFilters">
                    <button type="button" class="is-active" data-summer-destination-trigger="all">
                        <?= esc((string) ($copy['destinationAll'] ?? 'Tất cả')) ?>
                        <strong><?= esc((string) count($summerFilterTours)) ?></strong>
                    </button>
                    <?php foreach ($destinationFilters as $filter): ?>
                        <button type="button" data-summer-destination-trigger="<?= esc((string) $filter['key'], 'attr') ?>">
                            <?= esc((string) $filter['label']) ?>
                            <strong><?= esc((string) $filter['count']) ?></strong>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="summer-destination__cards" id="summerDestinationCards">
                    <?php foreach ($summerFilterTours as $tour): ?>
                        <?php
                        $destinationLabel = trim((string) (($tour['destination_name'] ?? '') ?: ($tour['continent'] ?? '')));
                        $destinationSummary = trim((string) (($tour['destination_summary'] ?? '') ?: $destinationLabel));
                        $destinationSummaryFull = trim((string) (($tour['destination_summary_full'] ?? '') ?: $destinationSummary));
                        $destinationItems = array_values(array_filter(array_map(
                            static fn($item): string => trim((string) $item),
                            (array) ($tour['destination_items'] ?? [])
                        )));
                        $destinationId = (int) ($tour['destination_id'] ?? 0);
                        $destinationKeys = array_values(array_filter(array_map(
                            static fn(string $label): string => ($slug = $slugify($label)) !== '' ? 'destination-' . $slug : '',
                            array_unique($destinationItems)
                        )));
                        if ($destinationKeys === []) {
                            $destinationKey = trim((string) ($tour['destination_slug'] ?? ''));
                            if ($destinationKey === '') {
                                $destinationKey = $destinationId > 0 ? 'destination-' . $destinationId : 'destination-' . $slugify($destinationLabel);
                            }
                            $destinationKeys = [$destinationKey];
                        }
                        $filterPriceLabel = ! empty($tour['price']['label']) ? (string) $tour['price']['label'] : '';
                        $filterPriceTitle = $filterPriceLabel !== '' ? $resolvePriceLabel($tour) : '';
                        $filterUrgency = $resolveUrgency($tour);
                        $filterDeparture = trim((string) ($tour['departure'] ?? ''));
                        ?>
                        <a class="summer-hero-teaser summer-hero-teaser--destination" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>" data-summer-destination-item="<?= esc(implode(' ', $destinationKeys), 'attr') ?>">
                            <div class="summer-hero-teaser__media">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <span><?= esc($resolveSaleBadge($tour)) ?></span>
                            </div>
                            <div class="summer-hero-teaser__body summer-hero-teaser__body--destination">
                                <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                <div class="summer-destination-card__facts">
                                    <p class="summer-destination-card__fact summer-destination-card__fact--route" title="<?= esc($destinationSummaryFull, 'attr') ?>">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        <span><?= esc($destinationSummary) ?></span>
                                    </p>
                                    <div class="summer-destination-card__meta-grid">
                                        <?php if (! empty($tour['duration']['label'])): ?>
                                            <p class="summer-destination-card__fact summer-destination-card__fact--meta">
                                                <i class="bi bi-clock"></i>
                                                <span><?= esc((string) $tour['duration']['label']) ?></span>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($filterDeparture !== ''): ?>
                                            <p class="summer-destination-card__fact summer-destination-card__fact--meta" title="<?= esc($filterDeparture, 'attr') ?>">
                                                <i class="bi bi-calendar-event"></i>
                                                <span><?= esc($filterDeparture) ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($filterPriceLabel !== '' || $filterUrgency !== ''): ?>
                                    <div class="summer-hero-teaser__offer">
                                        <?php if ($filterPriceLabel !== ''): ?>
                                            <div class="summer-hero-teaser__price">
                                                <?php if ($filterPriceTitle !== ''): ?>
                                                    <small><?= esc($filterPriceTitle) ?></small>
                                                <?php endif; ?>
                                                <strong><?= esc($filterPriceLabel) ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($filterUrgency !== ''): ?>
                                            <span class="summer-hero-teaser__deadline"><?= esc($filterUrgency) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($dealTiles !== []): ?>
        <section class="summer-deal-grid">
            <div class="container">
                <div class="summer-deal-grid__inner">
                    <?php foreach ($dealTiles as $index => $tile): ?>
                        <article class="summer-deal-tile<?= $index === 1 ? ' summer-deal-tile--accent' : '' ?>">
                            <span><?= esc((string) ($tile['label'] ?? '')) ?></span>
                            <strong><?= esc((string) ($tile['value'] ?? '')) ?></strong>
                            <p><?= esc((string) ($tile['desc'] ?? '')) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($editorialPrimary !== null): ?>
        <?php
        $editorialPriceLabel = ! empty($editorialPrimary['price']['label']) ? (string) $editorialPrimary['price']['label'] : '';
        $editorialUrgency = $resolveUrgency($editorialPrimary);
        $editorialDeparture = $resolveDeparture($editorialPrimary);
        $editorialLocation = $resolveDisplayLocation($editorialPrimary);
        ?>
        <section class="summer-editorial">
            <div class="container">
                <div class="summer-editorial__layout">
                    <a class="summer-editorial__feature" href="<?= esc((string) ($editorialPrimary['link'] ?? '#'), 'attr') ?>">
                        <img src="<?= esc((string) ($editorialPrimary['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($editorialPrimary['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                        <div class="summer-editorial__overlay">
                            <span><?= esc((string) ($copy['editorialKicker'] ?? $copy['promoTitle'] ?? '')) ?></span>
                            <strong><?= esc((string) ($editorialPrimary['title'] ?? '')) ?></strong>
                            <p><?= esc($editorialLocation) ?></p>

                            <div class="summer-editorial__meta">
                                <?php if ($editorialUrgency !== ''): ?>
                                    <em><?= esc($editorialUrgency) ?></em>
                                <?php endif; ?>
                                <?php if ($editorialDeparture !== ''): ?>
                                    <small><?= esc($editorialDeparture) ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="summer-editorial__offer">
                                <?php if ($editorialPriceLabel !== ''): ?>
                                    <div class="summer-editorial__price">
                                        <span><?= esc($resolvePriceLabel($editorialPrimary)) ?></span>
                                        <em><?= esc($editorialPriceLabel) ?></em>
                                    </div>
                                <?php endif; ?>
                                <b><?= esc((string) ($copy['editorialCta'] ?? $copy['primaryCta'] ?? '')) ?> <i class="bi bi-arrow-up-right"></i></b>
                            </div>
                        </div>
                    </a>

                    <div class="summer-editorial__side">
                        <div class="summer-editorial__copy">
                            <span><?= esc((string) ($copy['featuredTitle'] ?? '')) ?></span>
                            <h2><?= esc((string) ($copy['railTitle'] ?? $copy['featuredTitle'] ?? '')) ?></h2>
                            <p><?= esc((string) ($copy['featuredDesc'] ?? '')) ?></p>
                        </div>

                        <?php foreach ($editorialSide as $tour): ?>
                            <?php
                            $miniPriceLabel = ! empty($tour['price']['label']) ? (string) $tour['price']['label'] : '';
                            $miniPriceTitle = $miniPriceLabel !== '' ? $resolvePriceLabel($tour) : '';
                            $miniUrgency = $resolveUrgency($tour);
                            $miniLocation = $resolveDisplayLocation($tour);
                            ?>
                            <a class="summer-editorial__mini" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <div>
                                    <span><?= esc($resolveSaleBadge($tour)) ?></span>
                                    <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                    <small>
                                        <?= esc($miniLocation) ?>
                                        <?php if (! empty($tour['duration']['label'])): ?>
                                            <em><?= esc((string) $tour['duration']['label']) ?></em>
                                        <?php endif; ?>
                                    </small>
                                    <?php if ($miniPriceLabel !== '' || $miniUrgency !== ''): ?>
                                        <div class="summer-editorial__mini-offer">
                                            <?php if ($miniPriceLabel !== ''): ?>
                                                <div class="summer-editorial__mini-price">
                                                    <?php if ($miniPriceTitle !== ''): ?>
                                                        <small><?= esc($miniPriceTitle) ?></small>
                                                    <?php endif; ?>
                                                    <strong><?= esc($miniPriceLabel) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($miniUrgency !== ''): ?>
                                                <span class="summer-editorial__mini-deadline"><?= esc($miniUrgency) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($summerRail !== []): ?>
        <section class="summer-rail-section">
            <div class="container">
                <div class="summer-rail__head">
                    <div>
                        <span><?= esc((string) ($copy['promoTitle'] ?? '')) ?></span>
                        <h2><?= esc((string) ($copy['railTitle'] ?? $copy['promoTitle'] ?? '')) ?></h2>
                        <?php if (! empty($copy['railDesc'])): ?>
                            <p><?= esc((string) $copy['railDesc']) ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= esc((string) $searchUrl, 'attr') ?>"><?= esc((string) ($copy['railLink'] ?? $copy['ctaSecondary'] ?? '')) ?></a>
                </div>

                <div class="summer-rail">
                    <?php foreach ($summerRail as $tour): ?>
                        <?php
                        $railPriceLabel = ! empty($tour['price']['label']) ? (string) $tour['price']['label'] : '';
                        $railUrgency = $resolveUrgency($tour);
                        $railDeparture = $resolveDeparture($tour);
                        $railLocation = $resolveDisplayLocation($tour);
                        ?>
                        <a class="summer-rail-card" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                            <div class="summer-rail-card__media">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <span><?= esc($resolveSaleBadge($tour)) ?></span>
                            </div>

                            <div class="summer-rail-card__body">
                                <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                <small>
                                    <?= esc($railLocation) ?>
                                    <?php if (! empty($tour['duration']['label'])): ?>
                                        <em><?= esc((string) $tour['duration']['label']) ?></em>
                                    <?php endif; ?>
                                </small>

                                <?php if ($railPriceLabel !== ''): ?>
                                    <div class="summer-rail-card__price">
                                        <span><?= esc($resolvePriceLabel($tour)) ?></span>
                                        <strong><?= esc($railPriceLabel) ?></strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($railUrgency !== '' || $railDeparture !== ''): ?>
                                    <div class="summer-rail-card__details">
                                        <?php if ($railUrgency !== ''): ?>
                                            <em><?= esc($railUrgency) ?></em>
                                        <?php endif; ?>
                                        <?php if ($railDeparture !== ''): ?>
                                            <span><?= esc($railDeparture) ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <b class="summer-rail-card__cta">
                                    <?= esc((string) ($copy['cardCta'] ?? $copy['primaryCta'] ?? '')) ?>
                                    <i class="bi bi-arrow-up-right"></i>
                                </b>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($domesticTours !== [] || $outboundTours !== []): ?>
        <section class="summer-buckets">
            <div class="container">
                <div class="summer-buckets__grid">
                    <?php if ($domesticTours !== []): ?>
                        <div class="summer-bucket">
                            <span><?= esc((string) ($copy['domesticTitle'] ?? '')) ?></span>
                            <h3><?= esc((string) ($copy['domesticTitle'] ?? '')) ?></h3>
                            <div class="summer-bucket__list">
                                <?php foreach ($domesticTours as $tour): ?>
                                    <?php $bucketLocation = $resolveDisplayLocation($tour); ?>
                                    <a href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>" class="summer-bucket__item" style="--summer-bucket-image:url('<?= esc((string) (($tour['banner_image'] ?? '') ?: ($tour['image'] ?? '')), 'attr') ?>');">
                                        <div class="summer-bucket__item-content">
                                            <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                            <div class="summer-bucket__item-meta">
                                                <small>
                                                    <span><?= esc($locale === 'en' ? 'From' : 'Giá từ') ?></span>
                                                    <strong><?= esc((string) ($tour['price']['label'] ?? '')) ?></strong>
                                                </small>
                                                <span><?= esc($bucketLocation) ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($outboundTours !== []): ?>
                        <div class="summer-bucket summer-bucket--accent">
                            <span><?= esc((string) ($copy['outboundTitle'] ?? '')) ?></span>
                            <h3><?= esc((string) ($copy['outboundTitle'] ?? '')) ?></h3>
                            <div class="summer-bucket__list">
                                <?php foreach ($outboundTours as $tour): ?>
                                    <?php $bucketLocation = $resolveDisplayLocation($tour); ?>
                                    <a href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>" class="summer-bucket__item" style="--summer-bucket-image:url('<?= esc((string) (($tour['banner_image'] ?? '') ?: ($tour['image'] ?? '')), 'attr') ?>');">
                                        <div class="summer-bucket__item-content">
                                            <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                            <div class="summer-bucket__item-meta">
                                                <small>
                                                    <span><?= esc($locale === 'en' ? 'From' : 'Giá từ') ?></span>
                                                    <strong><?= esc((string) ($tour['price']['label'] ?? '')) ?></strong>
                                                </small>
                                                <span><?= esc($bucketLocation) ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="summer-cta">
        <div class="container">
            <div class="summer-cta__card">
                <div class="summer-cta__copy">
                    <span><?= esc((string) ($copy['eyebrow'] ?? '')) ?></span>
                    <h2><?= esc((string) ($copy['ctaTitle'] ?? '')) ?></h2>
                    <p><?= esc((string) ($copy['ctaDesc'] ?? '')) ?></p>
                </div>
                <div class="summer-cta__actions">
                    <a class="summer-btn summer-btn--primary" href="<?= esc((string) $contactUrl, 'attr') ?>">
                        <?= esc((string) ($copy['ctaPrimary'] ?? '')) ?>
                        <i class="bi bi-arrow-up-right"></i>
                    </a>
                    <a class="summer-btn summer-btn--ghost" href="<?= esc((string) $searchUrl, 'attr') ?>">
                        <?= esc((string) ($copy['ctaSecondary'] ?? '')) ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($recaptchaSiteKey !== ''): ?>
<script defer src="https://www.google.com/recaptcha/api.js?render=<?= esc($recaptchaSiteKey, 'url') ?>"></script>
<?php endif; ?>
<script type="module" src="<?= base_url('assets/js/contact-page.js?v=' . (@filemtime(FCPATH . 'assets/js/contact-page.js') ?: time())) ?>"></script>
<script>
(() => {
  const filterRoot = document.getElementById("summerDestinationFilters");
  const cardsRoot = document.getElementById("summerDestinationCards");

  if (!filterRoot || !cardsRoot) {
    return;
  }

  const buttons = Array.from(filterRoot.querySelectorAll("[data-summer-destination-trigger]"));
  const cards = Array.from(cardsRoot.querySelectorAll("[data-summer-destination-item]"));

  const applyFilter = (key) => {
    buttons.forEach((button) => {
      button.classList.toggle("is-active", button.dataset.summerDestinationTrigger === key);
    });

    cards.forEach((card) => {
      const cardKeys = (card.dataset.summerDestinationItem || "")
        .split(/\s+/)
        .filter(Boolean);
      card.hidden = key !== "all" && !cardKeys.includes(key);
    });
  };

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      applyFilter(button.dataset.summerDestinationTrigger || "all");
    });
  });
})();
</script>
<?= $this->endSection() ?>
