<?php

namespace App\Services;

use App\Data\AboutPageContent;
use App\Data\LegalPageCatalog;
use App\Data\MicePageContent;
use App\Data\OfficeLocationCatalog;
use App\Data\ServicePageCatalog;
use App\Data\VisaPageContent;
use CodeIgniter\Database\BaseConnection;
use Throwable;

class WebsiteKnowledgeService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
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

        if ($this->looksLikeMiceQuestion($question)) {
            return $this->buildStructuredMiceFacts($locale);
        }

        if ($this->looksLikeGeneralTourAvailabilityQuestion($question)) {
            $publishedTours = $this->getPublishedTours($locale, 5);

            if ($publishedTours !== []) {
                return $this->buildStructuredTourListFacts($locale, $publishedTours, 'general_availability');
            }
        }

        $destinationTripQuestion = $this->looksLikeDestinationTripPlanningQuestion($question);

        if (! $this->looksLikeTourQuestion($question) && ! $this->referencesCurrentTour($question) && ! $destinationTripQuestion) {
            return null;
        }

        if ($this->looksLikeUpcomingDepartureQuestion($question)) {
            $upcomingTours = $this->getUpcomingDepartureTours($locale, 3);

            if ($upcomingTours !== []) {
                return $this->buildStructuredTourListFacts($locale, $upcomingTours, 'upcoming_departures');
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

        return $this->buildStructuredTourListFacts($locale, $matches);
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

        return [
            'type' => 'tour_detail',
            'intent' => $intent,
            'selected_tour' => $this->formatTourFactItem($tour),
            'tour' => [
                'title' => (string) ($tour['title'] ?? ''),
                'url' => (string) ($tour['url'] ?? ''),
                'departure' => (string) ($tour['departure'] ?? ''),
                'price_label' => (string) ($tour['price_label'] ?? ''),
                'duration_label' => (string) ($tour['duration_label'] ?? ''),
                'overview' => $this->summarizeText($overview, 320, 3),
                'route_stops' => array_slice($routeStops, 0, 12),
                'attraction_highlights' => array_slice($attractions, 0, 12),
                'itinerary_highlights' => $itineraryHighlights,
            ],
            'sources' => [[
                'title' => (string) ($tour['title'] ?? ''),
                'url' => (string) ($tour['url'] ?? ''),
            ]],
            'chat_state' => [
                'last_tour_slug' => (string) ($tour['slug'] ?? ''),
                'last_tour_type' => (string) ($tour['tour_type'] ?? ''),
                'last_tour_title' => (string) ($tour['title'] ?? ''),
                'last_tour_departure' => (string) ($tour['departure'] ?? ''),
                'last_tour_price_label' => (string) ($tour['price_label'] ?? ''),
                'last_tour_duration_label' => (string) ($tour['duration_label'] ?? ''),
                'last_tour_url' => (string) ($tour['url'] ?? ''),
                'last_locale' => $locale,
            ],
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
    private function buildStructuredTourListFacts(string $locale, array $matches, string $intent = 'availability'): ?array
    {
        if ($matches === []) {
            return null;
        }

        return [
            'type' => 'tour_list',
            'intent' => $intent,
            'selected_tour' => $this->formatTourFactItem($matches[0]),
            'tours' => array_map(fn (array $tour): array => $this->formatTourFactItem($tour), array_slice($matches, 0, 3)),
            'sources' => array_values(array_filter(array_map(static function (array $tour): ?array {
                $title = (string) ($tour['title'] ?? '');
                $url = (string) ($tour['url'] ?? '');

                if ($title === '' || $url === '') {
                    return null;
                }

                return ['title' => $title, 'url' => $url];
            }, array_slice($matches, 0, 3)))),
            'chat_state' => [
                'last_tour_slug' => (string) ($matches[0]['slug'] ?? ''),
                'last_tour_type' => (string) ($matches[0]['tour_type'] ?? ''),
                'last_tour_title' => (string) ($matches[0]['title'] ?? ''),
                'last_tour_departure' => (string) ($matches[0]['departure'] ?? ''),
                'last_tour_price_label' => (string) ($matches[0]['price_label'] ?? ''),
                'last_tour_duration_label' => (string) ($matches[0]['duration_label'] ?? ''),
                'last_tour_url' => (string) ($matches[0]['url'] ?? ''),
                'last_locale' => $locale,
            ],
        ];
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
            'sources' => [[
                'title' => $locale === 'en' ? 'Tour search' : 'Tìm kiếm tour',
                'url' => $this->makeLocalizedUrl($locale === 'en' ? 'tour-search' : 'tim-kiem-tour', $locale),
            ]],
            'chat_state' => [
                'last_tour_slug' => '',
                'last_tour_type' => '',
                'last_tour_title' => '',
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
     * @return array<string, string>
     */
    private function formatTourFactItem(array $tour): array
    {
        return [
            'title' => (string) ($tour['title'] ?? ''),
            'slug' => (string) ($tour['slug'] ?? ''),
            'tour_type' => (string) ($tour['tour_type'] ?? ''),
            'departure' => (string) ($tour['departure'] ?? ''),
            'price_label' => (string) ($tour['price_label'] ?? ''),
            'duration_label' => (string) ($tour['duration_label'] ?? ''),
            'url' => (string) ($tour['url'] ?? ''),
        ];
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
        $statePriceLabel = trim((string) ($chatState['last_tour_price_label'] ?? ''));
        $stateDurationLabel = trim((string) ($chatState['last_tour_duration_label'] ?? ''));
        $stateUrl = trim((string) ($chatState['last_tour_url'] ?? ''));
        $detailUrl = trim((string) ($detail['url'] ?? ''));

        return [
            'title' => (string) ($detail['title'] ?? ''),
            'slug' => $slug,
            'tour_type' => (string) ($detail['tour_type'] ?? $tourType),
            'departure' => $departureDate !== '' ? $this->formatDisplayDate($departureDate) : $stateDeparture,
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

        if (! $this->db->tableExists('tours') || ! $this->db->tableExists('tour_translations')) {
            return [];
        }

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
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'left')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'left')
            ->join('locations dl', 'dl.id = tdst.location_id', 'left')
            ->join('locations dlp', 'dlp.id = dl.parent_id', 'left')
            ->join('locations dlgp', 'dlgp.id = dlp.parent_id', 'left')
            ->join('location_translations dltn', 'dltn.location_id = dl.id AND dltn.locale = ' . $this->db->escape($locale), 'left')
            ->where('t.status', 'published');

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
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'left')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'inner')
            ->join('locations dl', 'dl.id = tdst.location_id', 'inner')
            ->join('locations dlp', 'dlp.id = dl.parent_id', 'left')
            ->where('t.status', 'published');

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
        $search = $this->normalizeSearchText($question);

        if (preg_match('/\b(thang\s*\d{1,2}|\d{1,2}\s*\/\s*\d{1,2}|\d{1,2}\s*-\s*\d{1,2})\b/u', $search, $matches) === 1) {
            $value = trim((string) ($matches[1] ?? ''));

            return preg_replace('/^thang\s*/u', 'tháng ', $value) ?? $value;
        }

        return '';
    }

    private function extractBudgetText(string $question): string
    {
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
            ->where('DATE(td.departure_date) >=', $today)
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
        if (! $this->db->tableExists('tours') || ! $this->db->tableExists('tour_translations')) {
            return [];
        }

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
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'left')
            ->where('t.status', 'published')
            ->groupBy('t.id, t.tour_type, t.duration_days, t.duration_nights, t.base_price, tt.name, tt.slug')
            ->orderBy('MIN(td.departure_date) IS NULL', 'ASC', false)
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
