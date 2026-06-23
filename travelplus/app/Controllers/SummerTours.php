<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;
use App\Services\TourCatalogService;

class SummerTours extends BaseController
{
    private const LANDING_TOUR_LIMIT = 24;
    private const BUCKET_TOUR_LIMIT = 8;

    public function index()
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        $tourService = new TourCatalogService();
        $seo = new SeoService();

        $promotionalTours = $tourService->getPromotionalTours($locale, self::LANDING_TOUR_LIMIT);
        $featuredTours = $tourService->getFeaturedTours($locale, self::LANDING_TOUR_LIMIT);
        $homeTours = $tourService->getHomeTours($locale, self::LANDING_TOUR_LIMIT);

        $summerHighlights = $promotionalTours !== [] ? $promotionalTours : $featuredTours;
        if ($summerHighlights === []) {
            $summerHighlights = $homeTours;
        }

        $primaryTour = $summerHighlights[0] ?? $homeTours[0] ?? null;
        $landingBannerImage = base_url('assets/images/landing/summer/Banner_Landing.webp');
        $landingBackdropImage = base_url('assets/images/landing/summer/Background_Landing_W1920xH5000px.webp');
        $heroImage = $landingBannerImage;

        $featuredCollection = array_slice($featuredTours !== [] ? $featuredTours : $homeTours, 0, self::LANDING_TOUR_LIMIT);
        $promoCollection = array_slice($summerHighlights, 0, self::LANDING_TOUR_LIMIT);
        $flashMeta = $this->buildFlashMeta($promoCollection, $featuredCollection, $locale);

        $domesticTours = array_values(array_filter(
            $featuredCollection,
            static fn(array $tour): bool => (string) ($tour['tour_type'] ?? '') === 'inbound'
        ));
        $outboundTours = array_values(array_filter(
            $featuredCollection,
            static fn(array $tour): bool => (string) ($tour['tour_type'] ?? '') !== 'inbound'
        ));

        $heroLocations = [];
        foreach (array_slice($summerHighlights, 0, 6) as $tour) {
            $label = trim((string) ($tour['continent'] ?? ''));
            if ($label === '' || in_array($label, $heroLocations, true)) {
                continue;
            }

            $heroLocations[] = $label;
        }

