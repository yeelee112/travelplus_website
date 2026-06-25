<?php

namespace App\Services;

use App\Data\LocalizedPathCatalog;
use RuntimeException;

class GeminiWebsiteChatService
{
    private const GEMINI_MAX_OUTPUT_TOKENS = 1200;
    private const GEMINI_TIMEOUT_SECONDS = 60;

    private string $apiKey;
    private string $model;
    private WebsiteKnowledgeService $knowledgeService;

    public function __construct()
    {
        $this->apiKey = trim((string) env('gemini.apiKey', ''), " \t\n\r\0\x0B\"'");
        $this->model = trim((string) env('gemini.model', 'gemini-2.5-flash'), " \t\n\r\0\x0B\"'");
        $this->knowledgeService = new WebsiteKnowledgeService();
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @param list<array{role: string, text: string}> $history
     * @param array<string, mixed> $chatState
     * @return array{message: string, sources: list<array{title: string, url: string}>, chat_state?: array<string, mixed>, debug_meta?: array<string, mixed>}
     */
    public function answer(string $locale, string $message, array $history = [], array $chatState = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('AI provider credentials are missing.');
        }

        $directResponse = $this->buildDirectConsultationResponse($locale, $message, $history);
        if ($directResponse !== null) {
            return [
                'message' => $directResponse['message'],
                'sources' => $directResponse['sources'],
                'debug_meta' => [
                    'branch' => 'direct_consultation',
                    'intent' => $directResponse['intent'],
                    'source_count' => count($directResponse['sources']),
                ],
            ];
        }

        $referenceFacts = $this->knowledgeService->getReferenceFacts($locale, $message, $chatState);

        if ($referenceFacts !== null) {
            if (($referenceFacts['type'] ?? '') === 'reference_visa_timeline') {
                return [
                    'message' => $this->buildReferenceFallbackMessage($locale, $referenceFacts),
                    'sources' => $referenceFacts['sources'] ?? [],
                    'debug_meta' => [
                        'branch' => 'reference_mode_direct',
                        'facts_type' => (string) ($referenceFacts['type'] ?? ''),
                        'facts_intent' => (string) ($referenceFacts['intent'] ?? ''),
                        'source_count' => count($referenceFacts['sources'] ?? []),
                    ],
                ];
            }

            $prompt = $this->buildReferencePrompt($locale, $message, $history, $referenceFacts);
            try {
                $response = $this->normalizeChatMessage($this->requestGemini($prompt));
            } catch (RuntimeException $exception) {
                if (! $this->isRecoverableProviderError($exception)) {
                    throw $exception;
                }

                $response = $this->buildReferenceFallbackMessage($locale, $referenceFacts);
            }

            if ($this->looksIncompleteGenericResponse($response)) {
                $response = $this->buildReferenceFallbackMessage($locale, $referenceFacts);
            }

            return [
                'message' => $response,
                'sources' => $referenceFacts['sources'] ?? [],
                'debug_meta' => [
                    'branch' => 'reference_mode',
                    'facts_type' => (string) ($referenceFacts['type'] ?? ''),
                    'facts_intent' => (string) ($referenceFacts['intent'] ?? ''),
                    'source_count' => count($referenceFacts['sources'] ?? []),
                ],
            ];
        }

        $structuredFacts = $this->knowledgeService->getStructuredFacts($locale, $message, $chatState);

        if ($structuredFacts !== null) {
            $factsType = (string) ($structuredFacts['type'] ?? '');
            $factsIntent = (string) ($structuredFacts['intent'] ?? '');

            if (
                $factsType === 'company_strength'
                || $factsType === 'payment_support'
                || $factsType === 'custom_tour_support'
                || $factsType === 'mice_service'
                || ($factsType === 'visa_support' && $factsIntent === 'visa_cost')
                || ($factsType === 'tour_detail' && in_array($factsIntent, ['price', 'departure'], true))
            ) {
                return [
                    'message' => $this->buildFactsFallbackMessage($locale, $structuredFacts),
                    'sources' => $structuredFacts['sources'] ?? [],
                    'chat_state' => $structuredFacts['chat_state'] ?? [],
                    'debug_meta' => [
                        'branch' => 'structured_facts_direct',
                        'facts_type' => $factsType,
                        'facts_intent' => $factsIntent,
                        'source_count' => count($structuredFacts['sources'] ?? []),
                    ],
                ];
            }

            $prompt = $this->buildStructuredFactsPrompt($locale, $message, $history, $structuredFacts);
            $usedFactsFallback = false;
            $usedQuotaFallback = false;
            try {
                $response = $this->normalizeChatMessage($this->requestGemini($prompt));
            } catch (RuntimeException $exception) {
                if (! $this->isRecoverableProviderError($exception)) {
                    throw $exception;
                }

                $response = $this->buildFactsFallbackMessage($locale, $structuredFacts);
                $usedFactsFallback = true;
                $usedQuotaFallback = $this->isQuotaError($exception);
            }

            if ($this->looksIncompleteStructuredResponse($response, $structuredFacts)) {
                $response = $this->buildFactsFallbackMessage($locale, $structuredFacts);
                $usedFactsFallback = true;
            }

            return [
                'message' => $response,
                'sources' => $structuredFacts['sources'] ?? [],
                'chat_state' => $structuredFacts['chat_state'] ?? [],
                'debug_meta' => [
                    'branch' => 'structured_facts',
                    'facts_type' => (string) ($structuredFacts['type'] ?? ''),
                    'facts_intent' => (string) ($structuredFacts['intent'] ?? ''),
                    'used_quota_fallback' => $usedQuotaFallback,
                    'used_facts_fallback' => $usedFactsFallback,
                    'source_count' => count($structuredFacts['sources'] ?? []),
                ],
            ];
        }

        $context = $this->knowledgeService->getRelevantContext($locale, $message);
        $prompt = $this->buildPrompt($locale, $message, $history, $context['summary']);
        $usedContextFallback = false;
        $usedQuotaFallback = false;
        try {
            $response = $this->normalizeChatMessage($this->requestGemini($prompt));
        } catch (RuntimeException $exception) {
            if ($this->isTruncationError($exception)) {
                $response = $this->buildContextFallbackMessage($locale, $context['summary']);
                $usedContextFallback = true;
            } elseif (! $this->isRecoverableProviderError($exception)) {
                throw $exception;
            } else {
                $response = $locale === 'en'
                    ? 'The AI assistant is temporarily unavailable. Please try again later.'
                    : 'AI Travel Plus đang tạm thời không khả dụng. Vui lòng thử lại sau.';
                $usedQuotaFallback = true;
            }
        }

        if ($this->looksIncompleteGenericResponse($response)) {
            $response = $this->buildContextFallbackMessage($locale, $context['summary']);
            $usedContextFallback = true;
        }

        return [
            'message' => $response,
            'sources' => $context['sources'],
            'debug_meta' => [
                'branch' => 'generic_context',
                'used_quota_fallback' => $usedQuotaFallback,
                'used_context_fallback' => $usedContextFallback,
                'source_count' => count($context['sources'] ?? []),
            ],
        ];
    }

