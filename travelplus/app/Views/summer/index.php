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
    $label = trim((string) (($tour['destination_name'] ?? '') ?: ($tour['continent'] ?? '')));
    if ($label === '') {
        continue;
    }

    $destinationId = (int) ($tour['destination_id'] ?? 0);
    $key = trim((string) ($tour['destination_slug'] ?? ''));

    if ($key === '') {
        $key = $destinationId > 0 ? 'destination-' . $destinationId : 'destination-' . $slugify($label);
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

$leadCopy = $locale === 'en'
    ? [
        'eyebrow' => 'Quick summer consultation',
        'title' => 'Get a tighter summer shortlist instead of browsing every route one by one.',
        'desc' => 'Leave your month, group size and destination idea. Travel Plus will suggest a summer shortlist that matches budget and travel style.',
        'point1' => 'Best for families, friend groups and company teams',
        'point2' => 'Clearer route shortlist with price direction',
        'point3' => 'Works for beach trips, cooler city breaks and outbound plans',
        'hotlineLabel' => 'Hotline',
    ]
    : [
        'eyebrow' => 'Đăng ký tư vấn nhanh',
        'title' => 'Nhận shortlist tour hè gọn hơn thay vì phải mở từng tour một.',
        'desc' => 'Để lại tháng đi, số người và điểm đến đang quan tâm. Travel Plus sẽ gợi ý nhóm tour phù hợp với ngân sách và kiểu trải nghiệm của bạn.',
        'point1' => 'Phù hợp cho gia đình, nhóm bạn và đoàn doanh nghiệp',
        'point2' => 'Gợi ý nhanh tuyến phù hợp và mặt bằng giá',
        'point3' => 'Dùng tốt cho tour biển, tour đổi gió và tour nước ngoài',
        'hotlineLabel' => 'Hotline',
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
                        $heroUrgency = $resolveUrgency($tour);
                        ?>
                        <a class="summer-hero-teaser" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                            <div class="summer-hero-teaser__media">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <span><?= esc($resolveSaleBadge($tour)) ?></span>
                            </div>
                            <div class="summer-hero-teaser__body">
                                <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                <small>
                                    <?= esc((string) ($tour['continent'] ?? '')) ?>
                                    <?php if (! empty($tour['duration']['label'])): ?>
                                        <em><?= esc((string) $tour['duration']['label']) ?></em>
                                    <?php endif; ?>
                                </small>
                                <?php if ($heroPriceLabel !== '' || $heroUrgency !== ''): ?>
                                    <div class="summer-hero-teaser__offer">
                                        <?php if ($heroPriceLabel !== ''): ?>
                                            <b><?= esc($heroPriceLabel) ?></b>
                                        <?php endif; ?>
                                        <?php if ($heroUrgency !== ''): ?>
                                            <span><?= esc($heroUrgency) ?></span>
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
                        novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="contact_form_token" value="<?= esc($contactFormToken, 'attr') ?>">
                        <input type="hidden" name="redirect_to" value="<?= esc($leadRedirectUrl, 'attr') ?>">
                        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                        <div class="summer-lead__form-grid">
                            <label>
                                <span><?= esc(lang('Frontend.contact.name', [], $locale)) ?></span>
                                <input type="text" name="name" value="<?= esc((string) old('name'), 'attr') ?>" placeholder="<?= esc(lang('Frontend.contact.namePlaceholder', [], $locale), 'attr') ?>" autocomplete="name" required>
                            </label>
                            <label>
                                <span><?= esc(lang('Frontend.contact.phone', [], $locale)) ?></span>
                                <input type="tel" name="phone" value="<?= esc((string) old('phone'), 'attr') ?>" placeholder="+84..." autocomplete="tel" required>
                            </label>
                            <label>
                                <span><?= esc(lang('Frontend.contact.email', [], $locale)) ?></span>
                                <input type="email" name="email" value="<?= esc((string) old('email'), 'attr') ?>" placeholder="email@domain.com" autocomplete="email" required>
                            </label>
                            <label>
                                <span><?= esc(lang('Frontend.contact.destination', [], $locale)) ?></span>
                                <input type="text" name="destination" value="<?= esc((string) old('destination'), 'attr') ?>" placeholder="<?= esc(lang('Frontend.contact.destinationPlaceholder', [], $locale), 'attr') ?>">
                            </label>
                            <label class="summer-lead__form-message">
                                <span><?= esc(lang('Frontend.contact.message', [], $locale)) ?></span>
                                <textarea name="message" rows="5" placeholder="<?= esc(lang('Frontend.contact.messagePlaceholder', [], $locale), 'attr') ?>" required><?= esc((string) old('message')) ?></textarea>
                            </label>
                        </div>

                        <label class="summer-lead__check" for="summerLeadPrivacyAgree">
                            <input type="checkbox" name="privacy_agree" value="1" id="summerLeadPrivacyAgree" <?= old('privacy_agree') ? 'checked' : '' ?> required>
                            <span>
                                <a href="<?= esc($privacyUrl, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc(lang('Frontend.footer.link.privacy', [], $locale)) ?></a>
                                &nbsp;&&nbsp;
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
                        $destinationId = (int) ($tour['destination_id'] ?? 0);
                        $destinationKey = trim((string) ($tour['destination_slug'] ?? ''));
                        if ($destinationKey === '') {
                            $destinationKey = $destinationId > 0 ? 'destination-' . $destinationId : 'destination-' . $slugify($destinationLabel);
                        }
                        $filterPriceLabel = ! empty($tour['price']['label']) ? (string) $tour['price']['label'] : '';
                        $filterUrgency = $resolveUrgency($tour);
                        ?>
                        <a class="summer-hero-teaser" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>" data-summer-destination-item="<?= esc($destinationKey, 'attr') ?>">
                            <div class="summer-hero-teaser__media">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <span><?= esc($resolveSaleBadge($tour)) ?></span>
                            </div>
                            <div class="summer-hero-teaser__body">
                                <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                <small>
                                    <?= esc($destinationLabel) ?>
                                    <?php if (! empty($tour['duration']['label'])): ?>
                                        <em><?= esc((string) $tour['duration']['label']) ?></em>
                                    <?php endif; ?>
                                </small>
                                <?php if ($filterPriceLabel !== '' || $filterUrgency !== ''): ?>
                                    <div class="summer-hero-teaser__offer">
                                        <?php if ($filterPriceLabel !== ''): ?>
                                            <b><?= esc($filterPriceLabel) ?></b>
                                        <?php endif; ?>
                                        <?php if ($filterUrgency !== ''): ?>
                                            <span><?= esc($filterUrgency) ?></span>
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
        ?>
        <section class="summer-editorial">
            <div class="container">
                <div class="summer-editorial__layout">
                    <a class="summer-editorial__feature" href="<?= esc((string) ($editorialPrimary['link'] ?? '#'), 'attr') ?>">
                        <img src="<?= esc((string) ($editorialPrimary['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($editorialPrimary['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                        <div class="summer-editorial__overlay">
                            <span><?= esc((string) ($copy['editorialKicker'] ?? $copy['promoTitle'] ?? '')) ?></span>
                            <strong><?= esc((string) ($editorialPrimary['title'] ?? '')) ?></strong>
                            <p><?= esc((string) ($editorialPrimary['continent'] ?? '')) ?></p>

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
                            $miniUrgency = $resolveUrgency($tour);
                            ?>
                            <a class="summer-editorial__mini" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <div>
                                    <span><?= esc($resolveSaleBadge($tour)) ?></span>
                                    <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                    <small>
                                        <?= esc((string) ($tour['continent'] ?? '')) ?>
                                        <?php if (! empty($tour['duration']['label'])): ?>
                                            <em><?= esc((string) $tour['duration']['label']) ?></em>
                                        <?php endif; ?>
                                    </small>
                                    <?php if ($miniPriceLabel !== '' || $miniUrgency !== ''): ?>
                                        <div class="summer-editorial__mini-offer">
                                            <?php if ($miniPriceLabel !== ''): ?>
                                                <b><?= esc($miniPriceLabel) ?></b>
                                            <?php endif; ?>
                                            <?php if ($miniUrgency !== ''): ?>
                                                <span><?= esc($miniUrgency) ?></span>
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
                        ?>
                        <a class="summer-rail-card" href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                            <div class="summer-rail-card__media">
                                <img src="<?= esc((string) ($tour['image'] ?? ''), 'attr') ?>" alt="<?= esc((string) ($tour['title'] ?? ''), 'attr') ?>" loading="lazy" decoding="async">
                                <span><?= esc($resolveSaleBadge($tour)) ?></span>
                            </div>

                            <div class="summer-rail-card__body">
                                <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                <small>
                                    <?= esc((string) ($tour['continent'] ?? '')) ?>
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
                                    <a href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                                        <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                        <small><?= esc((string) ($tour['price']['label'] ?? '')) ?></small>
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
                                    <a href="<?= esc((string) ($tour['link'] ?? '#'), 'attr') ?>">
                                        <strong><?= esc((string) ($tour['title'] ?? '')) ?></strong>
                                        <small><?= esc((string) ($tour['price']['label'] ?? '')) ?></small>
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
      const cardKey = card.dataset.summerDestinationItem || "";
      card.hidden = key !== "all" && cardKey !== key;
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