        $copy = $locale === 'en'
            ? [
                'eyebrow' => 'Summer sale tours by Travel Plus',
                'title' => 'Summer sale tours for beach trips, family holidays, group departures and outbound plans.',
                'desc' => 'Compare live summer departures, promotional prices and routes worth booking early.',
                'primaryCta' => 'View summer deals',
                'secondaryCta' => 'Get summer tour advice',
                'flashBarLabel' => 'Summer tour deals',
                'flashBarMessage' => $flashMeta['bar_message'],
                'flashBarPoints' => [
                    $flashMeta['deadline_line'],
                    'Domestic and outbound summer tours on one page',
                    'Useful for families, private groups and company trips',
                ],
                'seasonCues' => [
                    ['icon' => 'bi-brightness-high', 'title' => 'Peak summer routes', 'desc' => 'Beach trips, cooler escapes and the dates guests open first.'],
                    ['icon' => 'bi-tags', 'title' => 'Promotional fares', 'desc' => 'Price-led picks so summer deals stand out faster.'],
                    ['icon' => 'bi-people', 'title' => 'Group-friendly choices', 'desc' => 'Useful for families, private groups and company trips.'],
                ],
                'metrics' => [
                    ['value' => $flashMeta['promo_count'], 'label' => 'live deals'],
                    ['value' => count($featuredCollection), 'label' => 'summer routes'],
                    ['value' => max(1, count($heroLocations)), 'label' => 'popular zones'],
                ],
                'panelTitle' => 'Why this summer page is easier to book from',
                'panelTag' => 'Travel Plus shortlist',
                'quickNotes' => [
                    'Open the tours that already show stronger sale or schedule signals.',
                    'Compare beach, domestic and outbound summer routes on one page.',
                    'Best when you already know your month and want a tighter shortlist.',
                ],
                'offerBar' => [
                    $flashMeta['promo_count'] . ' summer tours on sale',
                    $flashMeta['deadline_line'],
                    'Beach, family and outbound routes on one page',
                ],
                'signalBar' => [
                    'Summer tour sale is live',
                    'Domestic and outbound summer tours',
                    'Beach, city and family-friendly departures',
                    'Promotional prices worth checking early',
                    'Shortlist faster for private and group bookings',
                ],
                'dealTiles' => [
                    [
                        'label' => 'Summer deals live',
                        'value' => $flashMeta['promo_count'] . ' tours',
                        'desc' => 'Open the departures with the clearest sale signal first.',
                    ],
                    [
                        'label' => 'Closest deadline',
                        'value' => $flashMeta['deadline_value'],
                        'desc' => 'Earlier booking usually means better schedule choice.',
                    ],
                    [
                        'label' => 'Need a shortlist?',
                        'value' => '1 request',
                        'desc' => 'Send your month and group size to get a tighter shortlist.',
                    ],
                ],
                'promoTitle' => 'Summer sale tours worth opening first',
                'promoDesc' => 'Summer tours with stronger price signals and easier booking timing.',
                'featuredTitle' => 'Summer routes guests compare most',
                'featuredDesc' => 'Popular summer routes for beach trips, family holidays and outbound plans.',
                'editorialKicker' => 'Featured summer deal',
                'editorialCta' => 'See this summer tour',
                'railTitle' => 'Summer departures guests are checking now',
                'railDesc' => 'Compare active deals, prices and timing without opening a long list first.',
                'railLink' => 'See all summer tours',
                'cardCta' => 'See tour details',
                'destinationTitle' => 'Filter summer tours by destination',
                'destinationDesc' => 'Use destination filters to jump straight into the summer routes you care about.',
                'destinationAll' => 'All destinations',
                'domesticTitle' => 'Domestic summer tours',
                'outboundTitle' => 'Outbound summer tours',
                'pricePromoLabel' => 'Sale price',
                'priceDefaultLabel' => 'Tour price',
                'urgencyFallback' => 'Summer deal is live',
                'ctaTitle' => 'Need the right summer route faster?',
                'ctaDesc' => 'Send your destination, group size and travel period. Travel Plus will suggest a tighter summer shortlist.',
                'ctaPrimary' => 'Request summer advice',
                'ctaSecondary' => 'Browse all tours',
            ]
            : [
                'eyebrow' => 'Tour du lịch hè 2026 Travel Plus',
                'title' => 'Tour hè 2026 giá tốt: Tour du lịch, tour gia đình, tour trong nước và nước ngoài',
                'desc' => 'Khám phá các tour du lịch hè 2026 hấp dẫn từ Travel Plus với nhiều lựa chọn tour biển, tour nghỉ dưỡng, tour gia đình, tour đoàn và tour nước ngoài. Cập nhật ưu đãi mới nhất, lịch khởi hành đa dạng và mức giá tốt cho mùa du lịch hè.',
                'primaryCta' => 'Xem tour hè đang sale',
                'secondaryCta' => 'Nhờ Travel Plus tư vấn',
                'flashBarLabel' => 'Ưu đãi tour hè 2026',
                'flashBarMessage' => $flashMeta['bar_message'],
                'flashBarPoints' => [
                    $flashMeta['deadline_line'],
                    'Tour hè trong nước và tour hè nước ngoài khởi hành liên tục',
                    'Phù hợp cho gia đình, nhóm bạn, công ty và đoàn khách riêng',
                ],
                'seasonCues' => [
                    [
                        'icon' => 'bi-brightness-high',
                        'title' => 'Tour hè hot nhất',
                        'desc' => 'Các tour biển, tour nghỉ dưỡng và điểm đến mùa hè được nhiều khách lựa chọn.'
                    ],
                    [
                        'icon' => 'bi-tags',
                        'title' => 'Ưu đãi tour hè hấp dẫn',
                        'desc' => 'Cập nhật giá tốt và chương trình khuyến mãi mới nhất từ Travel Plus.'
                    ],
                    [
                        'icon' => 'bi-people',
                        'title' => 'Đa dạng đối tượng khách',
                        'desc' => 'Phù hợp cho gia đình, nhóm bạn, doanh nghiệp và đoàn khách riêng.'
                    ],
                ],
                'metrics' => [
                    ['value' => $flashMeta['promo_count'], 'label' => 'deal đang mở'],
                    ['value' => count($featuredCollection), 'label' => 'tuyến hè nổi bật'],
                    ['value' => max(1, count($heroLocations)), 'label' => 'khu vực hút khách'],
                ],
                'panelTitle' => 'Vì sao nên đặt tour hè tại Travel Plus?',
                'panelTag' => 'Travel Plus chọn sẵn',
                'quickNotes' => [
                    'Tổng hợp các tour du lịch hè 2026 nổi bật trong nước và quốc tế.',
                    'Dễ dàng so sánh lịch khởi hành, giá tour và điểm đến trên cùng một trang.',
                    'Đội ngũ Travel Plus tư vấn lịch trình phù hợp theo ngân sách và nhu cầu.',
                ],
                'offerBar' => [
                    $flashMeta['promo_count'] . ' tour hè đang có ưu đãi',
                    $flashMeta['deadline_line'],
                    'Tour biển, tour gia đình và tour nước ngoài trên cùng một trang',
                ],
                'signalBar' => [
                    'Tour hè sale đang mở',
                    'Tour hè trong nước và tour hè nước ngoài',
                    'Lịch đẹp cho gia đình, nhóm bạn và đoàn riêng',
                    'Ưu đãi rõ hơn để chốt sớm hơn',
                    'So nhanh giá, lịch và điểm đến ngay trên một trang',
                ],
                'dealTiles' => [
                    [
                        'label' => 'Tour hè đang sale',
                        'value' => $flashMeta['promo_count'] . ' tour',
                        'desc' => 'Đưa các tour hè có giá tốt lên trước để khách thấy ngay.',
                    ],
                    [
                        'label' => 'Hạn gần nhất',
                        'value' => $flashMeta['deadline_value'],
                        'desc' => 'Xem sớm thường dễ giữ được giá và lịch đẹp hơn.',
                    ],
                    [
                        'label' => 'Muốn shortlist nhanh?',
                        'value' => '1 yêu cầu',
                        'desc' => 'Gửi nhanh nhu cầu để nhận nhóm tour phù hợp hơn.',
                    ],
                ],
                'featuredTitle' => 'Tour hè 2026 được khách quan tâm nhiều',
                'featuredDesc' => 'Các tour biển, tour nghỉ dưỡng, tour gia đình và tour nước ngoài được đặt nhiều trong mùa du lịch hè.',
                'promoTitle' => 'Tour hè ưu đãi nổi bật',
                'promoDesc' => 'Danh sách tour hè giá tốt với lịch khởi hành đẹp và ưu đãi hấp dẫn.',
                'editorialKicker' => 'Tour hè nổi bật',
                'editorialCta' => 'Xem tour này',
                'railTitle' => 'Các tour hè khách đang mở nhiều',
                'railDesc' => 'Kéo ngang để so nhanh giá, lịch và hạn ưu đãi.',
                'railLink' => 'Xem tất cả tour hè',
                'cardCta' => 'Xem chi tiết tour',
                'destinationTitle' => 'Chọn tour hè theo điểm đến',
                'destinationDesc' => 'Khám phá các tour du lịch hè theo từng điểm đến nổi bật trong nước và quốc tế.',
                'destinationAll' => 'Tất cả điểm đến',
                'domesticTitle' => 'Tour hè trong nước nổi bật',
                'outboundTitle' => 'Tour hè nước ngoài hấp dẫn',
                'pricePromoLabel' => 'Giá ưu đãi',
                'priceDefaultLabel' => 'Giá tour',
                'urgencyFallback' => 'Ưu đãi đang mở',
                'ctaTitle' => 'Tư vấn tour hè 2026 phù hợp cho bạn',
                'ctaDesc' => 'Gửi điểm đến mong muốn, số lượng khách và thời gian dự kiến. Travel Plus sẽ tư vấn tour hè phù hợp với ngân sách và nhu cầu của bạn trong thời gian sớm nhất.',
                'ctaPrimary' => 'Nhận tư vấn tour hè',
                'ctaSecondary' => 'Xem tất cả tour hè',
            ];