    /**
     * @param list<array{role: string, text: string}> $history
     * @param array<string, mixed> $facts
     */
    private function buildStructuredFactsPrompt(string $locale, string $message, array $history, array $facts): string
    {
        $languageRule = $locale === 'en'
            ? 'Reply only in English.'
            : 'Reply only in Vietnamese.';

        $historyLines = [];

        foreach (array_slice($history, -6) as $item) {
            $role = $item['role'] === 'assistant' ? 'Assistant' : 'User';
            $historyLines[] = $role . ': ' . trim((string) ($item['text'] ?? ''));
        }

        $factsJson = json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return implode("\n\n", array_filter([
            'You are the Travel Plus website assistant.',
            $languageRule,
            'The structured facts below are trusted website data. Use only these facts. Do not invent details.',
            'Answer naturally, concisely, and directly for the user intent.',
            'Act like a Travel Plus consultation agent, not a documentation summarizer.',
            'For service requests such as visa, hotel, transport, MICE, or custom tours, first confirm Travel Plus can help, then ask 2 to 4 missing details needed to advise or hand off to sales.',
            'If the facts do not exactly match the user request, say that clearly and offer the closest option or a custom consultation instead of pretending it matches.',
            'End consultation-style answers with a soft next step such as asking for phone/email or preferred travel date when relevant.',
            'If the user asks about itinerary highlights or destinations, summarize the meaningful places and experiences from the facts instead of repeating operational lines like airport procedures, breakfast, or hotel checkout.',
            'If structured facts intent is "highlights", explain what makes the selected tour notable. Do not answer by listing alternative tours.',
            'If multiple tours are listed, keep the answer easy to scan.',
            'If a follow-up references "this tour", use the selected tour in the structured facts.',
            'Do not say the website lacks information when the structured facts already contain relevant data.',
            'Write in plain text. Do not use markdown bold, markdown bullet markers, raw URLs, or citation markers like [1].',
            'Conversation history:',
            $historyLines !== [] ? implode("\n", $historyLines) : '(none)',
            'Structured facts:',
            $factsJson ?: '{}',
            'Latest user question:',
            trim($message),
        ]));
    }

    /**
     * @param list<array{role: string, text: string}> $history
     */
    private function buildPrompt(string $locale, string $message, array $history, string $context): string
    {
        $languageRule = $locale === 'en'
            ? 'Reply only in English.'
            : 'Reply only in Vietnamese.';

        $historyLines = [];

        foreach (array_slice($history, -6) as $item) {
            $role = $item['role'] === 'assistant' ? 'Assistant' : 'User';
            $historyLines[] = $role . ': ' . trim((string) ($item['text'] ?? ''));
        }

        return implode("\n\n", array_filter([
            'You are the Travel Plus website assistant.',
            $languageRule,
            'Only answer using the provided website context. Do not invent policies, prices, schedules, or service details.',
            'If the provided context clearly contains relevant information, answer directly and confidently from that context.',
            'Only say that the website data does not currently confirm the answer when none of the provided context is relevant.',
            'Act like a Travel Plus consultation agent. Do not just summarize the page.',
            'When the user asks for advice, ask for the minimum missing details needed to continue: travel date, number of guests, budget, destination, visa purpose, hotel standard, or phone/email depending on the request.',
            'If the website context is broad but not specific enough, say what is known and ask a focused follow-up question.',
            'Keep answers concise and practical. When relevant, mention the most relevant page links from the provided context.',
            'Write in plain text. Do not use markdown bold, markdown bullet markers, raw URLs, or citation markers like [1].',
            'Conversation history:',
            $historyLines !== [] ? implode("\n", $historyLines) : '(none)',
            'Website context:',
            $context,
            'Latest user question:',
            trim($message),
        ]));
    }

    /**
     * @param list<array{role: string, text: string}> $history
     * @param array<string, mixed> $facts
     */
    private function buildReferencePrompt(string $locale, string $message, array $history, array $facts): string
    {
        $languageRule = $locale === 'en'
            ? 'Reply only in English.'
            : 'Reply only in Vietnamese.';

        $historyLines = [];

        foreach (array_slice($history, -6) as $item) {
            $role = $item['role'] === 'assistant' ? 'Assistant' : 'User';
            $historyLines[] = $role . ': ' . trim((string) ($item['text'] ?? ''));
        }

        $factsJson = json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return implode("\n\n", array_filter([
            'You are the Travel Plus assistant.',
            $languageRule,
            'This question is eligible for external reference knowledge.',
            'You may use general knowledge beyond the website when needed.',
            'Act like a Travel Plus consultation agent. Keep the answer useful for a customer who may need a callback.',
            'Start the answer with exactly this label in the correct language:',
            $locale === 'en'
                ? 'Reference information outside website data:'
                : 'Thông tin tham khảo ngoài dữ liệu website:',
            'After that label, explain the external reference information first. Do not start the next sentence with "According to the Travel Plus website", "According to the website", "Theo website", or "Theo thông tin hiện có trên website".',
            'If website facts are provided, keep them in a separate final sentence that starts with "Website data:" in English or "Dữ liệu website:" in Vietnamese.',
            'Do not invent specific Travel Plus policies, prices, processing commitments, or promises that are not explicitly in website facts.',
            'Keep the answer under 180 words and finish with a complete sentence.',
            'Write in plain text. Do not use markdown bold, markdown bullet markers, raw URLs, or citation markers like [1].',
            'Conversation history:',
            $historyLines !== [] ? implode("\n", $historyLines) : '(none)',
            'Reference facts and any relevant website facts:',
            $factsJson ?: '{}',
            'Latest user question:',
            trim($message),
        ]));
    }

    /**
     * @return array{message: string, sources: list<array{title: string, url: string}>, intent: string}|null
     */
    private function buildDirectConsultationResponse(string $locale, string $message, array $history = []): ?array
    {
        if ($this->looksLikeContactRequest($message)) {
            return [
                'message' => $locale === 'en'
                    ? "You can send your request on the Contact page, or leave your phone number here so Travel Plus can call back.\n\nTo advise faster, please share: your destination or service need, expected travel date, number of guests and your phone/email."
                    : "Anh/chị có thể gửi yêu cầu ở trang Liên hệ, hoặc để lại số điện thoại ngay tại đây để Travel Plus gọi lại tư vấn.\n\nĐể tư vấn nhanh hơn, anh/chị cho em xin: nhu cầu/điểm đến, thời gian dự kiến, số lượng khách và số điện thoại hoặc email.",
                'sources' => [[
                    'title' => $locale === 'en' ? 'Contact Travel Plus' : 'Liên hệ Travel Plus',
                    'url' => LocalizedPathCatalog::url('contact', $locale),
                ]],
                'intent' => 'contact_request',
            ];
        }

        if ($this->looksLikeVisaConsultationRequest($message)) {
            $destination = $this->extractVisaDestination($message);
            $destinationText = $destination !== ''
                ? ($locale === 'en' ? ' for ' . $destination : ' đi ' . $destination)
                : '';

            return [
                'message' => $locale === 'en'
                    ? "Travel Plus can advise on visa documents{$destinationText}. To check the right file, please share: visa purpose, expected travel date, number of applicants, passport nationality and where the applicant is currently living.\n\nIf you leave your phone number, Travel Plus can call back to review the case faster."
                    : "Travel Plus có thể tư vấn hồ sơ visa{$destinationText}. Để kiểm tra đúng trường hợp, anh/chị cho em xin thêm: mục đích đi, ngày dự kiến đi, số người làm visa, quốc tịch hộ chiếu và hiện đang sinh sống ở đâu.\n\nNếu anh/chị để lại số điện thoại, Travel Plus có thể gọi lại để rà hồ sơ nhanh hơn.",
                'sources' => [[
                    'title' => $locale === 'en' ? 'Visa service' : 'Dịch vụ visa',
                    'url' => LocalizedPathCatalog::url('service.visa', $locale),
                ]],
                'intent' => 'visa_consultation',
            ];
        }

        if ($this->looksLikeHotelConsultationRequest($message)) {
            return [
                'message' => $locale === 'en'
                    ? "Travel Plus can help suggest hotels that fit the trip context. For a family stay near the beach, please share: check-in/check-out dates, preferred budget per night, hotel star level, room setup and whether you prefer My Khe/An Thuong or another area.\n\nWith 3 adults and 1 child, the team can check options such as a family room, connecting rooms or one double plus one extra bed depending on hotel policy."
                    : "Travel Plus có thể tư vấn khách sạn phù hợp với bối cảnh chuyến đi. Với gia đình ở gần biển, anh/chị cho em xin thêm: ngày nhận/trả phòng, ngân sách mỗi đêm, tiêu chuẩn sao mong muốn, cấu hình phòng và muốn ở khu Mỹ Khê/An Thượng hay khu vực khác.\n\nVới 3 người lớn + 1 bé, đội ngũ có thể kiểm tra phương án phòng gia đình, phòng nối cửa hoặc 1 phòng đôi kèm extra bed tùy chính sách khách sạn.",
                'sources' => [[
                    'title' => $locale === 'en' ? 'Hotel service' : 'Dịch vụ khách sạn',
                    'url' => LocalizedPathCatalog::url('service.hotels', $locale),
                ]],
                'intent' => 'hotel_consultation',
            ];
        }

        if ($this->looksLikeItinerarySuggestionRequest($message)) {
            $destination = $this->inferTripDestination($message, $history);
            $itinerary = $this->buildDomesticShortTripSuggestion($locale, $destination);
            $internationalItinerary = $itinerary === null
                ? $this->buildInternationalTripSuggestion($locale, $destination)
                : null;

            if ($itinerary !== null) {
                return [
                    'message' => $itinerary,
                    'sources' => [[
                        'title' => $locale === 'en' ? 'Tour search' : 'Tìm kiếm tour',
                        'url' => LocalizedPathCatalog::url('search', $locale),
                    ]],
                    'intent' => 'itinerary_suggestion',
                ];
            }

            if ($internationalItinerary !== null) {
                return [
                    'message' => $internationalItinerary,
                    'sources' => [[
                        'title' => $locale === 'en' ? 'Tour search' : 'Tìm kiếm tour',
                        'url' => LocalizedPathCatalog::url('search', $locale),
                    ]],
                    'intent' => 'international_itinerary_suggestion',
                ];
            }

            return [
                'message' => $locale === 'en'
                    ? "Travel Plus can suggest a 3-day itinerary, but I need the destination first. Please share where you want to go, travel date, number of guests and approximate budget."
                    : "Travel Plus có thể gợi ý lịch trình 3 ngày, nhưng em cần biết rõ điểm đến trước. Anh/chị cho em xin điểm muốn đi, ngày đi, số khách và ngân sách dự kiến.",
                'sources' => [[
                    'title' => $locale === 'en' ? 'Contact Travel Plus' : 'Liên hệ Travel Plus',
                    'url' => LocalizedPathCatalog::url('contact', $locale),
                ]],
                'intent' => 'itinerary_suggestion_needs_destination',
            ];
        }

        if ($this->looksLikeTourConsultationRequest($message)) {
            return [
                'message' => $locale === 'en'
                    ? "Travel Plus can advise a suitable tour or tailor-made itinerary. To match the trip properly, please share: destination, expected travel date, number of guests, preferred trip length and approximate budget.\n\nIf the website does not have an exact matching tour, Travel Plus can suggest the closest option or prepare a custom itinerary."
                    : "Travel Plus có thể tư vấn tour phù hợp hoặc thiết kế lịch trình riêng. Để khớp đúng nhu cầu, anh/chị cho em xin thêm: điểm đến, ngày đi dự kiến, số lượng khách, số ngày muốn đi và ngân sách khoảng bao nhiêu.\n\nNếu website chưa có tour khớp đúng 100%, Travel Plus có thể gợi ý tour gần nhất hoặc thiết kế lịch trình riêng.",
                'sources' => [[
                    'title' => $locale === 'en' ? 'Tour search' : 'Tìm kiếm tour',
                    'url' => LocalizedPathCatalog::url('search', $locale),
                ]],
                'intent' => 'tour_consultation',
            ];
        }

        return null;
    }

