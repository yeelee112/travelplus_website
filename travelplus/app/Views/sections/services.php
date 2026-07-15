<?php
$locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';
$miceUrl = \App\Data\LocalizedPathCatalog::url('service.mice', $locale);
$visaUrl = \App\Data\LocalizedPathCatalog::url('service.visa', $locale);
$searchUrl = \App\Data\LocalizedPathCatalog::url('search', $locale);
$copy = $locale === 'en'
    ? [
        'eyebrow' => 'Core capabilities',
        'title' => 'MICE, tours and visa services in one operating ecosystem',
        'desc' => 'From program concept and destination planning to documents, logistics and onsite coordination, Travel Plus keeps each trip structured, measurable and easy to manage.',
        'cta' => 'View MICE services',
        'ctaUrl' => $miceUrl,
        'items' => [
            [
                'title' => 'Professional MICE for enterprises',
                'desc' => 'Meetings, incentive trips, congresses, symposiums, gala dinners and team building programs in Vietnam and abroad.',
                'meta' => 'Strong in medical and pharmaceutical programs',
                'url' => $miceUrl,
                'icon' => 'bi-briefcase-fill',
                'image' => 'assets/images/mice-corporate-travel.webp',
            ],
            [
                'title' => 'Outbound and domestic tours',
                'desc' => 'Curated journeys for families, groups and companies with clear departures, transparent pricing and reliable booking flow.',
                'meta' => 'Asia, Europe, America and Vietnam',
                'url' => $searchUrl,
                'icon' => 'bi-compass-fill',
                'image' => 'assets/images/home/banner02.webp',
            ],
            [
                'title' => 'Visa and travel services',
                'desc' => 'Visa documents, flights, hotels, transport and support services arranged in one preparation flow.',
                'meta' => 'Documents, timeline and service coordination',
                'url' => $visaUrl,
                'icon' => 'bi-passport-fill',
                'image' => 'assets/images/visa-wrapper.webp',
            ],
        ],
    ]
    : [
        'eyebrow' => 'Năng lực triển khai',
        'title' => 'MICE, tour và visa trong một hệ sinh thái vận hành',
        'desc' => 'Từ concept chương trình, lịch trình, hồ sơ đến điều phối onsite, Travel Plus giúp doanh nghiệp và khách đoàn quản lý chuyến đi rõ ràng, đúng ngân sách và đúng trải nghiệm.',
        'cta' => 'Xem dịch vụ MICE',
        'ctaUrl' => $miceUrl,
        'items' => [
            [
                'title' => 'Tổ chức MICE chuyên nghiệp',
                'desc' => 'Hội nghị, incentive, congress, symposium, gala dinner và team building tại Việt Nam hoặc quốc tế.',
                'meta' => 'Mạnh về chương trình y dược và đoàn bác sĩ',
                'url' => $miceUrl,
                'icon' => 'bi-briefcase-fill',
                'image' => 'assets/images/mice-corporate-travel.webp',
            ],
            [
                'title' => 'Tour nước ngoài và trong nước',
                'desc' => 'Hành trình chọn lọc cho gia đình, nhóm bạn và doanh nghiệp với lịch khởi hành rõ, giá minh bạch và quy trình đặt chỗ gọn.',
                'meta' => 'Châu Á, Châu Âu, Mỹ và Việt Nam',
                'url' => $searchUrl,
                'icon' => 'bi-compass-fill',
                'image' => 'assets/images/home/banner02.webp',
            ],
            [
                'title' => 'Visa và dịch vụ du lịch',
                'desc' => 'Hồ sơ visa, vé máy bay, khách sạn, vận chuyển và dịch vụ hỗ trợ được gom trong một quy trình chuẩn bị trọn gói.',
                'meta' => 'Hồ sơ, thời gian và điều phối dịch vụ',
                'url' => $visaUrl,
                'icon' => 'bi-passport-fill',
                'image' => 'assets/images/visa-wrapper.webp',
            ],
        ],
    ];
?>

<section class="home-modern-services" aria-labelledby="home-services-title">
    <div class="container">
        <div class="home-modern-section-head">
            <span><?= esc($copy['eyebrow']) ?></span>
            <h2 id="home-services-title"><?= esc($copy['title']) ?></h2>
            <p><?= esc($copy['desc']) ?></p>
            <a href="<?= esc((string) $copy['ctaUrl'], 'attr') ?>">
                <?= esc($copy['cta']) ?>
                <i class="bi bi-arrow-up-right"></i>
            </a>
        </div>

        <div class="home-modern-service-grid">
            <?php foreach ($copy['items'] as $index => $item): ?>
                <article class="home-modern-service-card">
                    <a class="home-modern-service-card__media" href="<?= esc((string) $item['url'], 'attr') ?>">
                        <img
                            src="<?= esc(base_url((string) $item['image']), 'attr') ?>"
                            alt="<?= esc((string) $item['title'], 'attr') ?>"
                            width="620"
                            height="420"
                            loading="lazy"
                            decoding="async">
                    </a>
                    <div class="home-modern-service-card__body">
                        <span>
                            <i class="bi <?= esc((string) $item['icon'], 'attr') ?>"></i>
                            <?= esc((string) $item['meta']) ?>
                        </span>
                        <h3><a href="<?= esc((string) $item['url'], 'attr') ?>"><?= esc((string) $item['title']) ?></a></h3>
                        <p><?= esc((string) $item['desc']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