        $path = LocalizedPathCatalog::path('summer', $locale);
        $canonicalUrl = localized_url($path);
        $searchUrl = LocalizedPathCatalog::url('search', $locale);
        $contactUrl = LocalizedPathCatalog::url('contact', $locale);
        $privacyUrl = LocalizedPathCatalog::url('legal.privacy', $locale);
        $termsUrl = LocalizedPathCatalog::url('legal.terms', $locale);
        $recaptchaSiteKey = trim((string) env('recaptcha.siteKey', ''), " \t\n\r\0\x0B\"'");
        $contactFormToken = bin2hex(random_bytes(16));
        session()->set('contact_form_token', $contactFormToken);

        $metaTitle = $locale === 'en'
            ? 'Summer Tours on Sale | Domestic & Outbound Summer Deals | Travel Plus'
            : 'Tour Hè Giá Tốt | Tour Hè Trong Nước, Tour Hè Nước Ngoài | Travel Plus';
        $metaDesc = $locale === 'en'
            ? 'Explore summer tours on sale, including domestic summer tours, outbound summer tours, family trips and group-friendly departures with live prices and schedules.'
            : 'Khám phá tour hè giá tốt, tour hè trong nước, tour hè nước ngoài, tour gia đình và tour nhóm với giá ưu đãi, lịch khởi hành rõ ràng và gợi ý điểm đến dễ chọn hơn.';