    private function normalizeIntentText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = is_string($converted) && $converted !== '' ? $converted : $text;

        return preg_replace('/[^a-z0-9\s]+/i', ' ', $text) ?? '';
    }

    private function looksLikeContactRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        foreach ([
            'lien he',
            'hotline',
            'so dien thoai',
            'sdt',
            'phone',
            'email',
            'goi lai',
            'contact',
            'tu van cho toi biet',
        ] as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return preg_match('/\b(dau|cho|co)\s+.*\b(lien he|sdt|so dien thoai)\b/u', $text) === 1;
    }

    private function looksLikeVisaConsultationRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return str_contains($text, 'visa')
            && preg_match('/\b(tu van|xin|lam|ho tro|can|muon|di)\b/u', $text) === 1
            && ! $this->looksLikeVisaSpecificFactRequest($message)
            && ! $this->looksLikeContactRequest($message);
    }

    private function looksLikeVisaSpecificFactRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return str_contains($text, 'visa')
            && (
                $this->looksLikeCostQuestion($message)
                || preg_match('/\b(bao lau|may ngay|thoi gian|xu ly|ket qua|phi|gia|cost|fee|price)\b/u', $text) === 1
            );
    }

    private function looksLikeHotelConsultationRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return preg_match('/\b(khach san|hotel|resort|luu tru|dat phong)\b/u', $text) === 1
            && preg_match('/\b(tu van|gan bien|gia dinh|nguoi lon|tre em|phong|budget|ngan sach)\b/u', $text) === 1
            && ! $this->looksLikeContactRequest($message);
    }

    private function looksLikeTourConsultationRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return preg_match('/\b(tu van|goi y|can|muon)\b/u', $text) === 1
            && preg_match('/\b(tour|du lich|lich trinh|hanh trinh)\b/u', $text) === 1
            && ! $this->looksLikeVisaConsultationRequest($message)
            && ! $this->looksLikeHotelConsultationRequest($message)
            && ! $this->looksLikeContactRequest($message);
    }

    private function looksLikeItinerarySuggestionRequest(string $message): bool
    {
        $text = $this->normalizeIntentText($message);

        return preg_match('/\b(de xuat|goi y|lich trinh|diem den|tu len|tu de xuat)\b/u', $text) === 1
            && preg_match('/\b(ngay|dem|xuat phat|tu tphcm|tu tp hcm|tu sai gon|from)\b/u', $text) === 1
            && ! $this->looksLikeVisaConsultationRequest($message)
            && ! $this->looksLikeHotelConsultationRequest($message)
            && ! $this->looksLikeContactRequest($message);
    }

    /**
     * @param list<array{role: string, text: string}> $history
     */
    private function inferTripDestination(string $message, array $history): string
    {
        $haystack = $message;

        foreach (array_reverse(array_slice($history, -6)) as $item) {
            $text = trim((string) ($item['text'] ?? ''));

            if ($text !== '') {
                $haystack .= "\n" . $text;
            }
        }

        $normalized = $this->normalizeIntentText($haystack);

        if (preg_match('/\b(da nang|danang)\b/u', $normalized) === 1) {
            return 'da nang';
        }

        if (preg_match('/\b(ha noi|hanoi)\b/u', $normalized) === 1) {
            return 'ha noi';
        }

        if (preg_match('/\b(nha trang|nhatrang)\b/u', $normalized) === 1) {
            return 'nha trang';
        }

        if (preg_match('/\b(da lat|dalat)\b/u', $normalized) === 1) {
            return 'da lat';
        }

        if (preg_match('/\b(phu quoc|phuquoc)\b/u', $normalized) === 1) {
            return 'phu quoc';
        }

        if (preg_match('/\b(sa pa|sapa)\b/u', $normalized) === 1) {
            return 'sa pa';
        }

        if (preg_match('/\b(phap|france)\b/u', $normalized) === 1) {
            return 'france';
        }

        if (preg_match('/\b(nhat ban|japan)\b/u', $normalized) === 1) {
            return 'japan';
        }

        if (preg_match('/\b(han quoc|korea|south korea)\b/u', $normalized) === 1) {
            return 'south korea';
        }

        if (preg_match('/\b(thai lan|thailand|bangkok)\b/u', $normalized) === 1) {
            return 'thailand';
        }

        if (preg_match('/\b(singapore)\b/u', $normalized) === 1) {
            return 'singapore';
        }

        if (preg_match('/\b(uc|australia)\b/u', $normalized) === 1) {
            return 'australia';
        }

        if (preg_match('/\b(new zealand)\b/u', $normalized) === 1) {
            return 'new zealand';
        }

        if (preg_match('/\b(my|usa|united states|hoa ky)\b/u', $normalized) === 1) {
            return 'usa';
        }

        return '';
    }

    private function buildDomesticShortTripSuggestion(string $locale, string $destination): ?string
    {
        $suggestions = [
            'da nang' => [
                'vi' => "Với lịch trình Đà Nẵng 3 ngày 2 đêm, xuất phát từ TP.HCM, Travel Plus có thể gợi ý nhẹ nhàng như sau:\n\nNgày 1: Bay TP.HCM - Đà Nẵng, nhận phòng, nghỉ biển Mỹ Khê, đi bán đảo Sơn Trà hoặc chùa Linh Ứng, tối ăn hải sản/khu biển.\nNgày 2: Đi Bà Nà Hills - Cầu Vàng cả ngày. Nếu muốn nhẹ hơn thì chọn Ngũ Hành Sơn kết hợp phố cổ Hội An buổi chiều tối.\nNgày 3: Đi chợ Hàn hoặc cafe ven sông Hàn, mua đặc sản rồi bay về TP.HCM.\n\nĐể Travel Plus tính phương án sát hơn, anh/chị cho em xin ngày đi, số khách, tiêu chuẩn khách sạn mong muốn và ngân sách dự kiến.",
                'en' => "For a 3-day, 2-night Da Nang trip from Ho Chi Minh City, Travel Plus can suggest this easy family-friendly route:\n\nDay 1: Fly from Ho Chi Minh City to Da Nang, check in, relax at My Khe Beach, visit Son Tra Peninsula or Linh Ung Pagoda, then have dinner near the beach.\nDay 2: Spend the day at Ba Na Hills and Golden Bridge, or choose Marble Mountains plus Hoi An Ancient Town if you prefer a lighter cultural day.\nDay 3: Visit Han Market or a cafe by the Han River, then return to Ho Chi Minh City.\n\nTo quote properly, please share your travel date, number of guests, preferred hotel level and approximate budget.",
            ],
            'ha noi' => [
                'vi' => "Với Hà Nội 3 ngày 2 đêm, Travel Plus có thể gợi ý lịch trình dễ đi như sau:\n\nNgày 1: Bay đến Hà Nội, nhận phòng, tham quan Hồ Hoàn Kiếm, phố cổ, Nhà Thờ Lớn hoặc cafe phố cổ, tối ăn đặc sản Hà Nội.\nNgày 2: Đi Lăng Bác, Văn Miếu, Hoàng thành Thăng Long; buổi chiều có thể ghé Hồ Tây hoặc làng gốm Bát Tràng nếu muốn nhẹ nhàng.\nNgày 3: Mua đặc sản, cafe sáng, tự do dạo phố rồi ra sân bay về.\n\nAnh/chị cho em xin ngày đi, số khách, tiêu chuẩn khách sạn và ngân sách dự kiến để Travel Plus tính phương án sát hơn.",
                'en' => "For a 3-day, 2-night Hanoi trip, Travel Plus can suggest:\n\nDay 1: Arrive in Hanoi, check in, visit Hoan Kiem Lake, the Old Quarter, St. Joseph's Cathedral or a local cafe, then try Hanoi specialties for dinner.\nDay 2: Visit Ho Chi Minh Mausoleum area, Temple of Literature and Imperial Citadel; in the afternoon, choose West Lake or Bat Trang pottery village.\nDay 3: Morning cafe, shopping for local specialties, then transfer to the airport.\n\nPlease share travel date, guest count, hotel level and budget so Travel Plus can quote properly.",
            ],
            'nha trang' => [
                'vi' => "Với Nha Trang 3 ngày 2 đêm, Travel Plus có thể gợi ý lịch trình như sau:\n\nNgày 1: Bay đến Nha Trang/Cam Ranh, nhận phòng, nghỉ biển Trần Phú hoặc khu Bãi Dài, tối ăn hải sản.\nNgày 2: Đi tour đảo/cano, tắm biển, lặn ngắm san hô nhẹ; nếu đi gia đình có thể chọn VinWonders hoặc tắm bùn khoáng.\nNgày 3: Ghé Tháp Bà Ponagar, chợ Đầm hoặc cafe biển rồi ra sân bay về.\n\nAnh/chị cho em xin ngày đi, số khách, khách sạn mong muốn và ngân sách để Travel Plus tư vấn phương án phù hợp.",
                'en' => "For a 3-day, 2-night Nha Trang trip, Travel Plus can suggest:\n\nDay 1: Fly to Cam Ranh/Nha Trang, check in, relax at Tran Phu Beach or Bai Dai, then seafood dinner.\nDay 2: Island hopping by canoe, beach time and light snorkeling; for families, consider VinWonders or a mud bath experience.\nDay 3: Visit Ponagar Cham Tower, Dam Market or a beach cafe, then return to the airport.\n\nPlease share travel date, guest count, hotel preference and budget so Travel Plus can advise the right option.",
            ],
            'da lat' => [
                'vi' => "Với Đà Lạt 3 ngày 2 đêm, Travel Plus có thể gợi ý:\n\nNgày 1: Đến Đà Lạt, nhận phòng, đi Quảng trường Lâm Viên, Hồ Xuân Hương, chợ đêm Đà Lạt.\nNgày 2: Tham quan Fresh Garden hoặc Puppy Farm, nhà thờ Domain, vườn hoa/cafe view đẹp; chiều có thể đi Langbiang hoặc săn mây nếu thích trải nghiệm sớm.\nNgày 3: Cafe sáng, mua đặc sản, ghé thêm một điểm gần trung tâm rồi về.\n\nAnh/chị cho em xin ngày đi, số khách, phương tiện dự kiến và ngân sách để Travel Plus tư vấn chi tiết hơn.",
                'en' => "For a 3-day, 2-night Da Lat trip, Travel Plus can suggest:\n\nDay 1: Arrive in Da Lat, check in, visit Lam Vien Square, Xuan Huong Lake and Da Lat night market.\nDay 2: Visit Fresh Garden or Puppy Farm, Domaine de Marie Church, flower gardens or scenic cafes; choose Langbiang or cloud hunting if you want an early experience.\nDay 3: Morning cafe, local specialties shopping, one more central stop, then return.\n\nPlease share travel date, guest count, transport preference and budget for a more accurate plan.",
            ],
            'phu quoc' => [
                'vi' => "Với Phú Quốc 3 ngày 2 đêm, Travel Plus có thể gợi ý:\n\nNgày 1: Bay đến Phú Quốc, nhận phòng, nghỉ biển, chiều đi Sunset Town hoặc ngắm hoàng hôn ở bãi biển.\nNgày 2: Chọn tour đảo Nam Phú Quốc/cáp treo Hòn Thơm hoặc đi VinWonders - Safari nếu có trẻ em.\nNgày 3: Ghé Dinh Cậu, chợ Dương Đông/mua đặc sản rồi ra sân bay về.\n\nAnh/chị cho em xin ngày đi, số khách, muốn nghỉ khu trung tâm hay resort biển và ngân sách dự kiến.",
                'en' => "For a 3-day, 2-night Phu Quoc trip, Travel Plus can suggest:\n\nDay 1: Fly to Phu Quoc, check in, beach time, then Sunset Town or sunset by the sea.\nDay 2: Choose South Island hopping and Hon Thom cable car, or VinWonders and Safari for families with children.\nDay 3: Visit Dinh Cau, Duong Dong Market or local specialty shops, then return to the airport.\n\nPlease share travel date, guest count, preferred area or beach resort style and budget.",
            ],
            'sa pa' => [
                'vi' => "Với Sa Pa 3 ngày 2 đêm, Travel Plus có thể gợi ý:\n\nNgày 1: Di chuyển Hà Nội - Sa Pa, nhận phòng, dạo trung tâm, nhà thờ đá, chợ đêm hoặc cafe view núi.\nNgày 2: Đi Fansipan hoặc bản Cát Cát; nếu thích nhẹ nhàng có thể chọn Ô Quy Hồ, Thác Bạc, Cầu Kính tùy thời tiết.\nNgày 3: Dạo bản/làng gần trung tâm, mua đặc sản rồi về Hà Nội.\n\nAnh/chị cho em xin điểm xuất phát, ngày đi, số khách và tiêu chuẩn khách sạn để Travel Plus tính lịch trình phù hợp.",
                'en' => "For a 3-day, 2-night Sa Pa trip, Travel Plus can suggest:\n\nDay 1: Travel from Hanoi to Sa Pa, check in, visit the town center, stone church, night market or a mountain-view cafe.\nDay 2: Choose Fansipan or Cat Cat Village; for a lighter route, consider O Quy Ho Pass, Silver Waterfall or Glass Bridge depending on weather.\nDay 3: Visit a nearby village, buy local specialties, then return to Hanoi.\n\nPlease share departure city, travel date, guest count and hotel standard so Travel Plus can plan properly.",
            ],
        ];

        if (! isset($suggestions[$destination])) {
            return null;
        }

        return $suggestions[$destination][$locale] ?? $suggestions[$destination]['vi'];
    }

    private function buildInternationalTripSuggestion(string $locale, string $destination): ?string
    {
        $labels = [
            'france' => ['vi' => 'Pháp', 'en' => 'France'],
            'japan' => ['vi' => 'Nhật Bản', 'en' => 'Japan'],
            'south korea' => ['vi' => 'Hàn Quốc', 'en' => 'South Korea'],
            'thailand' => ['vi' => 'Thái Lan', 'en' => 'Thailand'],
            'singapore' => ['vi' => 'Singapore', 'en' => 'Singapore'],
            'australia' => ['vi' => 'Úc', 'en' => 'Australia'],
            'new zealand' => ['vi' => 'New Zealand', 'en' => 'New Zealand'],
            'usa' => ['vi' => 'Mỹ', 'en' => 'United States'],
        ];

        if (! isset($labels[$destination])) {
            return null;
        }

        $label = $labels[$destination][$locale] ?? $labels[$destination]['vi'];
        $visaLine = in_array($destination, ['thailand', 'singapore'], true)
            ? ($locale === 'en'
                ? 'Visa requirements are usually lighter for Vietnamese leisure travelers, but the team should still check passport and entry conditions before departure.'
                : 'Yêu cầu visa thường nhẹ hơn với khách Việt đi du lịch, nhưng đội ngũ vẫn nên kiểm tra hộ chiếu và điều kiện nhập cảnh trước ngày đi.')
            : ($locale === 'en'
                ? 'Visa preparation should be checked early because requirements depend on passport, travel purpose and applicant profile.'
                : 'Phần visa nên được kiểm tra sớm vì yêu cầu phụ thuộc vào hộ chiếu, mục đích đi và hồ sơ từng khách.');

        $route = match ($destination) {
            'france' => $locale === 'en'
                ? "A suitable first-time route can focus on Paris, Versailles, the Seine area and nearby shopping or museum time."
                : "Lịch trình lần đầu có thể tập trung Paris, Versailles, khu sông Seine và thời gian mua sắm/bảo tàng gần trung tâm.",
            'japan' => $locale === 'en'
                ? "A practical route can combine Tokyo with either Mount Fuji, Osaka, Kyoto or nearby seasonal highlights."
                : "Lịch trình dễ đi có thể kết hợp Tokyo với núi Phú Sĩ, Osaka, Kyoto hoặc điểm theo mùa.",
            'south korea' => $locale === 'en'
                ? "A compact route can focus on Seoul, Nami Island, shopping streets and cultural stops such as Gyeongbokgung."
                : "Lịch trình gọn có thể tập trung Seoul, đảo Nami, các phố mua sắm và điểm văn hóa như Gyeongbokgung.",
            'thailand' => $locale === 'en'
                ? "A short route can focus on Bangkok, temples, shopping, food and one nearby experience such as Pattaya or Ayutthaya."
                : "Lịch trình ngắn có thể tập trung Bangkok, chùa, mua sắm, ẩm thực và một điểm gần như Pattaya hoặc Ayutthaya.",
            'singapore' => $locale === 'en'
                ? "A short route can cover Marina Bay, Gardens by the Bay, Sentosa, Jewel Changi and family-friendly attractions."
                : "Lịch trình ngắn có thể đi Marina Bay, Gardens by the Bay, Sentosa, Jewel Changi và các điểm phù hợp gia đình.",
            'australia' => $locale === 'en'
                ? "A first-time route usually works best by focusing on one city such as Sydney or Melbourne, with one nearby day trip."
                : "Lịch trình lần đầu thường nên tập trung một thành phố như Sydney hoặc Melbourne, kèm một chuyến đi trong ngày gần đó.",
            'new zealand' => $locale === 'en'
                ? "A suitable route depends heavily on season and trip length, often focusing on Auckland, Rotorua, Hobbiton or South Island scenery."
                : "Lịch trình phù hợp phụ thuộc nhiều vào mùa và số ngày, thường xoay quanh Auckland, Rotorua, Hobbiton hoặc cảnh quan đảo Nam.",
            'usa' => $locale === 'en'
                ? "The route should be planned by coast because distances are long. A first consultation should choose East Coast, West Coast or a combined route."
                : "Nên chọn tuyến theo bờ vì khoảng cách ở Mỹ rất xa. Bước đầu nên xác định đi bờ Đông, bờ Tây hay liên tuyến.",
            default => '',
        };

        return $locale === 'en'
            ? "Travel Plus can advise a {$label} itinerary, but international trips need travel date, trip length, number of guests, visa status and budget before quoting.\n\n{$route}\n\n{$visaLine}\n\nPlease share your expected travel date, number of guests, preferred trip length and whether you already have a visa."
            : "Travel Plus có thể tư vấn lịch trình {$label}, nhưng tour nước ngoài cần kiểm tra ngày đi, số ngày, số khách, tình trạng visa và ngân sách trước khi báo phương án.\n\n{$route}\n\n{$visaLine}\n\nAnh/chị cho em xin ngày đi dự kiến, số khách, số ngày muốn đi và hiện đã có visa chưa.";
    }

    private function extractVisaDestination(string $message): string
    {
        if (preg_match('/visa\s+(?:đi|di|du lịch|du lich|công tác|cong tac)?\s*([^\n,.;!?]+)/iu', $message, $matches) !== 1) {
            return '';
        }

        $destination = trim((string) ($matches[1] ?? ''));
        $destination = preg_replace('/\s{2,}/u', ' ', $destination) ?? $destination;

        return mb_substr($destination, 0, 40);
    }

    private function normalizeChatMessage(string $text): string
    {
        $text = preg_replace('/\[(.*?)\]\((https?:\/\/[^\)]+)\)/u', '$1', $text) ?? $text;
        $text = preg_replace('/\[\d+\]/u', '', $text) ?? $text;
        $text = preg_replace('/^\s*[\*\-]\s+/mu', '- ', $text) ?? $text;
        $text = str_replace('**', '', $text);
        $text = str_replace('__', '', $text);
        $text = trim($text, "\"' \t\n\r\0\x0B");
        $text = preg_replace('/[ \t]+\n/u', "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * @param array<string, mixed> $facts
     */
    private function looksIncompleteStructuredResponse(string $text, array $facts): bool
    {
        $text = trim($text);

        if ($text === '') {
            return true;
        }

        $selectedTour = is_array($facts['selected_tour'] ?? null) ? $facts['selected_tour'] : [];
        $title = trim((string) ($selectedTour['title'] ?? ''));

        if ($title !== '' && mb_stripos($text, $title) !== false && mb_strlen($text) < mb_strlen($title) + 20) {
            return true;
        }

        if (mb_substr_count($text, '(') > mb_substr_count($text, ')')) {
            return true;
        }

        if (mb_substr_count($text, '"') % 2 !== 0) {
            return true;
        }

        if (preg_match('/\(\d+\s*$/u', $text) === 1) {
            return true;
        }

        $lastChar = mb_substr($text, -1);
        if (! in_array($lastChar, ['.', '!', '?', '…'], true) && mb_strlen($text) < 260) {
            return true;
        }

        if (preg_match('/[A-ZÀ-Ỹ][^ \n]{0,8}$/u', $text) === 1) {
            return true;
        }

        return false;
    }

    private function looksIncompleteGenericResponse(string $text): bool
    {
        $text = trim($text);

        if ($text === '') {
            return true;
        }

        if (mb_strlen($text) < 40) {
            return true;
        }

        if (mb_substr_count($text, '(') > mb_substr_count($text, ')')) {
            return true;
        }

        if (mb_substr_count($text, '"') % 2 !== 0) {
            return true;
        }

        $lastChar = mb_substr($text, -1);
        if (! in_array($lastChar, ['.', '!', '?', '…'], true)) {
            return true;
        }

        if (preg_match('/(?:[,;:]|\b(?:và|hoặc|từ|là|của|với|and|or|from|to))\s*$/iu', $text) === 1) {
            return true;
        }

        return false;
    }

    private function buildContextFallbackMessage(string $locale, string $summary): string
    {
        $chunks = preg_split("/\n\s*\n/u", trim($summary)) ?: [];
        $firstChunk = trim((string) ($chunks[0] ?? ''));

        if ($firstChunk === '') {
            return $locale === 'en'
                ? 'The website currently has related information, but the AI summary was incomplete. Please open the related source below.'
                : 'Website hiện có thông tin liên quan, nhưng phần tóm tắt AI chưa hoàn chỉnh. Bạn vui lòng xem nguồn bên dưới.';
        }

        $lines = preg_split("/\n/u", $firstChunk) ?: [];
        array_shift($lines);
        $text = trim(implode(' ', array_map('trim', $lines)));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        if ($text === '') {
            return $locale === 'en'
                ? 'The website currently has related information. Please open the related source below.'
                : 'Website hiện có thông tin liên quan. Bạn vui lòng mở nguồn bên dưới để xem chi tiết.';
        }

        return $this->completeSentences($text, 2, 260);
    }

    private function completeSentences(string $text, int $maxSentences, int $maxLength): string
    {
        $sentences = preg_split('/(?<=[\.\!\?…])\s+/u', trim($text)) ?: [];
        $selected = [];
        $length = 0;

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            if ($sentence === '') {
                continue;
            }

            $nextLength = $length + ($selected === [] ? 0 : 1) + mb_strlen($sentence);
            if (count($selected) >= $maxSentences || $nextLength > $maxLength) {
                break;
            }

            $selected[] = $sentence;
            $length = $nextLength;
        }

        if ($selected === []) {
            return rtrim(mb_substr($text, 0, max(0, $maxLength - 1))) . '…';
        }

        return implode(' ', $selected);
    }

    private function isQuotaError(RuntimeException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'HTTP 429') || str_contains($message, 'RESOURCE_EXHAUSTED') || str_contains($message, 'quota');
    }

    private function isTruncationError(RuntimeException $exception): bool
    {
        return str_contains($exception->getMessage(), 'MAX_TOKENS');
    }

    private function isRecoverableProviderError(RuntimeException $exception): bool
    {
        $message = $exception->getMessage();

        return $this->isQuotaError($exception)
            || $this->isTruncationError($exception)
            || str_contains($message, 'HTTP 503')
            || str_contains($message, 'UNAVAILABLE')
            || str_contains($message, 'temporarily');
    }

    /**
     * @param array<string, mixed> $facts
     */
    private function buildFactsFallbackMessage(string $locale, array $facts): string
    {
        $type = (string) ($facts['type'] ?? '');

        if ($type === 'tour_list') {
            $lines = [
                $locale === 'en'
                    ? 'The website currently has these matching tours:'
                    : 'Hiện website có các tour phù hợp sau:',
                '',
            ];

            foreach ((array) ($facts['tours'] ?? []) as $tour) {
                if (! is_array($tour)) {
                    continue;
                }

                $parts = ['- ' . (string) ($tour['title'] ?? '')];

                if (! empty($tour['departure'])) {
                    $parts[] = ($locale === 'en' ? 'Departure' : 'Khởi hành') . ': ' . $tour['departure'];
                }

                if (! empty($tour['price_label'])) {
                    $parts[] = ($locale === 'en' ? 'Price from' : 'Giá từ') . ': ' . $tour['price_label'];
                }

                if (! empty($tour['duration_label'])) {
                    $parts[] = ($locale === 'en' ? 'Duration' : 'Thời lượng') . ': ' . $tour['duration_label'];
                }

                $lines[] = implode(' | ', $parts);
            }

            return trim(implode("\n", $lines));
        }

        if ($type === 'visa_support') {
            $visa = is_array($facts['visa'] ?? null) ? $facts['visa'] : [];
            $matchedDestination = trim((string) ($visa['matched_destination'] ?? ''));
            $lines = [];
            $intent = (string) ($facts['intent'] ?? 'visa_process');

            if ($intent === 'visa_timeline') {
                $lines[] = $locale === 'en'
                    ? 'The current website content does not state an exact processing time for this visa destination.'
                    : 'Nội dung hiện tại trên website chưa nêu thời gian xử lý cụ thể cho visa này.';

                if ($matchedDestination !== '') {
                    $lines[] = $locale === 'en'
                        ? 'Travel Plus does provide support content relevant to ' . $matchedDestination . '.'
                        : 'Travel Plus vẫn có nội dung hỗ trợ phù hợp với điểm đến ' . $matchedDestination . '.';
                }

                $lines[] = $locale === 'en'
                    ? 'The website recommends preparing early so there is enough room for additional document requests or schedule changes during processing.'
                    : 'Website có khuyến nghị nên chuẩn bị hồ sơ sớm để có dư thời gian xử lý nếu phát sinh yêu cầu bổ sung giấy tờ hoặc thay đổi lịch.';

                $steps = array_slice(array_values(array_filter((array) ($visa['steps'] ?? []), 'is_array')), 0, 2);
                if ($steps !== []) {
                    $lines[] = '';
                    $lines[] = $locale === 'en' ? 'Travel Plus support usually starts with:' : 'Travel Plus thường hỗ trợ theo các bước đầu như:';
                    foreach ($steps as $step) {
                        $title = trim((string) ($step['title'] ?? ''));
                        $text = trim((string) ($step['text'] ?? ''));
                        $line = '- ' . $title;
                        if ($text !== '') {
                            $line .= ': ' . $text;
                        }
                        $lines[] = $line;
                    }
                }

                return trim(implode("\n", $lines));
            }

            if ($intent === 'visa_cost') {
                return $locale === 'en'
                    ? "Travel Plus has not published a fixed visa service fee on the website. The final cost depends on the destination, visa type, consular fees, service scope and the applicant profile.\n\nPlease share the destination, visa purpose, number of applicants and expected travel date so Travel Plus can advise a more accurate estimate."
                    : "Travel Plus chưa công bố mức phí dịch vụ visa cố định trên website. Chi phí cuối cùng phụ thuộc vào điểm đến, loại visa, phí lãnh sự, phạm vi hỗ trợ và hồ sơ của từng khách.\n\nAnh/chị cho em xin điểm đến, mục đích xin visa, số người làm visa và ngày đi dự kiến để Travel Plus tư vấn mức phí sát hơn.";
            }

            $lines[] = $locale === 'en'
                ? 'Travel Plus currently supports visa consultation and document preparation.'
                : 'Travel Plus hiện có hỗ trợ tư vấn và chuẩn bị hồ sơ visa.';

            if ($matchedDestination !== '') {
                $lines[] = $locale === 'en'
                    ? 'The website currently includes support content relevant to ' . $matchedDestination . '.'
                    : 'Website hiện có nội dung hỗ trợ phù hợp với điểm đến ' . $matchedDestination . '.';
            }

            if (! empty($visa['description'])) {
                $lines[] = (string) $visa['description'];
            }

            $steps = array_slice(array_values(array_filter((array) ($visa['steps'] ?? []), 'is_array')), 0, 3);
            if ($steps !== []) {
                $lines[] = '';
                $lines[] = $locale === 'en' ? 'Typical support process:' : 'Quy trình hỗ trợ thường gồm:';
                foreach ($steps as $step) {
                    $title = trim((string) ($step['title'] ?? ''));
                    $text = trim((string) ($step['text'] ?? ''));
                    $line = '- ' . $title;
                    if ($text !== '') {
                        $line .= ': ' . $text;
                    }
                    $lines[] = $line;
                }
            }

            return trim(implode("\n", $lines));
        }

        if ($type === 'company_strength') {
            $companyStrength = is_array($facts['company_strength'] ?? null) ? $facts['company_strength'] : [];
            $summary = trim((string) ($companyStrength['summary'] ?? ''));
            $strengths = array_slice(array_values(array_filter((array) ($companyStrength['strengths'] ?? []), 'is_array')), 0, 4);
            $lines = [$summary !== '' ? $summary : ($locale === 'en'
                ? 'Travel Plus has strengths in corporate MICE, tours, visa support and coordinated travel services.'
                : 'Travel Plus có thế mạnh về MICE doanh nghiệp, tour, visa và dịch vụ du lịch phối hợp trọn gói.')];

            if ($strengths !== []) {
                $lines[] = '';
                $lines[] = $locale === 'en' ? 'Key strengths:' : 'Các điểm mạnh chính:';

                foreach ($strengths as $item) {
                    $title = trim((string) ($item['title'] ?? ''));
                    $text = trim((string) ($item['text'] ?? ''));

                    if ($title === '' && $text === '') {
                        continue;
                    }

                    $line = '- ' . $title;
                    if ($text !== '') {
                        $line .= ': ' . $text;
                    }

                    $lines[] = $line;
                }
            }

            return trim(implode("\n", $lines));
        }

        if ($type === 'mice_service') {
            $mice = is_array($facts['mice'] ?? null) ? $facts['mice'] : [];
            $lines = [];
            $description = trim((string) ($mice['description'] ?? ''));
            $servicesDesc = trim((string) ($mice['services_desc'] ?? ''));

            if ($description !== '') {
                $lines[] = $description;
            } else {
                $lines[] = $locale === 'en'
                    ? 'Travel Plus provides end-to-end MICE services for business programs.'
                    : 'Travel Plus cung cấp dịch vụ MICE trọn gói cho các chương trình doanh nghiệp.';
            }

            if ($servicesDesc !== '') {
                $lines[] = $servicesDesc;
            }

            $cards = array_slice(array_values(array_filter((array) ($mice['service_cards'] ?? []), 'is_array')), 0, 4);

            if ($cards !== []) {
                $lines[] = '';
                $lines[] = $locale === 'en' ? 'Main MICE services:' : 'Các mảng MICE chính:';

                foreach ($cards as $card) {
                    $title = trim((string) ($card['title'] ?? ''));
                    $text = trim((string) ($card['text'] ?? ''));

                    if ($title === '' && $text === '') {
                        continue;
                    }

                    $line = '- ' . $title;
                    if ($text !== '') {
                        $line .= ': ' . $text;
                    }

                    $lines[] = $line;
                }
            }

            return trim(implode("\n", $lines));
        }

        if (in_array($type, ['payment_support', 'custom_tour_support', 'hotel_service', 'transport_service'], true)) {
            if ($type === 'payment_support') {
                $payment = is_array($facts['payment'] ?? null) ? $facts['payment'] : [];
                $summary = trim((string) ($payment['summary'] ?? ''));
                $methods = array_values(array_filter((array) ($payment['methods'] ?? []), 'is_string'));
                $lines = [$summary !== '' ? $summary : ($locale === 'en' ? 'The website supports several payment methods.' : 'Website hiện có nhiều phương thức thanh toán.')];

                if ($methods !== []) {
                    $lines[] = '';
                    $lines[] = ($locale === 'en' ? 'Available methods:' : 'Các phương thức hiện có:') . ' ' . implode(', ', $methods) . '.';
                }

                return trim(implode("\n", $lines));
            }

            if ($type === 'custom_tour_support') {
                $customTour = is_array($facts['custom_tour'] ?? null) ? $facts['custom_tour'] : [];
                $summaryText = trim(((string) ($customTour['summary'] ?? ($locale === 'en'
                    ? 'Travel Plus supports custom tour requests.'
                    : 'Travel Plus có hỗ trợ tạo tour theo yêu cầu.'))));

                return $summaryText . "\n\n" . ($locale === 'en'
                    ? 'Please share destination, travel date, number of guests, trip length and approximate budget so Travel Plus can advise the right itinerary.'
                    : 'Anh/chị cho em xin điểm đến, ngày đi, số lượng khách, số ngày và ngân sách dự kiến để Travel Plus tư vấn lịch trình phù hợp.');
            }

            $service = is_array($facts['service'] ?? null) ? $facts['service'] : [];
            $summary = [];

            if (! empty($service['description'])) {
                $summary[] = (string) $service['description'];
            }

            if (! empty($service['intro'])) {
                $summary[] = (string) $service['intro'];
            }

            $highlights = array_slice(array_values(array_filter((array) ($service['highlights'] ?? []), 'is_array')), 0, 3);
            if ($highlights !== []) {
                $summary[] = '';
                $summary[] = $locale === 'en' ? 'Key support areas:' : 'Các hạng mục hỗ trợ chính:';
                foreach ($highlights as $item) {
                    $line = '- ' . trim((string) ($item['title'] ?? ''));
                    if (! empty($item['text'])) {
                        $line .= ': ' . trim((string) $item['text']);
                    }
                    $summary[] = $line;
                }
            }

            if ($type === 'hotel_service') {
                $summary[] = '';
                $summary[] = $locale === 'en'
                    ? 'To suggest the right hotel, please share check-in/check-out dates, number of guests, preferred area, room setup and budget per night.'
                    : 'Để gợi ý đúng khách sạn, anh/chị cho em xin ngày nhận/trả phòng, số lượng khách, khu vực muốn ở, cấu hình phòng và ngân sách mỗi đêm.';
            }

            return trim(implode("\n", $summary));
        }

        $tour = is_array($facts['tour'] ?? null) ? $facts['tour'] : [];
        $selectedTour = is_array($facts['selected_tour'] ?? null) ? $facts['selected_tour'] : [];
        $intent = (string) ($facts['intent'] ?? 'itinerary');
        $title = (string) ($tour['title'] ?? $selectedTour['title'] ?? '');

        if ($intent === 'price') {
            $price = trim((string) ($tour['price_label'] ?? $selectedTour['price_label'] ?? ''));
            $departure = trim((string) ($tour['departure'] ?? $selectedTour['departure'] ?? ''));
            $duration = trim((string) ($tour['duration_label'] ?? $selectedTour['duration_label'] ?? ''));
            $lines = [
                $price !== ''
                    ? ($locale === 'en' ? 'Price from for ' . $title . ': ' . $price . '.' : 'Giá từ của tour ' . $title . ' là ' . $price . '.')
                    : ($locale === 'en' ? 'The website has not published a price for ' . $title . '.' : 'Website chưa công bố giá cho tour ' . $title . '.'),
            ];
            $factsLine = array_values(array_filter([
                $departure !== '' ? (($locale === 'en' ? 'Departure' : 'Khởi hành') . ': ' . $departure) : '',
                $duration !== '' ? (($locale === 'en' ? 'Duration' : 'Thời lượng') . ': ' . $duration) : '',
            ]));

            if ($factsLine !== []) {
                $lines[] = implode(' | ', $factsLine);
            }

            return trim(implode("\n", $lines));
        }

        if ($intent === 'departure') {
            $departure = trim((string) ($tour['departure'] ?? $selectedTour['departure'] ?? ''));
            $price = trim((string) ($tour['price_label'] ?? $selectedTour['price_label'] ?? ''));
            $duration = trim((string) ($tour['duration_label'] ?? $selectedTour['duration_label'] ?? ''));
            $lines = [
                $departure !== ''
                    ? ($locale === 'en' ? $title . ' departs on ' . $departure . '.' : 'Tour ' . $title . ' khởi hành ngày ' . $departure . '.')
                    : ($locale === 'en' ? 'The website has not published a departure date for ' . $title . '.' : 'Website chưa công bố ngày khởi hành cho tour ' . $title . '.'),
            ];
            $factsLine = array_values(array_filter([
                $price !== '' ? (($locale === 'en' ? 'Price from' : 'Giá từ') . ': ' . $price) : '',
                $duration !== '' ? (($locale === 'en' ? 'Duration' : 'Thời lượng') . ': ' . $duration) : '',
            ]));

            if ($factsLine !== []) {
                $lines[] = implode(' | ', $factsLine);
            }

            return trim(implode("\n", $lines));
        }

        if ($intent === 'destinations') {
            $routeStops = array_values(array_filter((array) ($tour['route_stops'] ?? []), 'is_string'));
            $attractions = array_values(array_filter((array) ($tour['attraction_highlights'] ?? []), 'is_string'));

            $lines = [
                ($locale === 'en'
                    ? 'The main destinations in ' . $title . ' are:'
                    : 'Tour ' . $title . ' đi qua các điểm đến chính sau:'),
            ];

            if ($routeStops !== []) {
                $lines[] = '- ' . implode(', ', array_slice($routeStops, 0, 10));
            }

            if ($attractions !== []) {
                $lines[] = '';
                $lines[] = $locale === 'en'
                    ? 'Notable attractions:'
                    : 'Điểm tham quan nổi bật:';
                $lines[] = '- ' . implode(', ', array_slice($attractions, 0, 8));
            }

            return trim(implode("\n", $lines));
        }

        $lines = [
            ($intent === 'highlights'
                ? ($locale === 'en' ? 'What stands out in this tour: ' : 'Điểm đặc biệt của tour: ')
                : ($locale === 'en' ? 'The most relevant tour is: ' : 'Tour phù hợp nhất là: ')) . $title,
        ];

        if (! empty($tour['overview'])) {
            $lines[] = '';
            $lines[] = (string) $tour['overview'];
        }

        $routeStops = array_values(array_filter((array) ($tour['route_stops'] ?? []), 'is_string'));
        if ($intent === 'highlights' && $routeStops !== []) {
            $lines[] = '';
            $lines[] = $locale === 'en' ? 'Notable route:' : 'Hành trình nổi bật:';
            $lines[] = '- ' . implode(' - ', array_slice($routeStops, 0, 8));
        }

        $highlights = array_values(array_filter((array) ($tour['attraction_highlights'] ?? []), 'is_string'));
        if ($highlights !== []) {
            $lines[] = '';
            $lines[] = $locale === 'en' ? 'Highlights:' : 'Điểm nổi bật:';
            $lines[] = '- ' . implode(', ', array_slice($highlights, 0, 6));
        }

        $factsLine = [];
        if (! empty($tour['departure'])) {
            $factsLine[] = ($locale === 'en' ? 'Departure' : 'Khởi hành') . ': ' . $tour['departure'];
        }
        if (! empty($tour['price_label'])) {
            $factsLine[] = ($locale === 'en' ? 'Price from' : 'Giá từ') . ': ' . $tour['price_label'];
        }
        if (! empty($tour['duration_label'])) {
            $factsLine[] = ($locale === 'en' ? 'Duration' : 'Thời lượng') . ': ' . $tour['duration_label'];
        }

        if ($factsLine !== []) {
            $lines[] = '';
            $lines[] = implode(' | ', $factsLine);
        }

        return trim(implode("\n", $lines));
    }

    /**
     * @param array<string, mixed> $facts
     */
    private function buildReferenceFallbackMessage(string $locale, array $facts): string
    {
        $type = (string) ($facts['type'] ?? '');

        if ($type === 'reference_visa_timeline') {
            $websiteFacts = is_array($facts['website_facts'] ?? null) ? $facts['website_facts'] : [];
            $visa = is_array($websiteFacts['visa'] ?? null) ? $websiteFacts['visa'] : [];
            $matchedDestination = trim((string) ($visa['matched_destination'] ?? ''));
            $referenceRegion = (string) ($facts['reference_region'] ?? 'general');
            $topic = trim((string) ($facts['reference_topic'] ?? ''));

            if ($referenceRegion === 'schengen') {
                $lines = [
                    $locale === 'en'
                        ? 'Reference information outside website data: For Schengen visa destinations such as France, Italy and Switzerland, the usual decision time is 15 calendar days after the application is lodged. This can be extended up to 45 calendar days if the consulate needs additional examination or documents.'
                        : 'Thông tin tham khảo ngoài dữ liệu website: Với visa Schengen như Pháp, Ý và Thụy Sĩ, thời gian xét duyệt thông thường là 15 ngày lịch kể từ khi nộp hồ sơ. Thời gian này có thể kéo dài tối đa 45 ngày lịch nếu lãnh sự cần kiểm tra thêm hoặc yêu cầu bổ sung giấy tờ.',
                ];
            } else {
                $destinationText = $matchedDestination !== ''
                    ? $matchedDestination
                    : ($locale === 'en' ? 'this visa destination' : 'điểm đến visa này');

                $lines = [
                    $locale === 'en'
                        ? 'Reference information outside website data: Processing time for ' . $destinationText . ' can vary by destination, appointment availability, season, document completeness and whether additional review is required.'
                        : 'Thông tin tham khảo ngoài dữ liệu website: Thời gian xử lý visa cho ' . $destinationText . ' có thể thay đổi theo điểm đến, lịch hẹn còn trống, mùa nộp hồ sơ, độ đầy đủ của giấy tờ và việc có bị yêu cầu kiểm tra bổ sung hay không.',
                ];
            }

            if ($this->looksLikeCostQuestion($topic)) {
                $lines[] = $locale === 'en'
                    ? 'For cost, Travel Plus has not published a fixed service fee on the website; the final amount depends on visa type, consular fees, service scope and the applicant profile.'
                    : 'Về chi phí, Travel Plus chưa công bố mức phí cố định trên website; số tiền cuối cùng phụ thuộc vào loại visa, phí lãnh sự, phạm vi dịch vụ và hồ sơ của từng khách.';
            }

            $lines[] = '';
            $lines[] = $locale === 'en'
                ? 'Website data: Travel Plus currently supports visa consultation and document preparation, but the website has not published a fixed processing time for this exact request.'
                : 'Dữ liệu website: Travel Plus hiện hỗ trợ tư vấn và chuẩn bị hồ sơ visa, nhưng website chưa công bố thời gian xử lý cố định cho yêu cầu này.';

            return trim(implode("\n", $lines));
        }

        return $locale === 'en'
            ? 'Reference information outside website data: This topic is better treated as general travel guidance, but the AI reference answer is temporarily unavailable.'
            : 'Thông tin tham khảo ngoài dữ liệu website: Chủ đề này phù hợp với kiến thức tham khảo chung, nhưng phần trả lời tham khảo của AI đang tạm thời chưa sẵn sàng.';
    }

    private function looksLikeCostQuestion(string $text): bool
    {
        $search = mb_strtolower($text);
        $search = strtr($search, [
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
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $search);
        $search = is_string($ascii) && $ascii !== '' ? $ascii : $search;
        $search = preg_replace('/[^a-z0-9\s]+/i', ' ', $search) ?? '';

        foreach ([
            'chi phi',
            'phi',
            'gia',
            'bao nhieu tien',
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

    private function requestGemini(string $prompt): string
    {
        if (! function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is not enabled.');
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($this->model) . ':generateContent';
        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [[
                    'text' => $prompt,
                ]],
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'topP' => 0.9,
                'maxOutputTokens' => self::GEMINI_MAX_OUTPUT_TOKENS,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => self::GEMINI_TIMEOUT_SECONDS,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorNo = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException('AI provider request failed. cURL #' . $errorNo . ': ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || ! is_array($data)) {
            $detail = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $response;
            throw new RuntimeException('AI provider returned an invalid response. HTTP ' . $httpCode . ': ' . $detail);
        }

        $candidate = is_array($data['candidates'][0] ?? null) ? $data['candidates'][0] : [];
        $parts = is_array($candidate['content']['parts'] ?? null) ? $candidate['content']['parts'] : [];
        $textParts = [];

        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $textParts[] = $part['text'];
            }
        }

        $text = trim(implode("\n", $textParts));
        $finishReason = (string) ($candidate['finishReason'] ?? '');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('AI provider returned an empty response.');
        }

        if ($finishReason === 'MAX_TOKENS') {
            throw new RuntimeException('AI provider response stopped early with finishReason=MAX_TOKENS.');
        }

        return trim($text);
    }
}
