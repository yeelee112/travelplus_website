<?php

namespace App\Services;

use App\Data\AboutPageContent;
use App\Data\LegalPageCatalog;
use App\Data\MicePageContent;
use App\Data\OfficeLocationCatalog;
use App\Data\ServicePageCatalog;
use App\Data\VisaPageContent;
use CodeIgniter\Database\BaseConnection;
use Config\TourAdvisory;
use Throwable;

class WebsiteKnowledgeService
{
    private BaseConnection $db;
    private TourAdvisory $tourAdvisory;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->tourAdvisory = config(TourAdvisory::class);
    }

    /**
     * @param list<array{role: string, text: string}> $history
     * @param array<string, mixed> $chatState
     * @return array<string, mixed>
     */
    public function hydrateChatStateFromHistory(string $locale, array $history, array $chatState = []): array
    {
        if ($this->getLastMatchedTourFromState($locale, $chatState) !== null) {
            return $chatState;
        }

        $recentUserText = [];
        foreach (array_slice($history, -8) as $item) {
            if (($item['role'] ?? '') !== 'user') {
                continue;
            }

            $text = trim((string) ($item['text'] ?? ''));
            if ($text !== '') {
                $recentUserText[] = $text;
            }
        }

        if ($recentUserText !== []) {
            try {
                $matches = $this->findMatchingTours($locale, implode("\n", $recentUserText), 1);
                if ($matches !== []) {
                    return $this->buildTourChatState($matches[0], $locale);
                }
            } catch (Throwable) {
                // History hydration is best-effort. Normal intent matching still runs below.
            }
        }

        foreach (array_reverse(array_slice($history, -8)) as $item) {
            if (($item['role'] ?? '') !== 'assistant') {
                continue;
            }

            $text = trim((string) ($item['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            try {
                $matches = $this->findMatchingTours($locale, $text, 1);
                if ($matches !== []) {
                    return $this->buildTourChatState($matches[0], $locale);
                }
            } catch (Throwable) {
                continue;
            }
        }

        return $chatState;
    }

    /**
     * @return array{summary: string, sources: list<array{title: string, url: string}>}
     */
    public function getRelevantContext(string $locale, string $question, int $limit = 8): array
    {
        $chunks = array_merge(
            $this->getTourIntentChunks($locale, $question),
            $this->getOperationalChunks($locale),
            $this->getStaticChunks($locale),
            $this->getTourChunks($locale),
            $this->getBlogChunks($locale)
        );

        $scored = [];

        foreach ($chunks as $chunk) {
            $score = $this->scoreChunk($question, $chunk['text']);

            if ($score <= 0) {
                continue;
            }

            $chunk['score'] = $score;
            $scored[] = $chunk;
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        $selected = array_slice($scored, 0, max(1, $limit));

        if ($selected === []) {
            $selected = array_slice($chunks, 0, min(4, count($chunks)));
        }

        $summaryParts = [];
        $sources = [];

        foreach ($selected as $index => $chunk) {
            $summaryParts[] = '[' . ($index + 1) . '] ' . $chunk['title'] . "\n" . $chunk['text'];
            $sources[] = [
                'title' => $chunk['title'],
                'url' => $chunk['url'],
            ];
        }

        return [
            'summary' => implode("\n\n", $summaryParts),
            'sources' => $sources,
        ];
    }

    /**
     * @param array<string, mixed> $chatState
     * @return array<string, mixed>|null
     */
    public function getStructuredFacts(string $locale, string $question, array $chatState = []): ?array
    {
        if ($this->looksLikeCompanyStrengthQuestion($question)) {
            return $this->buildStructuredCompanyStrengthFacts($locale);
        }

        if ($this->looksLikePaymentQuestion($question)) {
            return $this->buildStructuredPaymentFacts($locale);
        }

        if ($this->looksLikeCustomTourQuestion($question)) {
            return $this->buildStructuredCustomTourFacts($locale);
        }

        if ($this->looksLikeHotelQuestion($question)) {
            return $this->buildStructuredServiceFacts($locale, 'hotels', 'hotel_service');
        }

        if ($this->looksLikeTransportQuestion($question)) {
            return $this->buildStructuredServiceFacts($locale, 'transport', 'transport_service');
        }

        if ($this->looksLikeVisaQuestion($question)) {
            return $this->buildStructuredVisaFacts($locale, $question);
        }

        if ($this->referencesCurrentTour($question)) {
            $selectedTour = $this->getLastMatchedTourFromState($locale, $chatState);

            if ($selectedTour !== null) {
                return $this->buildStructuredTourDetailFacts($locale, $question, $selectedTour);
            }
        }

        if ($this->looksLikeMiceQuestion($question)) {
            return $this->buildStructuredMiceFacts($locale);
        }

        if ($this->looksLikeGeneralTourAvailabilityQuestion($question)) {
            $publishedTours = $this->getPublishedTours($locale, 5);

            if ($publishedTours !== []) {
                return $this->buildStructuredTourListFacts($locale, $publishedTours, 'general_availability', $question);
            }
        }

        $destinationTripQuestion = $this->looksLikeDestinationTripPlanningQuestion($question);

        if (! $this->looksLikeTourQuestion($question) && ! $this->referencesCurrentTour($question) && ! $destinationTripQuestion) {
            return null;
        }

        if ($this->looksLikeUpcomingDepartureQuestion($question)) {
            $upcomingTours = $this->getUpcomingDepartureTours($locale, 3);

            if ($upcomingTours !== []) {
                return $this->buildStructuredTourListFacts($locale, $upcomingTours, 'upcoming_departures', $question);
            }
        }

        $selectedTour = null;
        $matches = [];
        $preferLastTour = $this->shouldPreferLastTourContext($locale, $question, $chatState);

        if ($preferLastTour) {
            $selectedTour = $this->getLastMatchedTourFromState($locale, $chatState);

            if ($selectedTour !== null) {
                $matches = [$selectedTour];
            }
        }

        if ($matches === []) {
            try {
                $matches = $this->findMatchingTours($locale, $question, 3);
            } catch (Throwable $exception) {
                $matches = [];
            }
        }

        if ($matches !== []) {
            $selectedTour = $matches[0];
        } elseif ($this->referencesCurrentTour($question)) {
            $selectedTour = $this->getLastMatchedTourFromState($locale, $chatState);

            if ($selectedTour !== null) {
                $matches = [$selectedTour];
            }
        }

        if ($selectedTour === null) {
            if ($destinationTripQuestion) {
                return $this->buildStructuredDestinationTripConsultationFacts($locale, $question);
            }

            return null;
        }

        if (
            $this->looksLikeTourContentQuestion($question)
            || $this->looksLikeDestinationListQuestion($question)
            || $this->looksLikeTourPriceQuestion($question)
            || $this->looksLikeTourDepartureQuestion($question)
            || $this->referencesCurrentTour($question)
        ) {
            return $this->buildStructuredTourDetailFacts($locale, $question, $selectedTour);
        }

        return $this->buildStructuredTourListFacts($locale, $matches, 'availability', $question);
    }

    /**
     * @param array<string, mixed> $chatState
     * @return array<string, mixed>|null
     */
    public function getReferenceFacts(string $locale, string $question, array $chatState = []): ?array
    {
        if ($this->looksLikeVisaQuestion($question) && $this->looksLikeVisaProcessingTimeQuestion($question)) {
            $visaFacts = $this->buildStructuredVisaFacts($locale, $question);
            $referenceRegion = $this->looksLikeSchengenVisaQuestion($question) ? 'schengen' : 'general';
            $sources = is_array($visaFacts['sources'] ?? null) ? $visaFacts['sources'] : [];

            if ($referenceRegion === 'schengen') {
                array_unshift($sources, [
                    'title' => $locale === 'en'
                        ? 'European Commission - Applying for a Schengen visa'
                        : 'Ủy ban Châu Âu - Nộp hồ sơ visa Schengen',
                    'url' => 'https://home-affairs.ec.europa.eu/policies/schengen/visa-policy/applying-schengen-visa_en',
                ]);
            }

            return [
                'type' => 'reference_visa_timeline',
                'intent' => 'reference_visa_timeline',
                'reference_topic' => $this->extractReferenceTopic($question),
                'reference_region' => $referenceRegion,
                'website_facts' => $visaFacts,
                'sources' => $sources,
            ];
        }

        if ($this->looksLikeTravelReferenceQuestion($question)) {
            return [
                'type' => 'reference_travel_general',
                'intent' => 'reference_travel_general',
                'reference_topic' => $this->extractReferenceTopic($question),
                'sources' => [],
            ];
        }

        return null;
    }

    /**
     * @return array{message: string, sources: list<array{title: string, url: string}>}|null
     */
    public function getDirectAnswer(string $locale, string $question): ?array
    {
        return $this->getDirectTourAnswer($locale, $question);
    }

    /**
     * @return list<array{title: string, text: string, url: string}>
     */
    private function getTourIntentChunks(string $locale, string $question): array
    {
        if (! $this->looksLikeTourQuestion($question)) {
            return [];
        }

        try {
            $matches = $this->findMatchingTours($locale, $question, 5);

            if ($matches === []) {
                return [];
            }

            $lines = [];

            foreach ($matches as $tour) {
                $lineParts = [$tour['title']];

                if ($tour['departure'] !== '') {
                    $lineParts[] = ($locale === 'en' ? 'Departure' : 'Khởi hành') . ': ' . $tour['departure'];
                }

                if ($tour['price_label'] !== '') {
                    $lineParts[] = ($locale === 'en' ? 'Price from' : 'Giá từ') . ': ' . $tour['price_label'];
                }

                if ($tour['duration_label'] !== '') {
                    $lineParts[] = ($locale === 'en' ? 'Duration' : 'Thời lượng') . ': ' . $tour['duration_label'];
                }

                $lines[] = implode(' | ', $lineParts);
            }

            return [[
                'title' => $locale === 'en' ? 'Matching tours' : 'Các tour phù hợp',
                'text' => implode("\n", $lines),
                'url' => $matches[0]['url'] ?? $this->makeLocalizedUrl($locale === 'en' ? 'tour-search' : 'tim-kiem-tour', $locale),
            ]];
        } catch (Throwable $exception) {
            return [];
        }
    }

    /**
     * @return array{message: string, sources: list<array{title: string, url: string}>}|null
     */
    private function getDirectTourAnswer(string $locale, string $question): ?array
    {
        if (! $this->looksLikeTourQuestion($question)) {
            return null;
        }

        try {
            $matches = $this->findMatchingTours($locale, $question, 5);

            if ($matches === []) {
                return null;
            }

            if ($this->looksLikeTourContentQuestion($question) || $this->looksLikeDestinationListQuestion($question)) {
                $topMatch = $matches[0] ?? null;

                if ($topMatch === null) {
                    return null;
                }

                $tourService = new TourCatalogService();
                $detail = $tourService->findTourBySlug($locale, $topMatch['slug'], $topMatch['tour_type']) ?? [];

                if ($detail === []) {
                    return null;
                }

                $intro = $locale === 'en'
                    ? 'The most relevant tour on the website is: ' . $topMatch['title']
                    : 'Tour phù hợp nhất trên website là: ' . $topMatch['title'];

                $overview = trim($this->stripHtml((string) ($detail['overview'] ?? $detail['short_description'] ?? '')));
                $overview = $this->summarizeText($overview, 260, 2);
                $itineraryDays = is_array($detail['itinerary_days'] ?? null) ? $detail['itinerary_days'] : [];
                $routeHighlights = $this->extractRouteStops($itineraryDays);
                $attractionHighlights = $this->extractAttractionHighlights($itineraryDays);

                if ($this->looksLikeDestinationListQuestion($question)) {
                    $routeText = $routeHighlights !== []
                        ? implode(', ', array_slice($routeHighlights, 0, 8))
                        : '';

                    $attractionText = $attractionHighlights !== []
                        ? implode(', ', array_slice($attractionHighlights, 0, 8))
                        : '';

                    $messageParts = [
                        $locale === 'en'
                            ? 'The main destinations shown in this tour are:'
                            : 'Tour này đi qua các điểm đến chính sau:',
                    ];

                    if ($routeText !== '') {
                        $messageParts[] = ($locale === 'en' ? 'Route:' : 'Tuyến điểm:') . ' ' . $routeText . '.';
                    }

                    if ($attractionText !== '') {
                        $messageParts[] = ($locale === 'en' ? 'Notable attractions:' : 'Điểm tham quan nổi bật:') . ' ' . $attractionText . '.';
                    }

                    $messageParts[] = $locale === 'en'
                        ? 'You can open the tour details to review the full itinerary.'
                        : 'Bạn có thể mở chi tiết tour để xem đầy đủ lịch trình.';

                    return [
                        'message' => implode("\n\n", array_filter($messageParts)),
                        'sources' => [[
                            'title' => $topMatch['title'],
                            'url' => $topMatch['url'],
                        ]],
                    ];
                }

                $facts = [];

                if ($topMatch['departure'] !== '') {
                    $facts[] = $locale === 'en'
                        ? 'Departure: ' . $topMatch['departure']
                        : 'Khởi hành: ' . $topMatch['departure'];
                }

                if ($topMatch['price_label'] !== '') {
                    $facts[] = $locale === 'en'
                        ? 'Price from: ' . $topMatch['price_label']
                        : 'Giá từ: ' . $topMatch['price_label'];
                }

                if ($topMatch['duration_label'] !== '') {
                    $facts[] = $locale === 'en'
                        ? 'Duration: ' . $topMatch['duration_label']
                        : 'Thời lượng: ' . $topMatch['duration_label'];
                }

                $messageParts = [$intro];

                if ($overview !== '') {
                    $messageParts[] = $overview;
                }

                if ($routeHighlights !== []) {
                    $messageParts[] = ($locale === 'en' ? 'Main route:' : 'Hành trình chính:') . ' ' . implode(' - ', array_slice($routeHighlights, 0, 6)) . '.';
                }

                if ($attractionHighlights !== []) {
                    $messageParts[] = ($locale === 'en' ? 'Highlights:' : 'Điểm nổi bật:') . "\n- " . implode("\n- ", array_slice($attractionHighlights, 0, 5));
                }

                if ($facts !== []) {
                    $messageParts[] = implode(' | ', $facts);
                }

                $messageParts[] = $locale === 'en'
                    ? 'You can open the tour details to review the full itinerary and booking information.'
                    : 'Bạn có thể mở chi tiết tour để xem đầy đủ lịch trình và thông tin đặt chỗ.';

                return [
                    'message' => implode("\n\n", array_filter($messageParts)),
                    'sources' => [[
                        'title' => $topMatch['title'],
                        'url' => $topMatch['url'],
                    ]],
                ];
            }

            $lines = [];
            $sources = [];

            foreach (array_slice($matches, 0, 3) as $tour) {
                $title = $tour['title'];
                $departure = $tour['departure'];
                $priceLabel = $tour['price_label'];
                $duration = $tour['duration_label'];
                $link = $tour['url'];

                if ($locale === 'en') {
                    $line = '- ' . $title;
                    if ($departure !== '') {
                        $line .= ' | Departure: ' . $departure;
                    }
                    if ($priceLabel !== '') {
                        $line .= ' | Price from: ' . $priceLabel;
                    }
                    if ($duration !== '') {
                        $line .= ' | Duration: ' . $duration;
                    }
                } else {
                    $line = '- ' . $title;
                    if ($departure !== '') {
                        $line .= ' | Khởi hành: ' . $departure;
                    }
                    if ($priceLabel !== '') {
                        $line .= ' | Giá từ: ' . $priceLabel;
                    }
                    if ($duration !== '') {
                        $line .= ' | Thời lượng: ' . $duration;
                    }
                }

                $lines[] = $line;

                if ($link !== '') {
                    $sources[] = [
                        'title' => $title,
                        'url' => $link,
                    ];
                }
            }

            if ($lines === []) {
                return null;
            }

            $intro = $locale === 'en'
                ? 'The website currently has these matching tours:'
                : 'Hiện website có các tour phù hợp với yêu cầu của bạn:';

            $outro = $locale === 'en'
                ? 'You can open the tour details to review the itinerary and booking information.'
                : 'Bạn có thể mở chi tiết tour để xem lịch trình và thông tin đặt chỗ.';
            return [
                'message' => $intro . "\n\n" . implode("\n", $lines) . "\n\n" . $outro,
                'sources' => $sources,
            ];
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $tour
     * @return array<string, mixed>|null
     */
    private function buildStructuredTourDetailFacts(string $locale, string $question, array $tour): ?array
    {
        $tourService = new TourCatalogService();
        $detail = $tourService->findTourBySlug($locale, (string) ($tour['slug'] ?? ''), (string) ($tour['tour_type'] ?? '')) ?? [];

        if ($detail === []) {
            return null;
        }

        $itineraryDays = is_array($detail['itinerary_days'] ?? null) ? $detail['itinerary_days'] : [];
        $routeStops = $this->extractRouteStops($itineraryDays);
        if ($routeStops === []) {
            $routeStops = $this->extractRouteStopsFromTourTitle((string) ($tour['title'] ?? $detail['title'] ?? ''));
        }
        $attractions = $this->extractAttractionHighlights($itineraryDays);
        $overview = trim($this->stripHtml((string) ($detail['overview'] ?? $detail['short_description'] ?? '')));

        $itineraryHighlights = [];

        foreach ($itineraryDays as $day) {
            $title = trim(html_entity_decode((string) ($day['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $description = trim(html_entity_decode($this->stripHtml((string) ($day['description'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($title === '' && $description === '') {
                continue;
            }

            $highlight = [
                'day' => (int) ($day['day_number'] ?? 0),
                'title' => $title,
                'summary' => $this->summarizeMeaningfulText($description, 180),
            ];

            if ($highlight['summary'] === '' && $title === '') {
                continue;
            }

            $itineraryHighlights[] = $highlight;

            if (count($itineraryHighlights) >= 6) {
                break;
            }
        }

        $intent = 'itinerary';

        if ($this->looksLikeDestinationListQuestion($question)) {
            $intent = 'destinations';
        } elseif ($this->looksLikeTourHighlightQuestion($question)) {
            $intent = 'highlights';
        } elseif ($this->looksLikeTourPriceQuestion($question)) {
            $intent = 'price';
        } elseif ($this->looksLikeTourDepartureQuestion($question)) {
            $intent = 'departure';
        }

        $advisory = $this->buildTourAdvisoryProfile(
            $locale,
            $tour,
            $detail,
            $routeStops,
            $attractions,
            $itineraryHighlights,
            $overview,
            $question
        );
        $fit = $this->buildTourFitProfile($locale, $tour, $advisory, $question);
        $selectedTour = $this->formatTourFactItem($tour, $locale);
        $selectedTour['fit'] = $fit;

        return [
            'type' => 'tour_detail',
            'intent' => $intent,
            'selected_tour' => $selectedTour,
            'selected_fit' => $fit,
            'tour' => [
                'title' => (string) ($tour['title'] ?? ''),
                'url' => (string) ($tour['url'] ?? ''),
                'departure_date' => (string) ($tour['departure_date'] ?? ''),
                'departure' => (string) ($tour['departure'] ?? ''),
                'price_label' => (string) ($tour['price_label'] ?? ''),
                'duration_label' => (string) ($tour['duration_label'] ?? ''),
                'overview' => $this->summarizeText($overview, 320, 3),
                'route_stops' => array_slice($routeStops, 0, 12),
                'attraction_highlights' => array_slice($attractions, 0, 12),
                'itinerary_highlights' => $itineraryHighlights,
                'advisory' => $advisory,
                'fit' => $fit,
            ],
            'sources' => [[
                'title' => (string) ($tour['title'] ?? ''),
                'url' => (string) ($tour['url'] ?? ''),
            ]],
            'chat_state' => $this->buildTourChatState($tour, $locale),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStructuredCompanyStrengthFacts(string $locale): array
    {
        if ($locale === 'en') {
            return [
                'type' => 'company_strength',
                'intent' => 'company_strength',
                'company_strength' => [
                    'summary' => 'Travel Plus is strongest in professional MICE programs for businesses, with a notable advantage in healthcare and pharmaceutical events. The company also supports outbound tours, domestic tours, visa preparation and related travel services as one coordinated operating team.',
                    'strengths' => [
                        [
                            'title' => 'Corporate MICE organization',
                            'text' => 'End-to-end conferences, seminars, incentive travel, team building, gala dinners and customer events built around each business objective.',
                        ],
                        [
                            'title' => 'Healthcare and pharmaceutical MICE',
                            'text' => 'Experience with medical conferences, pharmaceutical meetings, symposiums, congresses, doctor groups, speakers, rooming lists, visas, transfers and onsite coordination.',
                        ],
                        [
                            'title' => 'Custom tour and travel operation',
                            'text' => 'Outbound tours, domestic tours and tailor-made itineraries with clear schedules, budget control and practical support before and during the trip.',
                        ],
                        [
                            'title' => 'Connected travel services',
                            'text' => 'Visa consultation, flights, hotels, transport and travel add-ons can be coordinated together instead of handled as isolated services.',
                        ],
                    ],
                ],
                'sources' => [
                    [
                        'title' => 'Professional MICE organization for businesses',
                        'url' => $this->makeLocalizedUrl('dich-vu-mice', $locale),
                    ],
                    [
                        'title' => 'About Travel Plus',
                        'url' => $this->makeLocalizedUrl('ve-chung-toi', $locale),
                    ],
                ],
            ];
        }

        return [
            'type' => 'company_strength',
            'intent' => 'company_strength',
            'company_strength' => [
                'summary' => 'Thế mạnh của Travel Plus là tổ chức MICE chuyên nghiệp cho doanh nghiệp, nổi bật nhất ở các chương trình y dược/bác sĩ. Ngoài ra Travel Plus còn hỗ trợ tour nước ngoài, tour trong nước, visa và các dịch vụ du lịch đi kèm theo một đầu mối vận hành.',
                'strengths' => [
                    [
                        'title' => 'MICE doanh nghiệp',
                        'text' => 'Tổ chức hội nghị, hội thảo, incentive, team building, gala dinner và sự kiện khách hàng theo đúng mục tiêu của từng doanh nghiệp.',
                    ],
                    [
                        'title' => 'MICE ngành y dược',
                        'text' => 'Có kinh nghiệm với hội nghị y khoa, hội thảo dược phẩm, symposium, congress, đoàn bác sĩ, speaker, rooming list, visa, đưa đón và điều phối onsite.',
                    ],
                    [
                        'title' => 'Tour và lịch trình riêng',
                        'text' => 'Triển khai tour nước ngoài, tour trong nước và hành trình thiết kế riêng với lịch trình rõ, ngân sách minh bạch và hỗ trợ trong suốt chuyến đi.',
                    ],
                    [
                        'title' => 'Dịch vụ du lịch trọn gói',
                        'text' => 'Visa, vé máy bay, khách sạn, vận chuyển và dịch vụ đi kèm có thể được phối hợp chung, giúp khách không phải làm việc với nhiều đầu mối rời rạc.',
                    ],
                ],
            ],
            'sources' => [
                [
                    'title' => 'Tổ chức MICE chuyên nghiệp cho doanh nghiệp',
                    'url' => $this->makeLocalizedUrl('dich-vu-mice', $locale),
                ],
                [
                    'title' => 'Về Travel Plus',
                    'url' => $this->makeLocalizedUrl('ve-chung-toi', $locale),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStructuredMiceFacts(string $locale): array
    {
        $mice = MicePageContent::get($locale);
        $serviceCards = [];

        foreach (array_slice((array) ($mice['service_cards'] ?? []), 0, 4) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));
            $bullets = array_values(array_filter((array) ($item['bullets'] ?? []), 'is_string'));

            if ($title === '' && $text === '') {
                continue;
            }

            $serviceCards[] = [
                'title' => $title,
                'text' => $text,
                'bullets' => array_slice($bullets, 0, 3),
            ];
        }

        $solutionItems = [];

        foreach (array_slice((array) ($mice['solution_items'] ?? []), 0, 4) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));

            if ($title === '' && $text === '') {
                continue;
            }

            $solutionItems[] = [
                'title' => $title,
                'text' => $text,
            ];
        }

        return [
            'type' => 'mice_service',
            'intent' => 'mice_service',
            'mice' => [
                'title' => (string) ($mice['hero_title'] ?? 'MICE'),
                'description' => (string) ($mice['hero_desc'] ?? ''),
                'intro' => trim(((string) ($mice['intro_p1'] ?? '')) . ' ' . ((string) ($mice['intro_p2'] ?? ''))),
                'services_desc' => (string) ($mice['services_desc'] ?? ''),
                'service_cards' => $serviceCards,
                'solution_items' => $solutionItems,
            ],
            'sources' => [[
                'title' => (string) ($mice['hero_title'] ?? 'MICE'),
                'url' => $this->makeLocalizedUrl('dich-vu-mice', $locale),
            ]],
        ];
    }

    /**
     * @param list<array<string, mixed>> $matches
     * @return array<string, mixed>|null
     */
    private function buildStructuredTourListFacts(string $locale, array $matches, string $intent = 'availability', string $question = ''): ?array
    {
        if ($matches === []) {
            return null;
        }

        $tourFacts = [];
        $tourAdvisories = [];

        foreach (array_slice($matches, 0, 3) as $tour) {
            $advisory = $this->buildTourListAdvisoryProfile($locale, $tour, $question);
            $fit = $this->buildTourFitProfile($locale, $tour, $advisory, $question);
            $tourFact = $this->formatTourFactItem($tour, $locale);
            $tourFact['fit'] = $fit;

            $tourFacts[] = $tourFact;
            $tourAdvisories[] = [
                'title' => (string) ($tour['title'] ?? ''),
                'advisory' => $advisory,
                'fit' => $fit,
            ];
        }

        if ($tourFacts === []) {
            return null;
        }

        return [
            'type' => 'tour_list',
            'intent' => $intent,
            'selected_tour' => $tourFacts[0],
            'selected_advisory' => $tourAdvisories[0]['advisory'] ?? [],
            'selected_fit' => $tourFacts[0]['fit'] ?? [],
            'tours' => $tourFacts,
            'tour_advisories' => $tourAdvisories,
            'sources' => array_values(array_filter(array_map(static function (array $tour): ?array {
                $title = (string) ($tour['title'] ?? '');
                $url = (string) ($tour['url'] ?? '');

                if ($title === '' || $url === '') {
                    return null;
                }

                return ['title' => $title, 'url' => $url];
            }, array_slice($matches, 0, 3)))),
            'chat_state' => $this->buildTourChatState($matches[0], $locale),
        ];
    }

    /**
     * @param array<string, mixed> $tour
     * @return array<string, mixed>
     */
    private function buildTourListAdvisoryProfile(string $locale, array $tour, string $question): array
    {
        $detail = [];
        $routeStops = [];
        $attractions = [];
        $overview = '';

        try {
            $detail = (new TourCatalogService())->findTourBySlug(
                $locale,
                (string) ($tour['slug'] ?? ''),
                (string) ($tour['tour_type'] ?? '')
            ) ?? [];
        } catch (Throwable) {
            $detail = [];
        }

        if ($detail !== []) {
            $itineraryDays = is_array($detail['itinerary_days'] ?? null) ? $detail['itinerary_days'] : [];
            $routeStops = $this->extractRouteStops($itineraryDays);
            if ($routeStops === []) {
                $routeStops = $this->extractRouteStopsFromTourTitle((string) ($tour['title'] ?? $detail['title'] ?? ''));
            }
            $attractions = $this->extractAttractionHighlights($itineraryDays);
            $overview = trim($this->stripHtml((string) ($detail['overview'] ?? $detail['short_description'] ?? '')));
        }

        return $this->buildTourAdvisoryProfile(
            $locale,
            $tour,
            $detail,
            $routeStops,
            $attractions,
            [],
            $overview,
            $question
        );
    }

    /**
     * @param array<string, mixed> $tour
     * @param array<string, mixed> $advisory
     * @return array{score: int, label: string, reasons: list<string>, availability: string, availability_note: string}
     */
    private function buildTourFitProfile(string $locale, array $tour, array $advisory, string $question): array
    {
        $rawScore = (int) ($tour['score'] ?? 0);
        $isOutbound = (string) ($tour['tour_type'] ?? '') === 'outbound';
        $knownContext = $this->buildTourKnownContext($question, $isOutbound);
        $criteriaKeys = ['destination', 'guest_count', 'travel_time', 'budget', 'hotel_preference', 'pace_preference', 'departure_city'];
        if ($isOutbound) {
            $criteriaKeys[] = 'visa_status';
        }
        $criteriaCount = 0;
        foreach ($criteriaKeys as $key) {
            if (($knownContext[$key] ?? false) === true) {
                $criteriaCount++;
            }
        }

        $availability = $this->getTourAvailabilityStatus($tour);

        if ($criteriaCount === 0) {
            return [
                'score' => $availability === 'upcoming' ? 65 : 45,
                'label' => $locale === 'en' ? 'Needs qualification' : 'Cần hỏi thêm',
                'reasons' => [
                    $availability === 'upcoming'
                        ? ($locale === 'en' ? 'has an upcoming open departure' : 'có lịch khởi hành còn hiệu lực')
                        : ($locale === 'en' ? 'departure date needs rechecking' : 'cần kiểm tra lại lịch khởi hành'),
                    $locale === 'en'
                        ? 'the customer has not shared destination, date, guest count or budget yet'
                        : 'khách chưa cung cấp điểm đến, ngày đi, số khách hoặc ngân sách',
                ],
                'availability' => $availability,
                'availability_note' => $this->buildTourAvailabilityNote($availability, $locale),
            ];
        }

        $score = $rawScore >= 900 ? 78 : min(72, 38 + max(0, $rawScore * 2));
        $reasons = [];

        $matchedDestinations = array_values(array_filter((array) ($advisory['matched_destinations'] ?? []), 'is_string'));
        if ($matchedDestinations !== []) {
            $score += 10;
            $reasons[] = $locale === 'en'
                ? 'matches the requested destination'
                : 'khớp điểm đến khách đang hỏi';
        }

        $matchedCategories = array_values(array_filter((array) ($advisory['matched_categories'] ?? []), 'is_string'));
        if ($matchedCategories !== []) {
            $score += min(10, count($matchedCategories) * 3);
            $reasons[] = $locale === 'en'
                ? 'has a relevant travel style'
                : 'phong cách tour gần với nhu cầu';
        }

        $requestSignals = array_values(array_filter((array) ($advisory['request_signals'] ?? []), 'is_string'));
        if ($requestSignals !== []) {
            $score += min(12, count($requestSignals) * 4);
            $reasons[] = $locale === 'en'
                ? 'fits the customer profile mentioned in chat'
                : 'phù hợp nhóm khách đã nêu trong hội thoại';
        }

        $budgetNotes = array_values(array_filter((array) ($advisory['budget_notes'] ?? []), 'is_string'));
        if ($budgetNotes !== []) {
            $score += 3;
            $reasons[] = $locale === 'en'
                ? 'has budget context to qualify before quoting'
                : 'có dữ liệu ngân sách để tư vấn sát hơn';
        }

        if ($availability === 'upcoming') {
            $score += 8;
            $reasons[] = $locale === 'en'
                ? 'has an upcoming open departure'
                : 'có lịch khởi hành còn hiệu lực';
        } elseif ($availability === 'expired') {
            $score -= 30;
            $reasons[] = $locale === 'en'
                ? 'departure date has passed, use only as reference'
                : 'lịch khởi hành đã qua, chỉ nên dùng để tham khảo';
        } else {
            $score -= 10;
            $reasons[] = $locale === 'en'
                ? 'departure date needs rechecking'
                : 'cần kiểm tra lại lịch khởi hành';
        }

        if ($reasons === []) {
            $reasons[] = $locale === 'en'
                ? 'usable as an initial consultation option'
                : 'có thể dùng làm phương án tư vấn ban đầu';
        }

        $score = max(30, min(95, $score));

        return [
            'score' => $score,
            'label' => $this->formatTourFitScoreLabel($locale, $score),
            'reasons' => array_slice(array_values(array_unique($reasons)), 0, 4),
            'availability' => $availability,
            'availability_note' => $this->buildTourAvailabilityNote($availability, $locale),
        ];
    }

    private function formatTourFitScoreLabel(string $locale, int $score): string
    {
        if ($score >= 85) {
            return $locale === 'en' ? 'Very strong match' : 'Rất phù hợp';
        }

        if ($score >= 70) {
            return $locale === 'en' ? 'Good match' : 'Phù hợp';
        }

        if ($score >= 55) {
            return $locale === 'en' ? 'Needs qualification' : 'Cần hỏi thêm';
        }

        return $locale === 'en' ? 'Reference only' : 'Chỉ nên tham khảo';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStructuredDestinationTripConsultationFacts(string $locale, string $question): array
    {
        return [
            'type' => 'destination_trip_consultation',
            'intent' => 'destination_trip_consultation',
            'trip_request' => [
                'destination' => $this->extractKnownDestinationName($question),
                'guest_count' => $this->extractGuestCount($question),
                'travel_time' => $this->extractTravelTimeText($question),
                'budget' => $this->extractBudgetText($question),
            ],
            'advisory' => $this->buildDestinationConsultationAdvisory($locale, $question),
            'sources' => [[
                'title' => $locale === 'en' ? 'Tour search' : 'Tìm kiếm tour',
                'url' => $this->makeLocalizedUrl($locale === 'en' ? 'tour-search' : 'tim-kiem-tour', $locale),
            ]],
            'chat_state' => [
                'last_tour_slug' => '',
                'last_tour_type' => '',
                'last_tour_title' => '',
                'last_tour_departure_date' => '',
                'last_tour_departure' => '',
                'last_tour_price_label' => '',
                'last_tour_duration_label' => '',
                'last_tour_url' => '',
                'last_locale' => $locale,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDestinationConsultationAdvisory(string $locale, string $question): array
    {
        $haystack = $this->normalizeSearchText($question);
        $destinationGuides = $this->matchTourDestinationGuides($haystack);
        $requestSignals = $this->matchTourRequestSignals($question);
        $isOutbound = $this->looksLikeOutboundDestinationGuide($destinationGuides);
        $questionBudgetAmount = $this->extractTourQuestionBudgetAmount($question);

        $destinationNotes = [];
        $destinationQuestions = [];
        foreach ($destinationGuides as $guide) {
            $note = $this->localizedAdvisoryText($guide, 'note', $locale);
            if ($note !== '') {
                $destinationNotes[] = $note;
            }

            $destinationQuestions = array_merge($destinationQuestions, $this->localizedAdvisoryList($guide, 'questions', $locale));
        }

        $personalizedNotes = [];
        $requestQuestions = [];
        foreach ($requestSignals as $signal) {
            $note = $this->localizedAdvisoryText($signal, 'note', $locale);
            if ($note !== '') {
                $personalizedNotes[] = $note;
            }

            $requestQuestions = array_merge($requestQuestions, $this->localizedAdvisoryList($signal, 'questions', $locale));
        }

        $summary = $destinationNotes[0] ?? (
            $locale === 'en'
                ? 'Travel Plus can check available tours first and prepare a tailor-made option if no fixed itinerary matches.'
                : 'Travel Plus có thể kiểm tra tour có sẵn trước và thiết kế lịch trình riêng nếu chưa có tour khớp đúng.'
        );
        $knownContext = $this->buildTourKnownContext($question, $isOutbound);
        $missingInformation = $this->buildTourMissingInformation($locale, $question, $isOutbound);
        $leadAdvice = $this->buildTourLeadAdvice($locale, $knownContext, $missingInformation);
        $missingQuestions = $this->buildTourMissingQuestions($locale, $knownContext, $isOutbound);
        $questions = $this->filterTourNextQuestions($locale, array_values(array_unique(array_merge(
            $missingQuestions,
            $destinationQuestions,
            $requestQuestions,
            $this->buildTourConsultationQuestions($locale, $isOutbound)
        ))), $question, $isOutbound);

        return [
            'summary' => $summary,
            'matched_destinations' => array_slice(array_values(array_filter(array_map(
                static fn (array $guide): string => (string) ($guide['_key'] ?? ''),
                $destinationGuides
            ))), 0, 3),
            'request_signals' => array_slice(array_values(array_filter(array_map(
                static fn (array $signal): string => (string) ($signal['_key'] ?? ''),
                $requestSignals
            ))), 0, 5),
            'destination_notes' => array_slice(array_values(array_unique($destinationNotes)), 0, 2),
            'personalized_notes' => array_slice(array_values(array_unique($personalizedNotes)), 0, 3),
            'budget_notes' => $this->buildTourBudgetNotes($locale, $questionBudgetAmount, 0.0, $isOutbound),
            'service_addons' => $this->inferTourServiceAddons($locale, $isOutbound),
            'known_context' => $knownContext,
            'missing_information' => $missingInformation,
            'lead_readiness' => $leadAdvice['readiness'],
            'recommended_cta' => $leadAdvice['cta'],
            'next_questions' => $questions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStructuredVisaFacts(string $locale, string $question): array
    {
        $visa = VisaPageContent::get($locale);
        $normalizedQuestion = $this->normalizeSearchText($question);
        $matchedRegion = null;
        $intent = 'visa_process';

        if ($this->looksLikeVisaCostQuestion($question)) {
            $intent = 'visa_cost';
        } elseif ($this->looksLikeVisaProcessingTimeQuestion($question)) {
            $intent = 'visa_timeline';
        }

        foreach ((array) ($visa['regions'] ?? []) as $region) {
            foreach ((array) ($region['items'] ?? []) as $item) {
                $itemName = trim((string) $item);
                if ($itemName !== '' && str_contains(' ' . $normalizedQuestion . ' ', ' ' . $this->normalizeSearchText($itemName) . ' ')) {
                    $matchedRegion = $itemName;
                    break 2;
                }
            }
        }

        $steps = [];
        foreach (array_slice((array) ($visa['process'] ?? []), 0, 4) as $step) {
            if (! is_array($step)) {
                continue;
            }

            $title = trim((string) ($step['title'] ?? ''));
            $text = trim((string) ($step['text'] ?? ''));

            if ($title === '' && $text === '') {
                continue;
            }

            $steps[] = [
                'title' => $title,
                'text' => $text,
            ];
        }

        $supports = [];
        foreach (array_slice((array) ($visa['support_cards'] ?? []), 0, 4) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));

            if ($title === '' && $text === '') {
                continue;
            }

            $supports[] = [
                'title' => $title,
                'text' => $text,
            ];
        }

        return [
            'type' => 'visa_support',
            'intent' => $intent,
            'visa' => [
                'title' => (string) ($visa['hero_title'] ?? 'Visa'),
                'description' => $this->summarizeText((string) ($visa['hero_desc'] ?? ''), 260, 2),
                'intro' => $this->summarizeText(trim(((string) ($visa['intro_p1'] ?? '')) . ' ' . ((string) ($visa['intro_p2'] ?? ''))), 320, 2),
                'matched_destination' => $matchedRegion,
                'processing_time_available' => false,
                'processing_time_note' => $locale === 'en'
                    ? 'The current website content does not specify an exact processing time for this visa destination.'
                    : 'Nội dung hiện tại trên website chưa nêu thời gian xử lý cụ thể cho điểm đến visa này.',
                'steps' => $steps,
                'supports' => $supports,
                'cta_title' => (string) ($visa['cta_title'] ?? ''),
                'cta_text' => (string) ($visa['cta_text'] ?? ''),
            ],
            'sources' => [[
                'title' => (string) ($visa['hero_title'] ?? 'Visa'),
                'url' => $this->makeLocalizedUrl('dich-vu-visa', $locale),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStructuredPaymentFacts(string $locale): array
    {
        return [
            'type' => 'payment_support',
            'intent' => 'payment_methods',
            'payment' => [
                'title' => $locale === 'en' ? 'Payment methods' : 'Phương thức thanh toán',
                'summary' => $locale === 'en'
                    ? 'The website currently supports PayPal and VietQR checkout flows. The checkout interface also shows MoMo and ZaloPay options together with booking amount, deposit payment and total payment details.'
                    : 'Website hiện hỗ trợ luồng thanh toán với PayPal và VietQR. Trong giao diện checkout cũng có các lựa chọn MoMo và ZaloPay, kèm thông tin số tiền đặt cọc và tổng thanh toán.',
                'methods' => ['PayPal', 'VietQR', 'MoMo', 'ZaloPay'],
            ],
            'sources' => [[
                'title' => $locale === 'en' ? 'Checkout and payment' : 'Checkout và thanh toán',
                'url' => $this->makeLocalizedUrl('booking/checkout', $locale),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStructuredCustomTourFacts(string $locale): array
    {
        return [
            'type' => 'custom_tour_support',
            'intent' => 'custom_tour',
            'custom_tour' => [
                'title' => $locale === 'en' ? 'Custom tour requests' : 'Tạo tour theo yêu cầu',
                'summary' => $locale === 'en'
                    ? 'Travel Plus supports custom tour requests when no fixed itinerary matches. Travelers can send the destination, timing and trip preferences to receive a tailored program.'
                    : 'Travel Plus có hỗ trợ tạo tour theo yêu cầu khi chưa có hành trình cố định phù hợp. Khách có thể gửi điểm đến, thời gian và nhu cầu chuyến đi để nhận chương trình thiết kế riêng.',
            ],
            'sources' => [[
                'title' => $locale === 'en' ? 'Tour search and custom requests' : 'Tìm tour và tạo tour theo yêu cầu',
                'url' => $this->makeLocalizedUrl($locale === 'en' ? 'tour-search' : 'tim-kiem-tour', $locale),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildStructuredServiceFacts(string $locale, string $key, string $type): ?array
    {
        $pages = ServicePageCatalog::getAll();
        $page = $pages[$key] ?? null;

        if (! is_array($page)) {
            return null;
        }

        $title = (string) ($page['hero']['title'][$locale] ?? $page['hero']['title']['vi'] ?? '');
        $description = (string) ($page['hero']['description'][$locale] ?? $page['hero']['description']['vi'] ?? '');
        $intro = (string) ($page['intro']['body'][$locale] ?? $page['intro']['body']['vi'] ?? '');
        $useCases = [];

        foreach (array_slice((array) ($page['use_cases'] ?? []), 0, 3) as $item) {
            $text = trim((string) ($item[$locale] ?? $item['vi'] ?? ''));
            if ($text !== '') {
                $useCases[] = $text;
            }
        }

        $highlights = [];
        foreach (array_slice((array) ($page['capabilities'] ?? []), 0, 3) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $itemTitle = trim((string) ($item['title'][$locale] ?? $item['title']['vi'] ?? ''));
            $itemText = trim((string) ($item['text'][$locale] ?? $item['text']['vi'] ?? ''));

            if ($itemTitle !== '' || $itemText !== '') {
                $highlights[] = ['title' => $itemTitle, 'text' => $itemText];
            }
        }

        $path = (string) ($page['paths'][$locale] ?? $page['paths']['vi'] ?? '');
        $navLabel = (string) ($page['nav_label'][$locale] ?? $page['nav_label']['vi'] ?? $title);

        return [
            'type' => $type,
            'intent' => $type,
            'service' => [
                'title' => $title,
                'description' => $this->summarizeText($description, 220, 2),
                'intro' => $this->summarizeText($intro, 260, 2),
                'use_cases' => $useCases,
                'highlights' => $highlights,
            ],
            'sources' => [[
                'title' => $navLabel,
                'url' => $this->makeLocalizedUrl($path, $locale),
            ]],
        ];
    }

    /**
     * @param array<string, mixed> $tour
     * @return array<string, mixed>
     */
    private function formatTourFactItem(array $tour, string $locale = 'vi'): array
    {
        $availabilityStatus = $this->getTourAvailabilityStatus($tour);

        return [
            'title' => (string) ($tour['title'] ?? ''),
            'slug' => (string) ($tour['slug'] ?? ''),
            'tour_type' => (string) ($tour['tour_type'] ?? ''),
            'departure_date' => (string) ($tour['departure_date'] ?? ''),
            'departure' => (string) ($tour['departure'] ?? ''),
            'price_label' => (string) ($tour['price_label'] ?? ''),
            'duration_label' => (string) ($tour['duration_label'] ?? ''),
            'url' => (string) ($tour['url'] ?? ''),
            'availability_status' => $availabilityStatus,
            'availability_note' => $this->buildTourAvailabilityNote($availabilityStatus, $locale),
        ];
    }

    /**
     * @param array<string, mixed> $tour
     * @return array<string, mixed>
     */
    private function buildTourChatState(array $tour, string $locale): array
    {
        return [
            'last_tour_slug' => (string) ($tour['slug'] ?? ''),
            'last_tour_type' => (string) ($tour['tour_type'] ?? ''),
            'last_tour_title' => (string) ($tour['title'] ?? ''),
            'last_tour_departure_date' => (string) ($tour['departure_date'] ?? ''),
            'last_tour_departure' => (string) ($tour['departure'] ?? ''),
            'last_tour_price_label' => (string) ($tour['price_label'] ?? ''),
            'last_tour_duration_label' => (string) ($tour['duration_label'] ?? ''),
            'last_tour_url' => (string) ($tour['url'] ?? ''),
            'last_locale' => $locale,
        ];
    }

    /**
     * @param array<string, mixed> $tour
     * @param array<string, mixed> $detail
     * @param list<string> $routeStops
     * @param list<string> $attractions
     * @param list<array<string, mixed>> $itineraryHighlights
     * @return array<string, mixed>
     */
    private function buildTourAdvisoryProfile(
        string $locale,
        array $tour,
        array $detail = [],
        array $routeStops = [],
        array $attractions = [],
        array $itineraryHighlights = [],
        string $overview = '',
        string $question = ''
    ): array {
        $title = trim((string) ($tour['title'] ?? $detail['title'] ?? ''));
        $tourType = trim((string) ($tour['tour_type'] ?? $detail['tour_type'] ?? ''));
        $days = $this->extractTourDays($tour, $detail);
        $priceAmount = $this->extractTourPriceAmount($tour, $detail);
        $questionBudgetAmount = $this->extractTourQuestionBudgetAmount($question);

        $highlightTexts = [];
        foreach ($itineraryHighlights as $item) {
            if (! is_array($item)) {
                continue;
            }

            $highlightTexts[] = (string) ($item['title'] ?? '');
            $highlightTexts[] = (string) ($item['summary'] ?? '');
        }

        $haystack = $this->normalizeSearchText(implode(' ', array_filter(array_merge(
            [$title, $overview, (string) ($detail['short_description'] ?? '')],
            $routeStops,
            $attractions,
            $highlightTexts
        ))));

        $isOutbound = $tourType === 'outbound';
        $questionHaystack = $this->normalizeSearchText($question);
        $combinedHaystack = trim($haystack . ' ' . $questionHaystack);
        $matchedRules = $this->matchTourAdvisoryRules($combinedHaystack, $days, count($routeStops), count($itineraryHighlights));
        $destinationGuides = $this->matchTourDestinationGuides($combinedHaystack);
        $requestSignals = $this->matchTourRequestSignals($question);

        $strengths = [];
        $suitableFor = [];

        foreach ($matchedRules as $rule) {
            $strength = $this->localizedAdvisoryText($rule, 'strength', $locale);

            if ($strength !== '') {
                $strengths[] = $strength;
            }

            $suitableFor = array_merge($suitableFor, $this->localizedAdvisoryList($rule, 'suitable_for', $locale));
        }

        $personalizedNotes = [];
        $requestQuestions = [];
        $destinationNotes = [];
        $destinationQuestions = [];
        foreach ($destinationGuides as $guide) {
            $note = $this->localizedAdvisoryText($guide, 'note', $locale);
            if ($note !== '') {
                $destinationNotes[] = $note;
            }

            $destinationQuestions = array_merge($destinationQuestions, $this->localizedAdvisoryList($guide, 'questions', $locale));
        }

        foreach ($requestSignals as $signal) {
            $note = $this->localizedAdvisoryText($signal, 'note', $locale);
            if ($note !== '') {
                $personalizedNotes[] = $note;
            }

            $requestQuestions = array_merge($requestQuestions, $this->localizedAdvisoryList($signal, 'questions', $locale));
        }

        if ($strengths === []) {
            $fallback = $this->tourAdvisory->fallbacks[$isOutbound ? 'outbound' : 'domestic'] ?? [];
            $fallbackStrength = $this->localizedAdvisoryText($fallback, 'strength', $locale);
            $strengths[] = $fallbackStrength !== ''
                ? $fallbackStrength
                : ($locale === 'en' ? 'Suitable as a Travel Plus consultation option.' : 'Phù hợp để Travel Plus tư vấn theo nhu cầu khách.');
            $suitableFor = $this->localizedAdvisoryList($fallback, 'suitable_for', $locale);
        }

        if ($days >= 4 && $days <= 6) {
            $suitableFor[] = $locale === 'en' ? 'company trips' : 'company trip';
        }
        if ($suitableFor === []) {
            $suitableFor[] = $locale === 'en' ? 'small groups' : 'nhóm nhỏ';
        }

        $pace = $this->inferTourPace($locale, $days, count($routeStops), count($itineraryHighlights));
        $budgetSegment = $this->inferTourBudgetSegment($locale, $priceAmount, $isOutbound);
        $budgetNotes = $this->buildTourBudgetNotes($locale, $questionBudgetAmount, $priceAmount, $isOutbound);
        $addons = $this->inferTourServiceAddons($locale, $isOutbound);
        $knownContext = $this->buildTourKnownContext($question, $isOutbound);
        $missingInformation = $this->buildTourMissingInformation($locale, $question, $isOutbound);
        $leadAdvice = $this->buildTourLeadAdvice($locale, $knownContext, $missingInformation);
        $missingQuestions = $this->buildTourMissingQuestions($locale, $knownContext, $isOutbound);
        $questions = $this->filterTourNextQuestions($locale, array_values(array_unique(array_merge(
            $missingQuestions,
            $destinationQuestions,
            $requestQuestions,
            $this->buildTourConsultationQuestions($locale, $isOutbound)
        ))), $question, $isOutbound);
        $caution = $this->buildTourAdvisoryCaution($locale, $pace['key'], $isOutbound);

        $summary = $this->buildTourAdvisorySummary(
            $locale,
            $title,
            $strengths[0] ?? '',
            $pace['label'],
            $budgetSegment
        );

        return [
            'summary' => $summary,
            'matched_categories' => array_slice(array_values(array_filter(array_map(
                static fn (array $rule): string => (string) ($rule['_key'] ?? ''),
                $matchedRules
            ))), 0, 5),
            'request_signals' => array_slice(array_values(array_filter(array_map(
                static fn (array $signal): string => (string) ($signal['_key'] ?? ''),
                $requestSignals
            ))), 0, 5),
            'matched_destinations' => array_slice(array_values(array_filter(array_map(
                static fn (array $guide): string => (string) ($guide['_key'] ?? ''),
                $destinationGuides
            ))), 0, 3),
            'strengths' => array_slice(array_values(array_unique($strengths)), 0, 4),
            'suitable_for' => array_slice(array_values(array_unique($suitableFor)), 0, 4),
            'destination_notes' => array_slice(array_values(array_unique($destinationNotes)), 0, 2),
            'personalized_notes' => array_slice(array_values(array_unique($personalizedNotes)), 0, 3),
            'pace' => $pace['label'],
            'pace_note' => $pace['note'],
            'budget_segment' => $budgetSegment,
            'budget_notes' => $budgetNotes,
            'service_addons' => $addons,
            'sales_caution' => $caution,
            'known_context' => $knownContext,
            'missing_information' => $missingInformation,
            'lead_readiness' => $leadAdvice['readiness'],
            'recommended_cta' => $leadAdvice['cta'],
            'next_questions' => $questions,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function matchTourAdvisoryRules(string $haystack, int $days, int $routeStopCount, int $itineraryDayCount): array
    {
        $rules = [];

        foreach ($this->tourAdvisory->categories as $key => $rule) {
            if (! is_array($rule)) {
                continue;
            }

            $condition = (string) ($rule['condition'] ?? '');
            $matched = match ($condition) {
                'many_stops' => $routeStopCount >= 3 || $itineraryDayCount >= 4,
                'short_trip' => $days > 0 && $days <= 3,
                'long_trip' => $days >= 7,
                default => $this->containsAnyNormalized($haystack, array_values(array_filter((array) ($rule['keywords'] ?? []), 'is_string'))),
            };

            if (! $matched) {
                continue;
            }

            $rule['_key'] = (string) $key;
            $rules[] = $rule;
        }

        usort(
            $rules,
            static fn (array $a, array $b): int => ((int) ($b['priority'] ?? 0)) <=> ((int) ($a['priority'] ?? 0))
        );

        return $rules;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function matchTourRequestSignals(string $question): array
    {
        if (trim($question) === '') {
            return [];
        }

        $haystack = ' ' . trim($this->normalizeSearchText($question)) . ' ';
        $signals = [];

        foreach ($this->tourAdvisory->requestSignals as $key => $signal) {
            if (! is_array($signal)) {
                continue;
            }

            $keywords = array_values(array_filter((array) ($signal['keywords'] ?? []), 'is_string'));
            if (! $this->containsAnyNormalized($haystack, $keywords)) {
                continue;
            }

            $signal['_key'] = (string) $key;
            $signals[] = $signal;
        }

        return $signals;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function matchTourDestinationGuides(string $haystack): array
    {
        if (trim($haystack) === '') {
            return [];
        }

        $haystack = ' ' . trim($haystack) . ' ';
        $guides = [];

        foreach ($this->tourAdvisory->destinationGuides as $key => $guide) {
            if (! is_array($guide)) {
                continue;
            }

            $keywords = array_values(array_filter((array) ($guide['keywords'] ?? []), 'is_string'));
            if (! $this->containsAnyNormalized($haystack, $keywords)) {
                continue;
            }

            $guide['_key'] = (string) $key;
            $guides[] = $guide;
        }

        return $guides;
    }

    /**
     * @param list<array<string, mixed>> $destinationGuides
     */
    private function looksLikeOutboundDestinationGuide(array $destinationGuides): bool
    {
        $domestic = ['da_nang', 'nha_trang', 'phu_quoc', 'da_lat', 'sa_pa'];

        foreach ($destinationGuides as $guide) {
            $key = (string) ($guide['_key'] ?? '');

            if ($key !== '' && ! in_array($key, $domestic, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function localizedAdvisoryText(array $source, string $key, string $locale): string
    {
        $value = $source[$key] ?? null;

        if (! is_array($value)) {
            return is_string($value) ? trim($value) : '';
        }

        return trim((string) ($value[$locale] ?? $value['vi'] ?? $value['en'] ?? ''));
    }

    /**
     * @param array<string, mixed> $source
     * @return list<string>
     */
    private function localizedAdvisoryList(array $source, string $key, string $locale): array
    {
        $value = $source[$key] ?? [];

        if (! is_array($value)) {
            return [];
        }

        $items = $value[$locale] ?? $value['vi'] ?? $value['en'] ?? [];

        if (! is_array($items)) {
            return is_string($items) ? [trim($items)] : [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $items
        )));
    }

    private function localizedAdvisoryLabel(array $source, string $locale): string
    {
        return trim((string) ($source['label'][$locale] ?? $source['label']['vi'] ?? $source['label']['en'] ?? ''));
    }

    /**
     * @param array<string, mixed> $tour
     * @param array<string, mixed> $detail
     */
    private function extractTourDays(array $tour, array $detail): int
    {
        $days = (int) ($detail['duration_days'] ?? $tour['duration_days'] ?? 0);

        if ($days > 0) {
            return $days;
        }

        $durationLabel = (string) ($tour['duration_label'] ?? '');
        if (preg_match('/(\d+)\s*(?:ngay|day)/iu', $this->normalizeSearchText($durationLabel), $matches) === 1) {
            return (int) ($matches[1] ?? 0);
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $tour
     * @param array<string, mixed> $detail
     */
    private function extractTourPriceAmount(array $tour, array $detail): float
    {
        if (is_array($detail['price'] ?? null)) {
            $amount = (float) ($detail['price']['amount'] ?? 0);
            if ($amount > 0) {
                return $amount;
            }
        }

        foreach ([$detail['base_price'] ?? null, $detail['sale_price'] ?? null, $tour['base_price'] ?? null] as $value) {
            $amount = (float) $value;
            if ($amount > 0) {
                return $amount;
            }
        }

        $priceLabel = (string) ($tour['price_label'] ?? '');
        $digits = preg_replace('/[^\d]/', '', $priceLabel) ?? '';

        return $digits !== '' ? (float) $digits : 0.0;
    }

    private function extractTourQuestionBudgetAmount(string $question): float
    {
        $normalized = mb_strtolower($question);
        $normalized = strtr($normalized, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
            ',' => '.',
        ]);

        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:ty|ti|billion)/u', $normalized, $matches) === 1) {
            return (float) ($matches[1] ?? 0) * 1_000_000_000;
        }

        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:trieu|tr|m|million)/u', $normalized, $matches) === 1) {
            return (float) ($matches[1] ?? 0) * 1_000_000;
        }

        if (preg_match('/(?:ngan sach|budget|tam|khoang)\s*(\d{7,})/u', $normalized, $matches) === 1) {
            return (float) ($matches[1] ?? 0);
        }

        return 0.0;
    }

    /**
     * @return list<string>
     */
    private function buildTourBudgetNotes(string $locale, float $questionBudgetAmount, float $tourPriceAmount, bool $isOutbound): array
    {
        if ($questionBudgetAmount <= 0) {
            return [];
        }

        $notes = [];
        $notes[] = $locale === 'en'
            ? 'Clarify whether the budget is per guest or for the whole group, and whether it already includes flights.'
            : 'Cần hỏi rõ ngân sách đang tính mỗi khách hay cả đoàn, và đã gồm vé máy bay chưa.';

        if ($tourPriceAmount > 0 && $questionBudgetAmount < ($tourPriceAmount * .9)) {
            $notes[] = $locale === 'en'
                ? 'If this budget is per guest, it is lower than the listed starting price, so suggest a different date, service level or tailor-made option.'
                : 'Nếu ngân sách này tính mỗi khách thì đang thấp hơn giá từ của tour, nên gợi ý đổi ngày, đổi hạng dịch vụ hoặc thiết kế phương án riêng.';
        }

        if ($isOutbound && $questionBudgetAmount < 15_000_000) {
            $notes[] = $locale === 'en'
                ? 'For outbound tours, this budget needs careful checking because visa, flights and peak-season surcharges may change the total.'
                : 'Với tour nước ngoài, ngân sách này cần kiểm tra kỹ vì visa, vé bay và phụ thu mùa cao điểm có thể làm thay đổi tổng chi phí.';
        }

        return array_slice($notes, 0, 3);
    }

    /**
     * @return array<string, bool>
     */
    private function buildTourKnownContext(string $question, bool $isOutbound): array
    {
        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';

        return [
            'destination' => $this->extractKnownDestinationName($question) !== '',
            'guest_count' => $this->extractGuestCount($question) !== '',
            'travel_time' => $this->extractTravelTimeText($question) !== ''
                || $this->containsAnyNormalized($search, ['le', 'tet', 'he', 'cao diem', 'noel', 'giang sinh']),
            'budget' => $this->extractBudgetText($question) !== '' || $this->extractTourQuestionBudgetAmount($question) > 0,
            'hotel_preference' => $this->containsAnyNormalized($search, ['khach san', 'resort', 'homestay', 'gan bien', 'trung tam', 'may sao', 'sao']),
            'pace_preference' => $this->containsAnyNormalized($search, ['nghi duong', 'thu gian', 'nhe nhang', 'khong di nhieu', 'di nhieu diem', 'tham quan nhieu']),
            'departure_city' => $this->containsAnyNormalized($search, ['tu ha noi', 'tu tphcm', 'tu tp hcm', 'tu sai gon', 'xuat phat', 'di tu']),
            'visa_status' => ! $isOutbound || $this->containsAnyNormalized($search, ['visa', 'ho chieu', 'passport', 'da co visa', 'chua co visa']),
        ];
    }

    /**
     * @return list<string>
     */
    private function buildTourMissingInformation(string $locale, string $question, bool $isOutbound): array
    {
        $known = $this->buildTourKnownContext($question, $isOutbound);
        $labels = [
            'destination' => ['vi' => 'điểm đến mong muốn', 'en' => 'preferred destination'],
            'guest_count' => ['vi' => 'số lượng khách', 'en' => 'guest count'],
            'travel_time' => ['vi' => 'ngày đi hoặc khoảng thời gian dự kiến', 'en' => 'expected travel date or date range'],
            'budget' => ['vi' => 'ngân sách dự kiến', 'en' => 'approximate budget'],
            'hotel_preference' => ['vi' => 'tiêu chuẩn/khu vực khách sạn', 'en' => 'hotel standard or preferred area'],
            'departure_city' => ['vi' => 'điểm khởi hành', 'en' => 'departure city'],
        ];

        if ($isOutbound) {
            $labels['visa_status'] = ['vi' => 'tình trạng visa/hộ chiếu', 'en' => 'visa or passport status'];
        }

        $missing = [];
        foreach ($labels as $key => $label) {
            if (($known[$key] ?? false) === false) {
                $missing[] = $label[$locale] ?? $label['vi'];
            }
        }

        return array_slice($missing, 0, 4);
    }

    /**
     * @param array<string, bool> $known
     * @param list<string> $missing
     * @return array{readiness: string, cta: string}
     */
    private function buildTourLeadAdvice(string $locale, array $known, array $missing): array
    {
        $score = 0;
        foreach (['destination', 'guest_count', 'travel_time', 'budget', 'hotel_preference', 'departure_city', 'visa_status'] as $key) {
            if (($known[$key] ?? false) === true) {
                $score++;
            }
        }

        $hasCoreInfo = ($known['destination'] ?? false)
            && ($known['guest_count'] ?? false)
            && ($known['travel_time'] ?? false)
            && ($known['budget'] ?? false);

        if ($hasCoreInfo && count($missing) <= 2) {
            return [
                'readiness' => 'high',
                'cta' => $locale === 'en'
                    ? 'Ask for phone or email so Travel Plus can check availability and send a suitable option.'
                    : 'Nên xin số điện thoại hoặc email để Travel Plus kiểm tra chỗ và gửi phương án phù hợp.',
            ];
        }

        if ($score >= 3) {
            return [
                'readiness' => 'medium',
                'cta' => $locale === 'en'
                    ? 'Ask the highest-priority missing details first, then offer a callback.'
                    : 'Nên hỏi thêm các thông tin còn thiếu quan trọng nhất, sau đó mời khách để lại SĐT/email để tư vấn tiếp.',
            ];
        }

        return [
            'readiness' => 'low',
            'cta' => $locale === 'en'
                ? 'Collect destination, date range, guest count and budget before quoting.'
                : 'Nên lấy đủ điểm đến, thời gian đi, số khách và ngân sách trước khi báo phương án.',
        ];
    }

    /**
     * @param array<string, bool> $known
     * @return list<string>
     */
    private function buildTourMissingQuestions(string $locale, array $known, bool $isOutbound): array
    {
        $questions = [];

        if (! ($known['travel_time'] ?? false)) {
            $questions[] = $locale === 'en'
                ? 'What travel date or flexible date range do you prefer?'
                : 'Anh/chị dự kiến đi ngày nào hoặc khoảng thời gian nào?';
        }

        if (! ($known['guest_count'] ?? false)) {
            $questions[] = $locale === 'en'
                ? 'How many guests will travel?'
                : 'Đoàn mình dự kiến bao nhiêu khách?';
        }

        if (! ($known['departure_city'] ?? false)) {
            $questions[] = $locale === 'en'
                ? 'Which city will the group depart from?'
                : 'Đoàn mình khởi hành từ tỉnh/thành nào?';
        }

        if (! ($known['hotel_preference'] ?? false)) {
            $questions[] = $locale === 'en'
                ? 'What hotel standard or preferred area should Travel Plus quote?'
                : 'Anh/chị muốn khách sạn tiêu chuẩn mấy sao hoặc ở khu vực nào?';
        }

        if (! ($known['budget'] ?? false)) {
            $questions[] = $locale === 'en'
                ? 'What approximate budget per guest should Travel Plus work with?'
                : 'Ngân sách dự kiến mỗi khách khoảng bao nhiêu?';
        }

        if ($isOutbound && ! ($known['visa_status'] ?? false)) {
            $questions[] = $locale === 'en'
                ? 'Do guests already have visas or need Travel Plus to support visa preparation?'
                : 'Khách đã có visa chưa hay cần Travel Plus hỗ trợ hồ sơ?';
        }

        return $questions;
    }

    /**
     * @param list<string> $questions
     * @return list<string>
     */
    private function filterTourNextQuestions(string $locale, array $questions, string $question, bool $isOutbound): array
    {
        $known = $this->buildTourKnownContext($question, $isOutbound);
        $filtered = [];
        $usedTopics = [];

        foreach ($questions as $item) {
            $item = trim((string) $item);

            if ($item === '' || $this->shouldSkipTourQuestion($item, $known)) {
                continue;
            }

            $topic = $this->getTourQuestionTopic($item);
            if ($topic !== '' && isset($usedTopics[$topic])) {
                continue;
            }

            if ($topic !== '') {
                $usedTopics[$topic] = true;
            }

            $filtered[] = $item;
        }

        if ($filtered === []) {
            $missing = $this->buildTourMissingInformation($locale, $question, $isOutbound);

            if ($missing !== []) {
                return [
                    $locale === 'en'
                        ? 'Please share ' . implode(', ', array_slice($missing, 0, 3)) . ' so Travel Plus can advise accurately.'
                        : 'Anh/chị cho em xin thêm ' . implode(', ', array_slice($missing, 0, 3)) . ' để Travel Plus tư vấn sát hơn.',
                ];
            }
        }

        return array_slice(array_values(array_unique($filtered)), 0, 8);
    }

    private function getTourQuestionTopic(string $question): string
    {
        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';

        $topics = [
            'departure_city' => ['khoi hanh tu', 'diem khoi hanh', 'departure city'],
            'hotel' => ['khach san', 'resort', 'khu vuc nao', 'may sao', 'hotel standard', 'preferred area'],
            'budget_scope' => ['ngan sach nay da gom', 'budget include', 'da gom ve'],
            'budget' => ['ngan sach du kien', 'approximate budget'],
            'travel_time' => ['ngay nao', 'ngay di', 'khoang thoi gian', 'expected travel date', 'date range'],
            'date_flexibility' => ['ngay di co linh hoat', 'travel dates flexible'],
            'guest_count' => ['bao nhieu khach', 'so luong khach', 'number of guests', 'guest count'],
            'children' => ['tre em', 'em be', 'be bao nhieu tuoi', 'children', 'extra beds', 'giuong phu'],
            'senior' => ['nguoi lon tuoi', 'han che di bo', 'leo bac thang', 'suc khoe', 'senior', 'stairs'],
            'visa' => ['visa', 'ho chieu', 'passport'],
            'pace' => ['lich trinh', 'tham quan nhieu', 'nghi duong', 'more sightseeing', 'lighter pace'],
            'hold_option' => ['giu ve', 'giu phong', 'hold flights', 'hold rooms'],
        ];

        foreach ($topics as $topic => $needles) {
            if ($this->containsAnyNormalized($search, $needles)) {
                return $topic;
            }
        }

        return trim(preg_replace('/\s+/u', ' ', $search) ?? '');
    }

    /**
     * @param array<string, bool> $known
     */
    private function shouldSkipTourQuestion(string $question, array $known): bool
    {
        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';

        if (($known['guest_count'] ?? false) && $this->containsAnyNormalized($search, ['so luong khach', 'number of guests', 'guest count'])) {
            return true;
        }

        if (($known['travel_time'] ?? false) && $this->containsAnyNormalized($search, ['ngay di du kien', 'khoang ngay', 'expected travel date', 'date range'])) {
            return true;
        }

        if (($known['budget'] ?? false) && $this->containsAnyNormalized($search, ['ngan sach du kien moi khach', 'approximate budget per guest'])) {
            return true;
        }

        if (($known['hotel_preference'] ?? false) && $this->containsAnyNormalized($search, ['khach san tieu chuan', 'hotel standard', 'resort rieng tu'])) {
            return true;
        }

        if (($known['pace_preference'] ?? false) && $this->containsAnyNormalized($search, ['lich trinh nghi duong', 'tham quan nhieu diem', 'preferred style', 'more sightseeing'])) {
            return true;
        }

        if (($known['visa_status'] ?? false) && $this->containsAnyNormalized($search, ['tinh trang ho chieu', 'visa', 'passport'])) {
            return true;
        }

        return false;
    }

    /**
     * @param list<string> $needles
     */
    private function containsAnyNormalized(string $haystack, array $needles): bool
    {
        $haystack = ' ' . trim($haystack) . ' ';

        foreach ($needles as $needle) {
            if ($this->containsNormalizedPhrase($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{key: string, label: string, note: string}
     */
    private function inferTourPace(string $locale, int $days, int $routeStopCount, int $itineraryDayCount): array
    {
        $density = $days > 0 ? $routeStopCount / max(1, $days) : 0;
        $hasDetailedStops = $routeStopCount > 0 || $itineraryDayCount > 0;
        $paceKey = 'balanced';

        if ($days > 0 && ($days <= 3 || ($hasDetailedStops && $density <= .85 && $itineraryDayCount <= 3))) {
            $paceKey = 'light';
        } elseif ($days >= 7 || $density >= 1.15 || $routeStopCount >= 6) {
            $paceKey = 'dense';
        }

        $pace = $this->tourAdvisory->paces[$paceKey] ?? [];

        return [
            'key' => $paceKey,
            'label' => $this->localizedAdvisoryLabel($pace, $locale) ?: ($locale === 'en' ? $paceKey : 'vừa phải'),
            'note' => $this->localizedAdvisoryText($pace, 'note', $locale),
        ];
    }

    private function inferTourBudgetSegment(string $locale, float $priceAmount, bool $isOutbound): string
    {
        if ($priceAmount <= 0) {
            return $locale === 'en' ? 'quote on request' : 'cần kiểm tra báo giá';
        }

        $segments = $this->tourAdvisory->budgetSegments[$isOutbound ? 'outbound' : 'domestic'] ?? [];
        foreach ($segments as $segment) {
            if (! is_array($segment)) {
                continue;
            }

            $max = $segment['max'] ?? null;
            if ($max === null || $priceAmount <= (float) $max) {
                $label = $this->localizedAdvisoryLabel($segment, $locale);

                if ($label !== '') {
                    return $label;
                }
            }
        }

        return $locale === 'en' ? 'quote on request' : 'cần kiểm tra báo giá';
    }

    /**
     * @return list<string>
     */
    private function inferTourServiceAddons(string $locale, bool $isOutbound): array
    {
        $key = ($isOutbound ? 'outbound' : 'domestic') . '_' . ($locale === 'en' ? 'en' : 'vi');
        $addons = $this->tourAdvisory->serviceAddons[$key] ?? [];

        return array_values(array_filter($addons, 'is_string'));
    }

    /**
     * @return list<string>
     */
    private function buildTourConsultationQuestions(string $locale, bool $isOutbound): array
    {
        $key = ($isOutbound ? 'outbound' : 'domestic') . '_' . ($locale === 'en' ? 'en' : 'vi');
        $questions = $this->tourAdvisory->nextQuestions[$key] ?? [];

        return array_slice(array_values(array_filter($questions, 'is_string')), 0, 5);
    }

    private function buildTourAdvisoryCaution(string $locale, string $paceKey, bool $isOutbound): string
    {
        $key = $paceKey === 'dense' ? 'dense' : ($isOutbound ? 'outbound' : 'domestic');
        $caution = $this->tourAdvisory->cautions[$key] ?? [];

        return trim((string) ($caution[$locale] ?? $caution['vi'] ?? $caution['en'] ?? ''));
    }

    private function buildTourAdvisorySummary(string $locale, string $title, string $strength, string $pace, string $budgetSegment): string
    {
        if ($locale === 'en') {
            $prefix = $title !== '' ? $title . ' works as a consultation option because ' : 'This tour works as a consultation option because ';

            return $prefix . lcfirst($strength) . ' Pace: ' . $pace . '. Budget segment: ' . $budgetSegment . '.';
        }

        $prefix = $title !== '' ? 'Tour ' . $title . ' đáng tư vấn. ' : 'Tour này đáng tư vấn. ';

        return $prefix . 'Điểm mạnh: ' . $strength . ' Nhịp tour: ' . $pace . '. Phân khúc: ' . $budgetSegment . '.';
    }

    /**
     * @param array<string, mixed> $chatState
     * @return array<string, mixed>|null
     */
    private function getLastMatchedTourFromState(string $locale, array $chatState): ?array
    {
        $slug = trim((string) ($chatState['last_tour_slug'] ?? ''));
        $tourType = trim((string) ($chatState['last_tour_type'] ?? ''));

        if ($slug === '') {
            return null;
        }

        $tourService = new TourCatalogService();
        $detail = $tourService->findTourBySlug($locale, $slug, $tourType !== '' ? $tourType : null);

        if ($detail === null) {
            return null;
        }

        $priceAmount = 0.0;

        if (is_array($detail['price'] ?? null)) {
            $priceAmount = (float) (($detail['price']['amount'] ?? 0));
        }

        $firstDeparture = is_array($detail['departures'] ?? null) ? ($detail['departures'][0] ?? null) : null;

        if ($priceAmount <= 0) {
            if (is_array($firstDeparture)) {
                $priceAmount = (float) ($firstDeparture['price'] ?? 0);
            }
        }

        $departureDate = (string) ($detail['departure_date'] ?? '');

        if ($departureDate === '' && is_array($firstDeparture)) {
            $departureDate = (string) ($firstDeparture['date'] ?? $firstDeparture['departure_date'] ?? '');
        }

        $stateDeparture = trim((string) ($chatState['last_tour_departure'] ?? ''));
        $stateDepartureDate = trim((string) ($chatState['last_tour_departure_date'] ?? ''));
        $statePriceLabel = trim((string) ($chatState['last_tour_price_label'] ?? ''));
        $stateDurationLabel = trim((string) ($chatState['last_tour_duration_label'] ?? ''));
        $stateUrl = trim((string) ($chatState['last_tour_url'] ?? ''));
        $detailUrl = trim((string) ($detail['url'] ?? ''));
        $resolvedDeparture = $departureDate !== '' ? $departureDate : ($stateDepartureDate !== '' ? $stateDepartureDate : $stateDeparture);

        if ($resolvedDeparture !== '' && ! $this->isUpcomingDepartureValue($resolvedDeparture)) {
            return null;
        }

        return [
            'title' => (string) ($detail['title'] ?? ''),
            'slug' => $slug,
            'tour_type' => (string) ($detail['tour_type'] ?? $tourType),
            'departure_date' => $resolvedDeparture,
            'departure' => $resolvedDeparture !== '' ? $this->formatDisplayDate($resolvedDeparture) : $stateDeparture,
            'price_label' => $priceAmount > 0 ? $this->formatMoneyLabel($priceAmount) : $statePriceLabel,
            'duration_label' => $stateDurationLabel !== '' ? $stateDurationLabel : $this->formatDurationLabel(
                (int) ($detail['duration_days'] ?? 0),
                (int) ($detail['duration_nights'] ?? 0),
                $locale
            ),
            'url' => $detailUrl !== '' ? $detailUrl : $stateUrl,
        ];
    }

    /**
     * @return list<array{title: string, departure: string, price_label: string, duration_label: string, url: string, score: int, slug: string, tour_type: string}>
     */
    private function findMatchingTours(string $locale, string $question, int $limit = 5): array
    {
        $query = $this->extractTourSearchQuery($question);
        $focusTargets = $this->extractLocationFocusTargets($locale, $question);

        if ($query === '' && $focusTargets === []) {
            return [];
        }

        if ($focusTargets !== [] && $query === '') {
            $focusedMatches = $this->findToursByLocationTargets($locale, $focusTargets, $limit);

            if ($focusedMatches !== []) {
                return $focusedMatches;
            }
        }

        if (! $this->db->tableExists('tours') || ! $this->db->tableExists('tour_translations') || ! $this->db->tableExists('tour_departures')) {
            return [];
        }

        $today = date('Y-m-d');
        $rowsBuilder = $this->db->table('tours t')
            ->select('
                t.id,
                t.tour_type,
                t.duration_days,
                t.duration_nights,
                t.base_price,
                tt.name AS title,
                tt.slug,
                MIN(td.departure_date) AS departure_date,
                GROUP_CONCAT(DISTINCT dltn.name SEPARATOR " | ") AS destinations,
                GROUP_CONCAT(DISTINCT dl.id) AS destination_ids,
                GROUP_CONCAT(DISTINCT dlp.id) AS parent_ids,
                GROUP_CONCAT(DISTINCT dlgp.id) AS grandparent_ids
            ', false)
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $this->db->escape($locale), 'inner')
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'inner')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'left')
            ->join('locations dl', 'dl.id = tdst.location_id', 'left')
            ->join('locations dlp', 'dlp.id = dl.parent_id', 'left')
            ->join('locations dlgp', 'dlgp.id = dlp.parent_id', 'left')
            ->join('location_translations dltn', 'dltn.location_id = dl.id AND dltn.locale = ' . $this->db->escape($locale), 'left')
            ->where('t.status', 'published')
            ->where('td.departure_date >=', $today);

        if ($focusTargets !== []) {
            $this->applyLocationTargetConditions($rowsBuilder, $focusTargets);
        }

        $rows = $rowsBuilder
            ->groupBy('t.id, t.tour_type, t.duration_days, t.duration_nights, t.base_price, tt.name, tt.slug')
            ->limit(150)
            ->get()
            ->getResultArray();

        $matches = [];
        $queryTokens = $this->tokenize($query);
        $destinationSignals = $this->extractDestinationSignals($question);

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $slug = trim((string) ($row['slug'] ?? ''));

            if ($title === '' || $slug === '') {
                continue;
            }

            $haystack = implode(' ', array_filter([
                $title,
                (string) ($row['destinations'] ?? ''),
            ]));

            $normalizedHaystack = $this->normalizeSearchText($haystack);

            if ($focusTargets !== [] && ! $this->matchesLocationTargets($row, $normalizedHaystack, $focusTargets)) {
                continue;
            }

            $score = $this->scoreTokenSet($queryTokens, $haystack);
            $score += $this->scoreDestinationSignals($destinationSignals, $normalizedHaystack);

            if ($focusTargets !== []) {
                foreach ($focusTargets as $target) {
                    if (str_contains($normalizedHaystack, $target['name'])) {
                        $score += 20;
                    }
                }
            }

            if ($score <= 0) {
                continue;
            }

            $tourType = (string) ($row['tour_type'] ?? 'outbound');
            $locationSlug = $tourType === 'inbound' ? 'viet-nam' : 'diem-den';
            $url = $tourType === 'inbound'
                ? localized_url('tour-trong-nuoc/' . $locationSlug . '/tour/' . $slug)
                : localized_url('tour-nuoc-ngoai/' . $locationSlug . '/' . $slug);

            $matches[] = [
                'title' => $title,
                'departure_date' => (string) ($row['departure_date'] ?? ''),
                'departure' => $this->formatDisplayDate((string) ($row['departure_date'] ?? '')),
                'price_label' => $this->formatMoneyLabel((float) ($row['base_price'] ?? 0)),
                'duration_label' => $this->formatDurationLabel(
                    (int) ($row['duration_days'] ?? 0),
                    (int) ($row['duration_nights'] ?? 0),
                    $locale
                ),
                'url' => $url,
                'score' => $score,
                'slug' => $slug,
                'tour_type' => $tourType,
            ];
        }

        usort($matches, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, $limit);
    }

    /**
     * @param list<array{id: int, type: string, name: string}> $focusTargets
     * @return list<array{title: string, departure: string, price_label: string, duration_label: string, url: string, score: int, slug: string, tour_type: string}>
     */
    private function findToursByLocationTargets(string $locale, array $focusTargets, int $limit): array
    {
        $countryIds = [];
        $provinceIds = [];
        $continentIds = [];

        foreach ($focusTargets as $target) {
            $targetId = (int) ($target['id'] ?? 0);
            $targetType = (string) ($target['type'] ?? '');

            if ($targetId <= 0) {
                continue;
            }

            if ($targetType === 'country') {
                $countryIds[] = $targetId;
            } elseif ($targetType === 'province') {
                $provinceIds[] = $targetId;
            } elseif ($targetType === 'continent') {
                $continentIds[] = $targetId;
            }
        }

        $builder = $this->db->table('tours t')
            ->select('
                t.id,
                t.tour_type,
                t.duration_days,
                t.duration_nights,
                t.base_price,
                tt.name AS title,
                tt.slug,
                MIN(td.departure_date) AS departure_date
            ', false)
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $this->db->escape($locale), 'inner')
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'inner')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'inner')
            ->join('locations dl', 'dl.id = tdst.location_id', 'inner')
            ->join('locations dlp', 'dlp.id = dl.parent_id', 'left')
            ->where('t.status', 'published')
            ->where('td.departure_date >=', date('Y-m-d'));

        $this->applyLocationTargetConditions($builder, $focusTargets);

        $rows = $builder
            ->groupBy('t.id, t.tour_type, t.duration_days, t.duration_nights, t.base_price, tt.name, tt.slug')
            ->orderBy('MIN(td.departure_date)', 'ASC', false)
            ->limit($limit)
            ->get()
            ->getResultArray();

        $matches = [];

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $tourType = (string) ($row['tour_type'] ?? 'outbound');

            if ($slug === '' || $title === '') {
                continue;
            }

            $locationSlug = $tourType === 'inbound' ? 'viet-nam' : 'diem-den';
            $url = $tourType === 'inbound'
                ? localized_url('tour-trong-nuoc/' . $locationSlug . '/tour/' . $slug)
                : localized_url('tour-nuoc-ngoai/' . $locationSlug . '/' . $slug);

            $matches[] = [
                'title' => $title,
                'departure_date' => (string) ($row['departure_date'] ?? ''),
                'departure' => $this->formatDisplayDate((string) ($row['departure_date'] ?? '')),
                'price_label' => $this->formatMoneyLabel((float) ($row['base_price'] ?? 0)),
                'duration_label' => $this->formatDurationLabel(
                    (int) ($row['duration_days'] ?? 0),
                    (int) ($row['duration_nights'] ?? 0),
                    $locale
                ),
                'url' => $url,
                'score' => 999,
                'slug' => $slug,
                'tour_type' => $tourType,
            ];
        }

        return $matches;
    }

    /**
     * @param list<array{id: int, type: string, name: string}> $focusTargets
     */
    private function applyLocationTargetConditions(\CodeIgniter\Database\BaseBuilder $builder, array $focusTargets): void
    {
        $countryIds = [];
        $provinceIds = [];
        $continentIds = [];

        foreach ($focusTargets as $target) {
            $targetId = (int) ($target['id'] ?? 0);
            $targetType = (string) ($target['type'] ?? '');

            if ($targetId <= 0) {
                continue;
            }

            if ($targetType === 'country') {
                $countryIds[] = $targetId;
            } elseif ($targetType === 'province') {
                $provinceIds[] = $targetId;
            } elseif ($targetType === 'continent') {
                $continentIds[] = $targetId;
            }
        }

        $hasAny = false;
        $builder->groupStart();

        if ($countryIds !== []) {
            $hasAny = true;
            $builder->groupStart()
                ->whereIn('dl.id', $countryIds)
                ->orWhereIn('dl.parent_id', $countryIds)
                ->groupEnd();
        }

        if ($provinceIds !== []) {
            if ($hasAny) {
                $builder->orGroupStart()->whereIn('dl.id', $provinceIds)->groupEnd();
            } else {
                $builder->groupStart()->whereIn('dl.id', $provinceIds)->groupEnd();
                $hasAny = true;
            }
        }

        if ($continentIds !== []) {
            if ($hasAny) {
                $builder->orGroupStart()
                    ->whereIn('dl.id', $continentIds)
                    ->orWhereIn('dl.parent_id', $continentIds)
                    ->orWhereIn('dlp.parent_id', $continentIds)
                    ->groupEnd();
            } else {
                $builder->groupStart()
                    ->whereIn('dl.id', $continentIds)
                    ->orWhereIn('dl.parent_id', $continentIds)
                    ->orWhereIn('dlp.parent_id', $continentIds)
                    ->groupEnd();
                $hasAny = true;
            }
        }

        $builder->groupEnd();
    }

    /**
     * @return list<array{title: string, text: string, url: string}>
     */
    private function getOperationalChunks(string $locale): array
    {
        $contactPath = $locale === 'en' ? 'contact' : 'contact';
        $searchPath = $locale === 'en' ? 'tour-search' : 'tim-kiem-tour';
        $checkoutPath = $locale === 'en' ? 'booking/checkout' : 'booking/checkout';
        $offices = OfficeLocationCatalog::getAll($locale);
        $officeTexts = [];

        foreach ($offices as $office) {
            $officeTexts[] = (string) ($office['title'] ?? '');
            $officeTexts[] = (string) ($office['address'] ?? '');
        }

        return [
            [
                'title' => $locale === 'en' ? 'Payment methods' : 'Phương thức thanh toán',
                'text' => $locale === 'en'
                    ? 'The website currently supports checkout flows with PayPal and VietQR. The booking flow also presents payment method options such as MoMo and ZaloPay in the checkout interface. Payment policy, booking amount, deposit payment, and total amount are shown during checkout.'
                    : 'Website hiện hỗ trợ luồng thanh toán với PayPal và VietQR. Trong giao diện checkout cũng có các lựa chọn phương thức thanh toán như MoMo và ZaloPay. Chính sách thanh toán, số tiền đặt cọc và tổng tiền được hiển thị trong bước checkout.',
                'url' => $this->makeLocalizedUrl($checkoutPath, $locale),
            ],
            [
                'title' => $locale === 'en' ? 'Custom tour requests' : 'Tạo tour theo yêu cầu',
                'text' => $locale === 'en'
                    ? 'Travel Plus supports custom tour requests. If no matching itinerary is found, users can create a custom trip request and contact Travel Plus for consultation.'
                    : 'Travel Plus có hỗ trợ tạo tour theo yêu cầu. Nếu không có tour phù hợp, khách có thể gửi yêu cầu thiết kế hành trình riêng và liên hệ Travel Plus để được tư vấn.',
                'url' => $this->makeLocalizedUrl($searchPath, $locale),
            ],
            [
                'title' => $locale === 'en' ? 'Contact and support' : 'Liên hệ và hỗ trợ',
                'text' => $this->flattenContent(array_merge([
                    $locale === 'en'
                        ? 'Travel Plus provides contact and consultation support through the contact page and office information.'
                        : 'Travel Plus có hỗ trợ tư vấn và liên hệ qua trang contact cùng thông tin văn phòng.',
                ], $officeTexts)),
                'url' => $this->makeLocalizedUrl($contactPath, $locale),
            ],
        ];
    }

    /**
     * @return list<array{title: string, text: string, url: string}>
     */
    private function getStaticChunks(string $locale): array
    {
        $chunks = [];
        $servicePages = ServicePageCatalog::getAll();

        foreach ($servicePages as $page) {
            $path = $page['paths'][$locale] ?? $page['paths']['vi'] ?? '';
            $title = $page['hero']['title'][$locale] ?? $page['hero']['title']['vi'] ?? '';
            $text = $this->flattenContent([
                $page['hero']['description'][$locale] ?? '',
                $page['intro']['title'][$locale] ?? '',
                $page['intro']['body'][$locale] ?? '',
                $page['use_cases_title'][$locale] ?? '',
                $page['use_cases'] ?? [],
                $page['why_title'][$locale] ?? '',
                $page['why'] ?? [],
                $page['cta']['title'][$locale] ?? '',
                $page['cta']['text'][$locale] ?? '',
            ]);

            if ($path !== '' && $title !== '' && $text !== '') {
                $chunks[] = [
                    'title' => $title,
                    'text' => $text,
                    'url' => $this->makeLocalizedUrl($path, $locale),
                ];
            }
        }

        $visa = VisaPageContent::get($locale);
        $chunks[] = [
            'title' => (string) ($visa['hero_title'] ?? 'Visa'),
            'text' => $this->flattenContent([
                $visa['hero_desc'] ?? '',
                $visa['intro_title'] ?? '',
                $visa['intro_p1'] ?? '',
                $visa['intro_p2'] ?? '',
                $visa['support_cards'] ?? [],
                $visa['process'] ?? [],
                $visa['faqs'] ?? [],
                $visa['cta_text'] ?? '',
            ]),
            'url' => $this->makeLocalizedUrl($locale === 'en' ? 'dich-vu-visa' : 'dich-vu-visa', $locale),
        ];

        $mice = MicePageContent::get($locale);
        $chunks[] = [
            'title' => (string) ($mice['hero_title'] ?? 'MICE'),
            'text' => $this->flattenContent($mice),
            'url' => $this->makeLocalizedUrl($locale === 'en' ? 'dich-vu-mice' : 'dich-vu-mice', $locale),
        ];

        $about = AboutPageContent::get($locale);
        $chunks[] = [
            'title' => (string) ($about['hero_title'] ?? ($locale === 'en' ? 'About Travel Plus' : 'Về Travel Plus')),
            'text' => $this->flattenContent($about),
            'url' => $this->makeLocalizedUrl('ve-chung-toi', $locale),
        ];

        foreach (['terms', 'privacy'] as $type) {
            $legal = LegalPageCatalog::get($type, $locale);

            if ($legal !== []) {
                $path = $type === 'terms'
                    ? ($locale === 'en' ? 'terms-of-service' : 'dieu-khoan-su-dung')
                    : ($locale === 'en' ? 'privacy-statement' : 'chinh-sach-bao-mat');

                $chunks[] = [
                    'title' => (string) ($legal['title'] ?? ''),
                    'text' => $this->flattenContent($legal['sections'] ?? []),
                    'url' => $this->makeLocalizedUrl($path, $locale),
                ];
            }
        }

        return $chunks;
    }

    /**
     * @return list<array{title: string, text: string, url: string}>
     */
    private function getTourChunks(string $locale): array
    {
        if (! $this->db->tableExists('tours') || ! $this->db->tableExists('tour_translations')) {
            return [];
        }

        $rows = $this->db->table('tours t')
            ->select('
                t.tour_type,
                tt.name,
                tt.slug,
                tt.short_description,
                tt.overview,
                tt.description,
                t.duration_days,
                t.duration_nights
            ')
            ->join('tour_translations tt', 'tt.tour_id = t.id', 'inner')
            ->where('tt.locale', $locale)
            ->where('t.status', 'published')
            ->orderBy('t.id', 'DESC')
            ->limit(120)
            ->get()
            ->getResultArray();

        $chunks = [];

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            $tourType = (string) ($row['tour_type'] ?? 'outbound');

            if ($slug === '') {
                continue;
            }

            $searchPath = $locale === 'en' ? 'tour-search' : 'tim-kiem-tour';
            $query = rawurlencode((string) ($row['name'] ?? $slug));

            $chunks[] = [
                'title' => (string) ($row['name'] ?? ''),
                'text' => $this->truncateText($this->flattenContent([
                    $row['short_description'] ?? '',
                    $row['overview'] ?? '',
                    $row['description'] ?? '',
                    ($locale === 'en' ? 'Duration' : 'Thời lượng') . ': ' . (int) ($row['duration_days'] ?? 0) . ' / ' . (int) ($row['duration_nights'] ?? 0),
                ]), 1600),
                'url' => $this->makeLocalizedUrl($searchPath, $locale) . '?q=' . $query . '&type=' . rawurlencode($tourType),
            ];
        }

        return $chunks;
    }

    /**
     * @return list<array{title: string, text: string, url: string}>
     */
    private function getBlogChunks(string $locale): array
    {
        if (! $this->db->tableExists('blogs') || ! $this->db->tableExists('blog_translations')) {
            return [];
        }

        $basePath = $locale === 'en' ? 'travel-inspiration' : 'cam-hung-du-lich';

        $rows = $this->db->table('blogs b')
            ->select('bt.title, bt.slug, bt.excerpt, bt.content')
            ->join('blog_translations bt', 'bt.blog_id = b.id', 'inner')
            ->where('b.status', 'published')
            ->where('bt.locale', $locale)
            ->orderBy('b.published_at', 'DESC')
            ->limit(80)
            ->get()
            ->getResultArray();

        $chunks = [];

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));

            if ($slug === '') {
                continue;
            }

            $chunks[] = [
                'title' => (string) ($row['title'] ?? ''),
                'text' => $this->truncateText($this->flattenContent([
                    $row['excerpt'] ?? '',
                    $this->stripHtml((string) ($row['content'] ?? '')),
                ]), 1200),
                'url' => $this->makeLocalizedUrl($basePath . '/' . $slug, $locale),
            ];
        }

        return $chunks;
    }

    /**
     * @param mixed $value
     */
    private function flattenContent($value): string
    {
        if (is_string($value)) {
            return trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
        }

        if (! is_array($value)) {
            return '';
        }

        $parts = [];

        foreach ($value as $item) {
            $text = $this->flattenContent($item);

            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return implode(' ', $parts);
    }

    private function stripHtml(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    }

    private function truncateText(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 1)) . '…';
    }

    private function summarizeText(string $text, int $limit, int $maxSentences = 2): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($text === '') {
            return '';
        }

        $sentences = preg_split('/(?<=[\.\!\?…])\s+/u', $text) ?: [];
        $selected = [];
        $currentLength = 0;

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            if ($sentence === '') {
                continue;
            }

            $sentenceLength = mb_strlen($sentence);
            $separatorLength = $selected === [] ? 0 : 1;

            if (count($selected) >= $maxSentences || ($currentLength + $separatorLength + $sentenceLength) > $limit) {
                break;
            }

            $selected[] = $sentence;
            $currentLength += $separatorLength + $sentenceLength;
        }

        if ($selected !== []) {
            return implode(' ', $selected);
        }

        return $this->truncateText($text, $limit);
    }

    private function summarizeMeaningfulText(string $text, int $limit): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($text === '') {
            return '';
        }

        $sentences = preg_split('/(?<=[\.\!\?…])\s+/u', $text) ?: [];
        $selected = [];

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            if ($sentence === '') {
                continue;
            }

            $normalized = $this->normalizeSearchText($sentence);

            if ($this->isOperationalSentence($normalized)) {
                continue;
            }

            $selected[] = $sentence;

            if (mb_strlen(implode(' ', $selected)) >= $limit) {
                break;
            }
        }

        if ($selected === []) {
            return $this->summarizeText($text, $limit, 1);
        }

        return $this->summarizeText(implode(' ', $selected), $limit, 2);
    }

    private function scoreChunk(string $question, string $text): int
    {
        $questionTokens = $this->tokenize($question);
        return $this->scoreTokenSet($questionTokens, $text);
    }

    /**
     * @param list<string> $questionTokens
     */
    private function scoreTokenSet(array $questionTokens, string $text): int
    {
        $textTokens = array_flip($this->tokenize($text));
        $score = 0;

        foreach ($questionTokens as $token) {
            if (isset($textTokens[$token])) {
                $score += max(1, strlen($token) - 2);
            }
        }

        return $score;
    }

    /**
     * @return array<string, list<string>>
     */
    private function extractDestinationSignals(string $question): array
    {
        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';
        $destinationMap = [
            'france' => ['phap', 'france', 'paris'],
            'switzerland' => ['thuy si', 'thuy sy', 'switzerland', 'swiss', 'zurich', 'lucerne', 'interlaken', 'titlis'],
            'italy' => ['y', 'italy', 'italia', 'rome', 'roma', 'milan', 'venice', 'venezia', 'pisa', 'florence'],
            'europe' => ['chau au', 'europe', 'tay au'],
            'japan' => ['nhat ban', 'japan', 'tokyo', 'osaka', 'kyoto'],
            'korea' => ['han quoc', 'korea', 'seoul', 'nami', 'busan'],
            'thailand' => ['thai lan', 'thailand', 'bangkok', 'pattaya', 'phuket'],
            'usa' => ['my', 'hoa ky', 'usa', 'america', 'new york', 'washington', 'los angeles'],
            'vietnam' => ['viet nam', 'vietnam'],
            'ha_noi' => ['ha noi', 'hanoi'],
            'nha_trang' => ['nha trang', 'cam ranh'],
            'da_nang' => ['da nang', 'danang', 'hoi an', 'ba na'],
            'da_lat' => ['da lat', 'dalat'],
            'phu_quoc' => ['phu quoc', 'phuquoc'],
            'sa_pa' => ['sa pa', 'sapa'],
            'ho_chi_minh' => ['tphcm', 'tp hcm', 'sai gon', 'ho chi minh'],
        ];

        $signals = [];

        foreach ($destinationMap as $key => $needles) {
            foreach ($needles as $needle) {
                if ($this->containsNormalizedPhrase($search, $needle)) {
                    $signals[$key] = $needles;
                    break;
                }
            }
        }

        return $signals;
    }

    /**
     * @param array<string, list<string>> $destinationSignals
     */
    private function scoreDestinationSignals(array $destinationSignals, string $normalizedHaystack): int
    {
        if ($destinationSignals === []) {
            return 0;
        }

        $haystack = ' ' . trim($normalizedHaystack) . ' ';
        $matchedCount = 0;
        $score = 0;

        foreach ($destinationSignals as $needles) {
            foreach ($needles as $needle) {
                if ($this->containsNormalizedPhrase($haystack, $needle)) {
                    $matchedCount++;
                    $score += 12;
                    break;
                }
            }
        }

        if ($matchedCount >= 2) {
            $score += $matchedCount * 10;
        }

        if ($matchedCount === count($destinationSignals) && $matchedCount > 1) {
            $score += 15;
        }

        return $score;
    }

    private function containsNormalizedPhrase(string $normalizedText, string $phrase): bool
    {
        $phrase = trim($this->normalizeSearchText($phrase));

        if ($phrase === '') {
            return false;
        }

        return str_contains($normalizedText, ' ' . $phrase . ' ');
    }

    private function looksLikeCompanyStrengthQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        if (
            str_contains($search, 'tour')
            && ! str_contains($search, 'travel plus')
            && ! str_contains($search, 'cong ty')
            && ! str_contains($search, 'doanh nghiep')
            && ! str_contains($search, 'the manh')
        ) {
            return false;
        }

        foreach ([
            'the manh',
            'diem manh',
            'loi the',
            'uu diem',
            'manh ve gi',
            'manh nhat',
            'noi bat',
            'khac biet',
            'vi sao chon',
            'tai sao chon',
            'why choose',
            'strength',
            'advantage',
            'different',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTourQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach (['tour', 'giá', 'gia', 'khởi hành', 'khoi hanh', 'lịch', 'lich', 'điểm đến', 'diem den'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        if ($this->extractKnownDestinationName($question) !== '' && $this->looksLikeDestinationListQuestion($question)) {
            return true;
        }

        return $this->looksLikeDestinationTripPlanningQuestion($question);
    }

    private function looksLikeGeneralTourAvailabilityQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        if (! str_contains($search, 'tour')) {
            return false;
        }

        foreach ([
            'tour gi',
            'co tour gi',
            'co nhung tour',
            'dang co tour',
            'tour nao',
            'danh sach tour',
            'cac tour',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeDestinationTripPlanningQuestion(string $question): bool
    {
        if ($this->extractKnownDestinationName($question) === '') {
            return false;
        }

        $search = $this->normalizeSearchText($question);

        foreach ([
            'muon di',
            'can di',
            'du dinh di',
            'co di',
            'di vao',
            'cho nguoi',
            'nguoi',
            'ngan sach',
            'budget',
            'thang',
            'ngay',
            'lich trinh',
            'tour',
            'du lich',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractKnownDestinationName(string $question): string
    {
        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';
        $destinations = [
            'nha trang' => 'Nha Trang',
            'da nang' => 'Đà Nẵng',
            'danang' => 'Đà Nẵng',
            'da lat' => 'Đà Lạt',
            'dalat' => 'Đà Lạt',
            'phu quoc' => 'Phú Quốc',
            'phuquoc' => 'Phú Quốc',
            'ha noi' => 'Hà Nội',
            'hanoi' => 'Hà Nội',
            'sa pa' => 'Sa Pa',
            'sapa' => 'Sa Pa',
            'phap' => 'Pháp',
            'france' => 'Pháp',
            'nhat ban' => 'Nhật Bản',
            'japan' => 'Nhật Bản',
            'han quoc' => 'Hàn Quốc',
            'korea' => 'Hàn Quốc',
            'thai lan' => 'Thái Lan',
            'thailand' => 'Thái Lan',
            'singapore' => 'Singapore',
            'uc' => 'Úc',
            'australia' => 'Úc',
            'my' => 'Mỹ',
            'usa' => 'Mỹ',
            'hoa ky' => 'Mỹ',
        ];

        foreach ($destinations as $needle => $label) {
            if ($this->containsNormalizedPhrase($search, $needle)) {
                return $label;
            }
        }

        return '';
    }

    private function extractGuestCount(string $question): string
    {
        $search = $this->normalizeSearchText($question);

        if (preg_match('/\b(\d{1,3})\s*(?:nguoi|khach|pax|guest|guests)\b/u', $search, $matches) === 1) {
            return (string) ($matches[1] ?? '');
        }

        return '';
    }

    private function extractTravelTimeText(string $question): string
    {
        $raw = mb_strtolower(trim($question));

        if (preg_match('/\b(tháng\s*\d{1,2}|thang\s*\d{1,2}|\d{1,2}\s*[\/\-]\s*\d{1,2})\b/iu', $raw, $matches) === 1) {
            $value = trim((string) ($matches[1] ?? ''));
            $value = preg_replace('/^thang\s*/iu', 'tháng ', $value) ?? $value;

            return preg_replace('/\s+/u', ' ', $value) ?? $value;
        }

        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';

        $seasonLabels = [
            'le' => 'lễ',
            'tet' => 'Tết',
            'he' => 'hè',
            'cao diem' => 'mùa cao điểm',
            'noel' => 'Noel',
            'giang sinh' => 'Giáng sinh',
        ];

        foreach ($seasonLabels as $needle => $label) {
            if ($this->containsNormalizedPhrase($search, $needle)) {
                return $label;
            }
        }

        return '';
    }

    private function extractBudgetText(string $question): string
    {
        $raw = mb_strtolower(str_replace(',', '.', trim($question)));

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*(?:tỷ|ty|tỉ|ti|billion)\b/iu', $raw, $matches) === 1) {
            return trim((string) ($matches[1] ?? '')) . ' tỷ';
        }

        if (preg_match('/\b(\d+(?:\.\d+)?)\s*(?:tr|triệu|trieu|m|million)\b/iu', $raw, $matches) === 1) {
            return trim((string) ($matches[1] ?? '')) . 'tr';
        }

        $search = $this->normalizeSearchText($question);

        if (preg_match('/\b(\d+(?:[\.,]\d+)?)\s*(?:tr|trieu|m|million)\b/u', $search, $matches) === 1) {
            return trim((string) ($matches[1] ?? '')) . 'tr';
        }

        if (preg_match('/\b(\d{6,})\s*(?:vnd|dong|d)?\b/u', $search, $matches) === 1) {
            return trim((string) ($matches[1] ?? '')) . 'đ';
        }

        return '';
    }

    private function looksLikeUpcomingDepartureQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach ([
            'sap khoi hanh',
            'sắp khởi hành',
            'gan khoi hanh',
            'gần khởi hành',
            'khoi hanh som',
            'khởi hành sớm',
            'hien tai tour nao sap khoi hanh',
            'tour nao sap khoi hanh',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeVisaQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach ([
            'visa',
            'xin visa',
            'lam visa',
            'làm visa',
            'ho so visa',
            'hồ sơ visa',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeVisaProcessingTimeQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);
        $collapsed = str_replace(' ', '', $search);

        if ($this->looksLikeVisaCostQuestion($question) && ! $this->containsVisaTimeSignal($search)) {
            return false;
        }

        foreach ([
            'bao lau',
            'mat bao lau',
            'thoi gian xu ly',
            'xu ly bao lau',
            'bao nhieu ngay',
            'trong bao',
            'lam trong bao',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        foreach ([
            'thoigianxuly',
            'xulybaolau',
            'matbaolau',
            'baonhieungay',
            'lamtrongbao',
        ] as $needle) {
            if (str_contains($collapsed, $needle)) {
                return true;
            }
        }

        $normalized = mb_strtolower($question);

        foreach ([
            'bao lau',
            'bao lâu',
            'mat bao lau',
            'mất bao lâu',
            'thoi gian xu ly',
            'thời gian xử lý',
            'xu ly bao lau',
            'xử lý bao lâu',
            'bao nhieu ngay',
            'bao nhiêu ngày',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        $search = $this->normalizeSearchText($question);
        $tokens = preg_split('/\s+/u', trim($search)) ?: [];

        $hasBao = in_array('bao', $tokens, true);
        $hasTimeWord = false;

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            if (
                str_starts_with($token, 'lau') ||
                str_starts_with($token, 'lau') ||
                str_starts_with($token, 'alu') ||
                str_starts_with($token, 'thoigian') ||
                str_starts_with($token, 'thoilian') ||
                str_starts_with($token, 'xuly') ||
                str_starts_with($token, 'ngay')
            ) {
                $hasTimeWord = true;
                break;
            }
        }

        if ($hasBao && $hasTimeWord) {
            return true;
        }

        if (! $this->looksLikeVisaCostQuestion($question) && preg_match('/\\b(trong\\s+)?bao\\s+\\S{1,8}\\b/u', $search) === 1) {
            return true;
        }

        return false;
    }

    private function containsVisaTimeSignal(string $search): bool
    {
        foreach ([
            'bao lau',
            'mat bao lau',
            'thoi gian',
            'xu ly',
            'bao nhieu ngay',
            'may ngay',
            'trong bao lau',
            'lam trong bao lau',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeVisaCostQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        foreach ([
            'chi phi',
            'le phi',
            'phi visa',
            'gia visa',
            'bao nhieu tien',
            'ton bao nhieu',
            'cost',
            'fee',
            'price',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeSchengenVisaQuestion(string $question): bool
    {
        $search = ' ' . trim($this->normalizeSearchText($question)) . ' ';

        foreach ([
            'schengen',
            'phap',
            'france',
            'thuy si',
            'thuy sy',
            'switzerland',
            'italy',
            'italia',
            'chau au',
            'tay au',
            'europe',
        ] as $needle) {
            if ($this->containsNormalizedPhrase($search, $needle)) {
                return true;
            }
        }

        return $this->containsNormalizedPhrase($search, 'y');
    }

    private function looksLikePaymentQuestion(string $question): bool
    {
        $normalized = $this->normalizeSearchText($question);

        foreach ([
            'thanh toan',
            'thanh toán',
            'payment',
            'paypal',
            'vietqr',
            'momo',
            'zalo pay',
            'zalopay',
            'dat coc',
            'chuyen khoan',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeCustomTourQuestion(string $question): bool
    {
        $normalized = $this->normalizeSearchText($question);

        foreach ([
            'tour theo yeu cau',
            'tour theo yêu cầu',
            'tao tour',
            'tạo tour',
            'thiet ke hanh trinh',
            'thiết kế hành trình',
            'hanh trinh rieng',
            'lich trinh rieng',
            'tour rieng',
            'khong co tour',
            'khong tim thay tour',
            'khong co hanh trinh',
            'khong co lich trinh',
            'toi muon tour rieng',
            'toi muon lich trinh rieng',
            'custom tour',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeMiceQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        foreach ([
            'mice',
            'hoi nghi',
            'hoi thao',
            'incentive',
            'team building',
            'gala dinner',
            'congress',
            'symposium',
            'su kien doanh nghiep',
            'khach hang doanh nghiep',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeHotelQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach ([
            'khach san',
            'khách sạn',
            'hotel',
            'dat phong',
            'đặt phòng',
            'luu tru',
            'lưu trú',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTransportQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach ([
            'van chuyen',
            'vận chuyển',
            'dua don',
            'đưa đón',
            'xe dua don',
            'xe đưa đón',
            'airport transfer',
            'shuttle',
            'transport',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTravelReferenceQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        if ($this->looksLikeTourQuestion($question) || $this->looksLikePaymentQuestion($question) || $this->looksLikeCustomTourQuestion($question)) {
            return false;
        }

        foreach ([
            'kinh nghiem du lich',
            'kinh nghiệm du lịch',
            'diem den noi bat',
            'điểm đến nổi bật',
            'nen di dau',
            'nên đi đâu',
            'mua nao dep',
            'mùa nào đẹp',
            'an gi',
            'ăn gì',
            'choi gi',
            'chơi gì',
            'co gi hay',
            'có gì hay',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractReferenceTopic(string $question): string
    {
        $question = trim(preg_replace('/\s+/u', ' ', $question) ?? $question);
        return $question;
    }

    /**
     * @param array<string, mixed> $chatState
     */
    private function shouldPreferLastTourContext(string $locale, string $question, array $chatState): bool
    {
        if ($this->getLastMatchedTourFromState($locale, $chatState) === null) {
            return false;
        }

        if ($this->looksLikeCurrentTourFollowUp($question)) {
            return true;
        }

        if ($this->referencesCurrentTour($question)) {
            return true;
        }

        if ($this->extractLocationFocusTargets($locale, $question) !== []) {
            return false;
        }

        $normalized = mb_strtolower($question);

        foreach ([
            'gia',
            'giá',
            'bao nhieu',
            'bao nhiêu',
            'khoi hanh',
            'khởi hành',
            'thoi luong',
            'thời lượng',
            'lich trinh',
            'lịch trình',
            'diem den',
            'điểm đến',
            'dia diem',
            'địa điểm',
            'di qua',
            'đi qua',
            'co gi hay',
            'có gì hay',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function referencesCurrentTour(string $question): bool
    {
        if ($this->looksLikeCurrentTourFollowUp($question)) {
            return true;
        }

        $normalized = mb_strtolower($question);

        foreach ([
            'tour này',
            'tour nay',
            'tour đó',
            'tour do',
            'tour này ',
            'chương trình này',
            'chuong trinh nay',
            'hành trình này',
            'hanh trinh nay',
            'này đi qua',
            'nay di qua',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeCurrentTourFollowUp(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        foreach ([
            'tour co gi',
            'co gi',
            'chi tiet chuong trinh',
            'chuong trinh chi tiet',
            'chi tiet lich trinh',
            'chi tiet tour',
            'noi dung chuong trinh',
            'noi dung tour',
            'chuong trinh co gi',
            'tour gom nhung gi',
            'bao gom nhung gi',
            'lich trinh the nao',
            'di cho nao',
            'choi gi',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeDestinationListQuestion(string $question): bool
    {
        $normalized = mb_strtolower($question);

        foreach ([
            'điểm đến nào',
            'diem den nao',
            'địa điểm nào',
            'dia diem nao',
            'các điểm nào',
            'cac diem nao',
            'đi các điểm',
            'di cac diem',
            'đi những điểm',
            'di nhung diem',
            'đi qua',
            'di qua',
            'đi đâu',
            'di dau',
            'ghé đâu',
            'ghe dau',
            'ghé những đâu',
            'ghe nhung dau',
            'tham quan đâu',
            'tham quan dau',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTourHighlightQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        foreach ([
            'co gi dac biet',
            'diem dac biet',
            'dac biet',
            'diem nhan',
            'diem noi bat',
            'noi bat',
            'co gi hay',
            'co gi dep',
            'hay o dau',
            'highlight',
            'highlights',
            'special',
            'unique',
            'interesting',
            'what is special',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTourPriceQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        foreach ([
            'gia',
            'gia tu',
            'bao nhieu tien',
            'chi phi',
            'price',
            'cost',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTourDepartureQuestion(string $question): bool
    {
        $search = $this->normalizeSearchText($question);

        foreach ([
            'khoi hanh',
            'ngay nao',
            'ngay di',
            'lich di',
            'departure',
            'depart',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeTourContentQuestion(string $question): bool
    {
        if ($this->looksLikeTourHighlightQuestion($question)) {
            return true;
        }

        $search = $this->normalizeSearchText($question);

        foreach ([
            'lich trinh',
            'co gi hay',
            'co gi dac biet',
            'diem dac biet',
            'dac biet',
            'diem nhan',
            'diem noi bat',
            'noi bat',
            'co gi dep',
            'hay o dau',
            'tham quan',
            'trai nghiem',
            'noi dung',
            'highlight',
            'highlights',
            'special',
            'unique',
            'interesting',
            'what is special',
        ] as $needle) {
            if (str_contains($search, $needle)) {
                return true;
            }
        }

        $normalized = mb_strtolower($question);

        foreach (['lịch trình', 'lich trinh', 'có gì hay', 'co gi hay', 'điểm nổi bật', 'diem noi bat', 'tham quan', 'trải nghiệm', 'trai nghiem', 'nội dung', 'noi dung'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractTourSearchQuery(string $question): string
    {
        $query = mb_strtolower(trim($question));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $query);
        $query = is_string($ascii) && $ascii !== '' ? $ascii : $query;
        $query = preg_replace('/[^a-z0-9\s]+/i', ' ', $query) ?? '';

        $stopwords = [
            'co', 'khong', 'hong', 'tour', 'xin', 'thong', 'tin', 'ngay', 'khoi', 'hanh', 'va', 'gia',
            'voi', 'giup', 'toi', 'website', 'travel', 'plus', 'cho', 'em', 'anh', 'chi', 've', 'cua',
            'nhung', 'nao', 'the', 'duoc', 'khong', 'hay', 'co', 'ko', 'gia', 'tour', 'lich', 'trinh',
            'diem', 'noi', 'bat', 'dac', 'biet', 'special', 'highlight', 'highlights', 'unique',
            'ben', 'ban', 'minh', 'muon', 'can', 'di', 'nguoi', 'khach', 'ngan', 'sach', 'du', 'kien',
            'thang', 'vao', 'khoang', 'duoc', 'la', 'cho', 'pax', 'budget',
        ];

        $tokens = preg_split('/\s+/u', trim($query)) ?: [];
        $filtered = [];

        foreach ($tokens as $token) {
            if ($token === '' || in_array($token, $stopwords, true) || mb_strlen($token) < 2) {
                continue;
            }

            $filtered[] = $token;
        }

        if ($filtered === []) {
            return '';
        }

        return implode(' ', array_slice($filtered, 0, 4));
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $text = $this->normalizeSearchText($text);
        $tokens = preg_split('/\s+/u', trim($text)) ?: [];
        $tokens = array_values(array_filter($tokens, static fn (string $token): bool => mb_strlen($token) >= 2));

        return array_values(array_unique($tokens));
    }

    private function normalizeSearchText(string $text): string
    {
        $text = mb_strtolower($text);
        $text = strtr($text, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ]);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        if (is_string($ascii) && $ascii !== '') {
            $text = $ascii;
        }

        return preg_replace('/[^a-z0-9\s]+/i', ' ', $text) ?? '';
    }

    /**
     * @return list<array{title: string, departure: string, price_label: string, duration_label: string, url: string, score: int, slug: string, tour_type: string}>
     */
    private function getUpcomingDepartureTours(string $locale, int $limit = 3): array
    {
        if (! $this->db->tableExists('tours') || ! $this->db->tableExists('tour_translations') || ! $this->db->tableExists('tour_departures')) {
            return [];
        }

        $today = date('Y-m-d');
        $rows = $this->db->table('tours t')
            ->select('
                t.id,
                t.tour_type,
                t.duration_days,
                t.duration_nights,
                t.base_price,
                tt.name AS title,
                tt.slug,
                MIN(td.departure_date) AS departure_date
            ', false)
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $this->db->escape($locale), 'inner')
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'inner')
            ->where('t.status', 'published')
            ->where('td.departure_date >=', $today)
            ->groupBy('t.id, t.tour_type, t.duration_days, t.duration_nights, t.base_price, tt.name, tt.slug')
            ->orderBy('MIN(td.departure_date)', 'ASC', false)
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        $matches = [];

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $tourType = (string) ($row['tour_type'] ?? 'outbound');

            if ($slug === '' || $title === '') {
                continue;
            }

            $locationSlug = $tourType === 'inbound' ? 'viet-nam' : 'diem-den';
            $url = $tourType === 'inbound'
                ? localized_url('tour-trong-nuoc/' . $locationSlug . '/tour/' . $slug)
                : localized_url('tour-nuoc-ngoai/' . $locationSlug . '/' . $slug);

            $matches[] = [
                'title' => $title,
                'departure_date' => (string) ($row['departure_date'] ?? ''),
                'departure' => $this->formatDisplayDate((string) ($row['departure_date'] ?? '')),
                'price_label' => $this->formatMoneyLabel((float) ($row['base_price'] ?? 0)),
                'duration_label' => $this->formatDurationLabel(
                    (int) ($row['duration_days'] ?? 0),
                    (int) ($row['duration_nights'] ?? 0),
                    $locale
                ),
                'url' => $url,
                'score' => 100,
                'slug' => $slug,
                'tour_type' => $tourType,
            ];
        }

        return $matches;
    }

    /**
     * @return list<array{title: string, departure: string, price_label: string, duration_label: string, url: string, score: int, slug: string, tour_type: string}>
     */
    private function getPublishedTours(string $locale, int $limit = 5): array
    {
        if (! $this->db->tableExists('tours') || ! $this->db->tableExists('tour_translations') || ! $this->db->tableExists('tour_departures')) {
            return [];
        }

        $today = date('Y-m-d');
        $rows = $this->db->table('tours t')
            ->select('
                t.id,
                t.tour_type,
                t.duration_days,
                t.duration_nights,
                t.base_price,
                tt.name AS title,
                tt.slug,
                MIN(td.departure_date) AS departure_date
            ', false)
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $this->db->escape($locale), 'inner')
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'inner')
            ->where('t.status', 'published')
            ->where('td.departure_date >=', $today)
            ->groupBy('t.id, t.tour_type, t.duration_days, t.duration_nights, t.base_price, tt.name, tt.slug')
            ->orderBy('MIN(td.departure_date)', 'ASC', false)
            ->orderBy('t.id', 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        $matches = [];

        foreach ($rows as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $tourType = (string) ($row['tour_type'] ?? 'outbound');

            if ($slug === '' || $title === '') {
                continue;
            }

            $locationSlug = $tourType === 'inbound' ? 'viet-nam' : 'diem-den';
            $url = $tourType === 'inbound'
                ? localized_url('tour-trong-nuoc/' . $locationSlug . '/tour/' . $slug)
                : localized_url('tour-nuoc-ngoai/' . $locationSlug . '/' . $slug);

            $matches[] = [
                'title' => $title,
                'departure_date' => (string) ($row['departure_date'] ?? ''),
                'departure' => $this->formatDisplayDate((string) ($row['departure_date'] ?? '')),
                'price_label' => $this->formatMoneyLabel((float) ($row['base_price'] ?? 0)),
                'duration_label' => $this->formatDurationLabel(
                    (int) ($row['duration_days'] ?? 0),
                    (int) ($row['duration_nights'] ?? 0),
                    $locale
                ),
                'url' => $url,
                'score' => 100,
                'slug' => $slug,
                'tour_type' => $tourType,
            ];
        }

        return $matches;
    }

    /**
     * @return list<array{id: int, type: string, name: string}>
     */
    private function extractLocationFocusTargets(string $locale, string $question): array
    {
        if (! $this->db->tableExists('locations') || ! $this->db->tableExists('location_translations')) {
            return [];
        }

        $normalizedQuestion = ' ' . trim($this->normalizeSearchText($question)) . ' ';
        $rows = $this->db->table('locations l')
            ->select('l.id, l.type, lt.name, lt.slug')
            ->join('location_translations lt', 'lt.location_id = l.id AND lt.locale = ' . $this->db->escape($locale), 'inner')
            ->whereIn('l.type', ['continent', 'country', 'province'])
            ->get()
            ->getResultArray();

        $targets = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalizedName = trim($this->normalizeSearchText($name));
            $slug = trim((string) ($row['slug'] ?? ''));
            $normalizedSlug = trim($this->normalizeSearchText($slug));

            if (($normalizedName === '' || mb_strlen($normalizedName) < 2) && ($normalizedSlug === '' || mb_strlen($normalizedSlug) < 2)) {
                continue;
            }

            $nameMatched = $normalizedName !== '' && str_contains($normalizedQuestion, ' ' . $normalizedName . ' ');
            $slugMatched = $normalizedSlug !== '' && str_contains($normalizedQuestion, ' ' . $normalizedSlug . ' ');

            if ($nameMatched || $slugMatched) {
                $targets[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'type' => (string) ($row['type'] ?? ''),
                    'name' => $normalizedName !== '' ? $normalizedName : $normalizedSlug,
                ];
            }
        }

        usort($targets, static function (array $a, array $b): int {
            $priority = ['country' => 3, 'province' => 2, 'continent' => 1];
            $typeCompare = ($priority[$b['type']] ?? 0) <=> ($priority[$a['type']] ?? 0);

            if ($typeCompare !== 0) {
                return $typeCompare;
            }

            return mb_strlen($b['name']) <=> mb_strlen($a['name']);
        });

        $unique = [];

        foreach ($targets as $target) {
            $unique[$target['type'] . ':' . $target['id']] = $target;
        }

        return array_values($unique);
    }

    /**
     * @param list<array{id: int, type: string, name: string}> $focusTargets
     */
    private function matchesLocationTargets(array $row, string $haystack, array $focusTargets): bool
    {
        $destinationIds = $this->parseIdList((string) ($row['destination_ids'] ?? ''));
        $parentIds = $this->parseIdList((string) ($row['parent_ids'] ?? ''));
        $grandparentIds = $this->parseIdList((string) ($row['grandparent_ids'] ?? ''));

        foreach ($focusTargets as $target) {
            $targetId = (int) ($target['id'] ?? 0);
            $targetType = (string) ($target['type'] ?? '');
            $targetName = (string) ($target['name'] ?? '');

            if ($targetId <= 0) {
                continue;
            }

            if ($targetType === 'country' && (in_array($targetId, $destinationIds, true) || in_array($targetId, $parentIds, true))) {
                return true;
            }

            if ($targetType === 'province' && in_array($targetId, $destinationIds, true)) {
                return true;
            }

            if ($targetType === 'continent' && (in_array($targetId, $destinationIds, true) || in_array($targetId, $parentIds, true) || in_array($targetId, $grandparentIds, true))) {
                return true;
            }

            if ($targetName !== '' && str_contains($haystack, $targetName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    private function parseIdList(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $csv))));
    }

    private function formatDisplayDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $timestamp = strtotime($date);

        return $timestamp ? date('d/m/Y', $timestamp) : $date;
    }

    /**
     * @param array<string, mixed> $tour
     */
    private function getTourAvailabilityStatus(array $tour): string
    {
        $departure = trim((string) ($tour['departure_date'] ?? ''));
        if ($departure === '') {
            $departure = trim((string) ($tour['departure'] ?? ''));
        }

        if ($departure === '') {
            return 'unknown';
        }

        return $this->isUpcomingDepartureValue($departure) ? 'upcoming' : 'expired';
    }

    private function buildTourAvailabilityNote(string $status, string $locale = 'vi'): string
    {
        if ($locale === 'en') {
            return match ($status) {
                'upcoming' => 'The departure is still valid and can be used for consultation.',
                'expired' => 'This departure has passed and should not be presented as actively available.',
                default => 'The departure date is unclear and should be rechecked before quoting.',
            };
        }

        return match ($status) {
            'upcoming' => 'Lịch khởi hành còn hiệu lực, có thể dùng để tư vấn cho khách.',
            'expired' => 'Lịch khởi hành này đã qua, không nên đề xuất như tour còn bán.',
            default => 'Chưa có lịch khởi hành rõ ràng, cần kiểm tra lại trước khi chốt.',
        };
    }

    private function isUpcomingDepartureValue(string $date): bool
    {
        $date = trim($date);

        if ($date === '') {
            return false;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches) === 1) {
            $date = sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return false;
        }

        return $timestamp >= strtotime(date('Y-m-d'));
    }

    private function formatMoneyLabel(float $amount): string
    {
        if ($amount <= 0) {
            return '';
        }

        return number_format($amount, 0, ',', '.') . 'đ';
    }

    private function formatDurationLabel(int $days, int $nights, string $locale): string
    {
        if ($days <= 0 && $nights <= 0) {
            return '';
        }

        if ($locale === 'en') {
            return sprintf('%02d Days / %02d Nights', max(0, $days), max(0, $nights));
        }

        return sprintf('%02d Ngày / %02d Đêm', max(0, $days), max(0, $nights));
    }

    /**
     * @param list<array<string, mixed>> $itineraryDays
     * @return list<string>
     */
    private function extractRouteStops(array $itineraryDays): array
    {
        $stops = [];

        foreach ($itineraryDays as $day) {
            $title = html_entity_decode((string) ($day['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($title === '') {
                continue;
            }

            $parts = preg_split('/\s*[-–—]+\s*/u', $title) ?: [];

            foreach ($parts as $part) {
                $stop = trim($part);

                if ($stop === '' || $this->isGenericTravelStop($stop)) {
                    continue;
                }

                $stop = preg_replace('/\s*\(.*?\)\s*/u', '', $stop) ?? $stop;
                $stop = trim($stop);

                if ($stop === '' || $this->isGenericTravelStop($stop)) {
                    continue;
                }

                $key = mb_strtolower($this->normalizeSearchText($stop));
                $stops[$key] = $stop;
            }
        }

        return array_values($stops);
    }

    /**
     * @return list<string>
     */
    private function extractRouteStopsFromTourTitle(string $title): array
    {
        $clean = preg_replace('/\([^)]*\)|\b\d+\s*n\s*\d+\s*[dđ]\b/iu', '', $title) ?? $title;
        $parts = preg_split('/\s*(?:-|–|—|\||,|:)\s*/u', $clean) ?: [];
        $stops = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);
            $part = preg_replace('/\s+/u', ' ', $part) ?? $part;

            if ($part === '' || mb_strlen($part) < 3 || $this->isGenericTravelStop($part)) {
                continue;
            }

            $key = mb_strtolower($this->normalizeSearchText($part));
            $stops[$key] = $part;
        }

        return array_slice(array_values($stops), 0, 8);
    }

    /**
     * @param list<array<string, mixed>> $itineraryDays
     * @return list<string>
     */
    private function extractAttractionHighlights(array $itineraryDays): array
    {
        $items = [];

        foreach ($itineraryDays as $day) {
            $description = html_entity_decode((string) ($day['description'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($description === '') {
                continue;
            }

            if (preg_match_all('/<strong>(.*?)<\/strong>/isu', $description, $matches)) {
                foreach (($matches[1] ?? []) as $match) {
                    $name = trim($this->stripHtml((string) $match));

                    if ($name === '' || $this->isGenericAttractionText($name)) {
                        continue;
                    }

                    $key = mb_strtolower($this->normalizeSearchText($name));
                    $items[$key] = $name;
                }
            }
        }

        return array_values($items);
    }

    private function isGenericTravelStop(string $value): bool
    {
        $normalized = trim($this->normalizeSearchText($value));
        $generic = [
            'tphcm', 'tp hcm', 'ho chi minh city', 'airport cdg', 'airport', 'rome city tour',
            'den thanh pho ho chi minh', 'ho chi minh', 'city tour',
        ];

        return $normalized === '' || in_array($normalized, $generic, true);
    }

    private function isGenericAttractionText(string $value): bool
    {
        $normalized = trim($this->normalizeSearchText($value));

        foreach (['thu do', 'nha hat', 'quoc gia', 'noi tieng', 'thanh pho', 'khu pho'] as $generic) {
            if ($normalized === $generic) {
                return true;
            }
        }

        return $normalized === '';
    }

    private function isOperationalSentence(string $normalizedSentence): bool
    {
        foreach ([
            'lam thu tuc',
            'tap trung tai san bay',
            'qua canh',
            'an sang tai khach san',
            'tra phong',
            'nghi dem tren may bay',
            'xe don doan',
            'den san bay',
            'lam thu tuc nhap canh',
        ] as $needle) {
            if (str_contains($normalizedSentence, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function makeLocalizedUrl(string $path, string $locale): string
    {
        $normalized = ltrim($path, '/');

        if ($locale === 'en' && ! str_starts_with($normalized, 'en/')) {
            $normalized = 'en/' . $normalized;
        }

        return base_url($normalized);
    }
}