        $breadcrumbs = [
            ['label' => lang('Frontend.common.home', [], $locale), 'url' => localized_url('/')],
            ['label' => $locale === 'en' ? 'Summer tours' : 'Tour hè'],
        ];

        return view('summer/index', [
            'copy' => $copy,
            'heroImage' => $heroImage,
            'landingBannerImage' => $landingBannerImage,
            'landingBackdropImage' => $landingBackdropImage,
            'heroLocations' => $heroLocations,
            'primaryTour' => $primaryTour,
            'promoCollection' => $promoCollection,
            'featuredCollection' => $featuredCollection,
            'domesticTours' => array_slice($domesticTours, 0, self::BUCKET_TOUR_LIMIT),
            'outboundTours' => array_slice($outboundTours, 0, self::BUCKET_TOUR_LIMIT),
            'searchUrl' => $searchUrl,
            'contactUrl' => $contactUrl,
            'privacyUrl' => $privacyUrl,
            'termsUrl' => $termsUrl,
            'recaptchaSiteKey' => $recaptchaSiteKey,
            'contact_form_token' => $contactFormToken,
            'breadcrumbs' => $breadcrumbs,
            'meta_title' => $metaTitle,
            'meta_desc' => $metaDesc,
            'canonical_url' => $canonicalUrl,
            'meta_image' => $heroImage,
            'meta_image_alt' => $copy['title'],
            'alternate_links' => [
                ['hreflang' => 'vi', 'href' => base_url(LocalizedPathCatalog::path('summer', 'vi'))],
                ['hreflang' => 'en', 'href' => base_url('en/' . LocalizedPathCatalog::path('summer', 'en'))],
                ['hreflang' => 'x-default', 'href' => base_url(LocalizedPathCatalog::path('summer', 'vi'))],
            ],
            'schema_graph' => [
                $seo->organizationSchema(),
                $seo->breadcrumbSchema($breadcrumbs, $canonicalUrl),
                $seo->webpageSchema($metaTitle, $metaDesc, $canonicalUrl),
                $seo->itemListSchema($copy['featuredTitle'], $canonicalUrl, $featuredCollection, 'Product'),
                $seo->itemListSchema($copy['promoTitle'], $canonicalUrl, $promoCollection, 'Product'),
            ],
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $promoCollection
     * @param array<int, array<string, mixed>> $featuredCollection
     * @return array<string, mixed>
     */
    private function buildFlashMeta(array $promoCollection, array $featuredCollection, string $locale): array
    {
        $promoTours = array_values(array_filter(
            $promoCollection,
            static fn(array $tour): bool => ! empty($tour['promotion']['is_active'])
        ));

        if ($promoTours === []) {
            $promoTours = array_values(array_filter(
                $featuredCollection,
                static fn(array $tour): bool => ! empty($tour['promotion']['is_active'])
            ));
        }

        $promoCount = count($promoTours !== [] ? $promoTours : $promoCollection);
        $deadlines = [];

        foreach ($promoTours as $tour) {
            $endsAt = trim((string) ($tour['promotion']['ends_at'] ?? ''));
            $timestamp = $endsAt === '' ? false : strtotime($endsAt);
            if ($timestamp === false) {
                continue;
            }

            $deadlines[] = $timestamp;
        }

        sort($deadlines);
        $nearestDeadline = $deadlines[0] ?? null;
        $deadlineValue = $nearestDeadline !== null
            ? ($locale === 'en' ? date('M d', $nearestDeadline) : date('d/m', $nearestDeadline))
            : ($locale === 'en' ? 'Updated daily' : 'Cập nhật liên tục');

        $deadlineLine = $nearestDeadline !== null
            ? ($locale === 'en'
                ? 'Nearest promotion ends ' . date('M d', $nearestDeadline)
                : 'Ưu đãi gần nhất đến ' . date('d/m', $nearestDeadline))
            : ($locale === 'en'
                ? 'Promotions update continuously across the page'
                : 'Ưu đãi trên trang được cập nhật liên tục');

        $barMessage = $locale === 'en'
            ? sprintf('%d live summer deals are being pushed to the front for faster booking decisions.', $promoCount)
            : sprintf('%d deal hè đang được đẩy lên trước để khách chốt nhanh hơn.', $promoCount);

        return [
            'promo_count' => $promoCount,
            'deadline_value' => $deadlineValue,
            'deadline_line' => $deadlineLine,
            'bar_message' => $barMessage,
        ];
    }
}
