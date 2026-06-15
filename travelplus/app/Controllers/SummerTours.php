<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Services\SeoService;
use App\Services\TourCatalogService;

class SummerTours extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        $tourService = new TourCatalogService();
        $seo = new SeoService();

        $promotionalTours = $tourService->getPromotionalTours($locale, 6);
        $featuredTours = $tourService->getFeaturedTours($locale, 8);
        $homeTours = $tourService->getHomeTours($locale, 8);

        $summerHighlights = $promotionalTours !== [] ? $promotionalTours : $featuredTours;
        if ($summerHighlights === []) {
            $summerHighlights = $homeTours;
        }

        $primaryTour = $summerHighlights[0] ?? $homeTours[0] ?? null;
        $landingBannerImage = base_url('assets/images/landing/summer/Banner_Landing.png');
        $landingBackdropImage = base_url('assets/images/landing/summer/Background_Landing_W1920xH5000px.png');
        $heroImage = $landingBannerImage;

        $featuredCollection = array_slice($featuredTours !== [] ? $featuredTours : $homeTours, 0, 6);
        $promoCollection = array_slice($summerHighlights, 0, 6);
        $flashMeta = $this->buildFlashMeta($promoCollection, $featuredCollection, $locale);

        $domesticTours = array_values(array_filter($featuredCollection, static fn(array $tour): bool => (string) ($tour['tour_type'] ?? '') === 'inbound'));
        $outboundTours = array_values(array_filter($featuredCollection, static fn(array $tour): bool => (string) ($tour['tour_type'] ?? '') !== 'inbound'));

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
                'eyebrow' => 'Travel Plus Summer Escape',
                'title' => 'Choose the right summer route faster with beach, city and family departures already grouped clearly.',
                'desc' => 'Use the campaign banner as the visual lead, then compare routes, prices and promotional departures without dropping into a normal catalog layout too early.',
                'primaryCta' => 'See summer tours',
                'secondaryCta' => 'Get help shortlisting',
                'flashBarLabel' => 'Summer flash sale',
                'flashBarMessage' => $flashMeta['bar_message'],
                'flashBarPoints' => [
                    $flashMeta['deadline_line'],
                    'Beach, city and family-friendly routes',
                    'Shortlist support for family and group bookings',
                ],
                'seasonCues' => [
                    ['icon' => 'bi-brightness-high', 'title' => 'Sunny escapes', 'desc' => 'Beach routes and bright city breaks that actually read like summer.'],
                    ['icon' => 'bi-water', 'title' => 'Sea-first mood', 'desc' => 'A lighter aqua palette and warmer imagery so the page feels seasonal, not generic.'],
                    ['icon' => 'bi-people', 'title' => 'Easy for groups', 'desc' => 'Families, friend groups and company teams can compare the shortlist faster.'],
                ],
                'metrics' => [
                    ['value' => $flashMeta['promo_count'], 'label' => 'live deals'],
                    ['value' => count($featuredCollection), 'label' => 'summer routes'],
                    ['value' => max(1, count($heroLocations)), 'label' => 'popular zones'],
                ],
                'panelTitle' => 'Why act early this week',
                'panelTag' => 'Fast-booking view',
                'quickNotes' => [
                    'Promotional tours are shown on real product cards, not hidden inside generic listings.',
                    'Domestic and outbound routes stay on one campaign page for quicker comparison.',
                    'Some offers have live promotion end dates, so priority goes to departures worth opening now.',
                ],
                'offerBar' => [
                    $flashMeta['promo_count'] . ' summer deals live now',
                    $flashMeta['deadline_line'],
                    'Sea, city and family moods on one page',
                ],
                'signalBar' => [
                    'Flash sale summer departures',
                    'Beach mood with cleaner route visuals',
                    'Promotional cards updated from live catalog data',
                    'Family, friend and company group friendly',
                    'Move from browsing to booking faster',
                ],
                'dealTiles' => [
                    [
                        'label' => 'Deals live now',
                        'value' => $flashMeta['promo_count'] . ' tours',
                        'desc' => 'Real promotional tours pulled to the front so the page reads like an active campaign, not a normal listing.',
                    ],
                    [
                        'label' => 'Nearest deadline',
                        'value' => $flashMeta['deadline_value'],
                        'desc' => 'Some promotional departures already carry an end date, so earlier comparison matters more here.',
                    ],
                    [
                        'label' => 'Need a faster shortlist?',
                        'value' => '1 request',
                        'desc' => 'Share your month, budget and group size to get a narrower summer shortlist from Travel Plus.',
                    ],
                ],
                'promoTitle' => 'Flash deals worth opening first',
                'promoDesc' => 'Sale-led departures, fast-moving schedules and the tours that should take the first click in summer browsing.',
                'featuredTitle' => 'Best-looking summer routes',
                'featuredDesc' => 'The layout stays campaign-driven, but the color, spacing and imagery now feel more like a summer collection than a standard promotion page.',
                'editorialKicker' => 'Lead summer offer',
                'editorialCta' => 'Hold this deal',
                'railTitle' => 'Deals people are checking now',
                'railDesc' => 'A horizontal offer rail for quick comparisons, stronger pricing hierarchy and clearer action buttons.',
                'railLink' => 'See all summer tours',
                'cardCta' => 'View this deal',
                'destinationTitle' => 'Choose a destination before choosing a tour',
                'destinationDesc' => 'Summer tours here are intentionally limited, so this filter helps visitors jump straight to the routes that actually have departures on the page.',
                'destinationAll' => 'All destinations',
                'domesticTitle' => 'Domestic summer picks',
                'outboundTitle' => 'Outbound summer picks',
                'pricePromoLabel' => 'Promotional price',
                'priceDefaultLabel' => 'Summer tour price',
                'urgencyFallback' => 'Deal is live',
                'ctaTitle' => 'Already know your month and budget?',
                'ctaDesc' => 'Send the expected travel month, group size and budget band. Travel Plus can cut the noise and suggest a tighter summer shortlist quickly.',
                'ctaPrimary' => 'Talk to Travel Plus',
                'ctaSecondary' => 'Browse all tours',
            ]
            : [
                'eyebrow' => 'Bộ sưu tập hè Travel Plus',
                'title' => 'Chọn nhanh tour hè đúng gu, đúng lịch và đúng kiểu trải nghiệm ngay từ màn hình đầu tiên.',
                'desc' => 'Banner dẫn cảm xúc mùa hè, còn phần dưới gom lại tour biển, tour đổi gió, lịch đẹp cho gia đình và nhóm bạn để khách xem nhanh mà vẫn dễ chốt.',
                'primaryCta' => 'Xem tour hè',
                'secondaryCta' => 'Nhờ Travel Plus shortlist',
                'flashBarLabel' => 'Flash sale mùa hè',
                'flashBarMessage' => $flashMeta['bar_message'],
                'flashBarPoints' => [
                    $flashMeta['deadline_line'],
                    'Biển, nghỉ dưỡng và tour gia đình',
                    'Chốt nhanh hơn cho gia đình và đoàn nhóm',
                ],
                'seasonCues' => [
                    ['icon' => 'bi-brightness-high', 'title' => 'Đi trốn nắng', 'desc' => 'Nhìn vào là ra chất hè hơn: sáng, thoáng, có cảm giác muốn đi ngay.'],
                    ['icon' => 'bi-water', 'title' => 'Biển và nghỉ dưỡng', 'desc' => 'Sắc xanh biển, cát sáng và tông coral giúp page đỡ nặng, đỡ giống campaign sale thường.'],
                    ['icon' => 'bi-people', 'title' => 'Dễ chốt cho nhóm', 'desc' => 'Gia đình, nhóm bạn và đoàn doanh nghiệp đều có thể shortlist nhanh hơn trên cùng một trang.'],
                ],
                'metrics' => [
                    ['value' => $flashMeta['promo_count'], 'label' => 'deal đang mở'],
                    ['value' => count($featuredCollection), 'label' => 'tuyến hè nổi bật'],
                    ['value' => max(1, count($heroLocations)), 'label' => 'khu vực hút khách'],
                ],
                'panelTitle' => 'Lý do nên xem sớm tuần này',
                'panelTag' => 'Tư duy chốt nhanh',
                'quickNotes' => [
                    'Tour ưu đãi được kéo lên trước bằng dữ liệu tour thật, không phải trang danh sách thường đổi màu.',
                    'Tour trong nước và tour nước ngoài nằm trên cùng một campaign page để so nhanh hơn.',
                    'Một số deal có hạn ưu đãi cụ thể, nên giá đẹp và lịch đẹp đáng xem sớm hơn bình thường.',
                ],
                'offerBar' => [
                    $flashMeta['promo_count'] . ' tour hè đang mở deal',
                    $flashMeta['deadline_line'],
                    'Biển, thành phố mát và tour nhóm trên cùng một trang',
                ],
                'signalBar' => [
                    'Flash sale tour hè đang mở',
                    'Không khí biển và nghỉ dưỡng rõ hơn',
                    'Deal thật lấy trực tiếp từ catalog tour',
                    'Ưu tiên tour dễ chốt nhanh trong mùa cao điểm',
                    'So nhanh tour gia đình, nhóm bạn và đoàn nhóm',
                    'Từ xem tour sang giữ lịch nhanh hơn',
                ],
                'dealTiles' => [
                    [
                        'label' => 'Deal đang mở',
                        'value' => $flashMeta['promo_count'] . ' tour',
                        'desc' => 'Đẩy các tour đang có ưu đãi lên trước để khách vào là thấy ngay phần đáng mở đầu tiên.',
                    ],
                    [
                        'label' => 'Hạn gần nhất',
                        'value' => $flashMeta['deadline_value'],
                        'desc' => 'Một số lịch hè có mốc ưu đãi cụ thể, nên càng để lâu càng khó giữ được mức giá đang đẹp.',
                    ],
                    [
                        'label' => 'Muốn shortlist nhanh?',
                        'value' => '1 yêu cầu',
                        'desc' => 'Gửi tháng đi, số người và ngân sách để Travel Plus gom lại nhóm tour phù hợp, đỡ phải lọc rời rạc.',
                    ],
                ],
                'promoTitle' => 'Các deal hè nên mở trước',
                'promoDesc' => 'Những tour đang sale, lịch đẹp và đáng bấm xem ngay khi khách vừa vào trang.',
                'featuredTitle' => 'Những tuyến hè đang hút khách',
                'featuredDesc' => 'Giữ lực bán của campaign page, nhưng phần nhìn phải ra mùa hè hơn: sáng hơn, xanh hơn, có cảm giác biển và nghỉ dưỡng rõ hơn.',
                'editorialKicker' => 'Deal dẫn đầu mùa hè',
                'editorialCta' => 'Giữ deal này',
                'railTitle' => 'Các deal khách đang xem nhiều',
                'railDesc' => 'Kéo ngang để so nhanh giá, hạn ưu đãi và hành trình mà không phải đọc lại một trang danh sách dài.',
                'railLink' => 'Xem tất cả tour hè',
                'cardCta' => 'Xem deal này',
                'destinationTitle' => 'Chọn điểm đến trước khi chọn tour hè',
                'destinationDesc' => 'Tour hè trên trang này không quá nhiều, nên nên lọc nhanh theo điểm đến để khách vào là thấy đúng nhóm tour đang quan tâm.',
                'destinationAll' => 'Tất cả điểm đến',
                'domesticTitle' => 'Tour hè trong nước',
                'outboundTitle' => 'Tour hè nước ngoài',
                'pricePromoLabel' => 'Giá ưu đãi',
                'priceDefaultLabel' => 'Giá tour hè',
                'urgencyFallback' => 'Deal đang mở',
                'ctaTitle' => 'Đã có tháng đi và ngân sách rồi?',
                'ctaDesc' => 'Gửi nhanh thời gian dự kiến, số người và mức chi. Travel Plus sẽ gợi ý nhóm tour phù hợp hơn để bạn chốt sớm thay vì xem quá nhiều lựa chọn rời rạc.',
                'ctaPrimary' => 'Liên hệ Travel Plus',
                'ctaSecondary' => 'Xem toàn bộ tour',
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
            ? 'Summer Flash Sale Tours | Travel Plus'
            : 'Flash Sale Tour Hè | Travel Plus';
        $metaDesc = $locale === 'en'
            ? 'Summer campaign page with live promotional tours, stronger CTA hierarchy and better urgency cues for faster booking decisions.'
            : 'Trang flash sale tour hè với tour ưu đãi thật, CTA mạnh hơn và tín hiệu chốt sớm rõ hơn để khách quyết định nhanh.';

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
            'domesticTours' => array_slice($domesticTours, 0, 3),
            'outboundTours' => array_slice($outboundTours, 0, 3),
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
            static fn(array $tour): bool => !empty($tour['promotion']['is_active'])
        ));

        if ($promoTours === []) {
            $promoTours = array_values(array_filter(
                $featuredCollection,
                static fn(array $tour): bool => !empty($tour['promotion']['is_active'])
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
