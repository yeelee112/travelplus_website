<?php

namespace App\Services;

use RuntimeException;

class GeminiWebsiteChatService
{
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
            throw new RuntimeException('Gemini API key is missing.');
        }

        $referenceFacts = $this->knowledgeService->getReferenceFacts($locale, $message, $chatState);

        if ($referenceFacts !== null) {
            $prompt = $this->buildReferencePrompt($locale, $message, $history, $referenceFacts);
            try {
                $response = $this->normalizeChatMessage($this->requestGemini($prompt));
            } catch (RuntimeException $exception) {
                if (! $this->isQuotaError($exception)) {
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
            $prompt = $this->buildStructuredFactsPrompt($locale, $message, $history, $structuredFacts);
            $usedFactsFallback = false;
            $usedQuotaFallback = false;
            try {
                $response = $this->normalizeChatMessage($this->requestGemini($prompt));
            } catch (RuntimeException $exception) {
                if (! $this->isQuotaError($exception)) {
                    throw $exception;
                }

                $response = $this->buildFactsFallbackMessage($locale, $structuredFacts);
                $usedFactsFallback = true;
                $usedQuotaFallback = true;
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
            if (! $this->isQuotaError($exception)) {
                throw $exception;
            }

            $response = $locale === 'en'
                ? 'The AI assistant is temporarily over its Gemini quota. Please try again later.'
                : 'AI Travel Plus đang tạm thời vượt hạn mức Gemini. Vui lòng thử lại sau.';
            $usedQuotaFallback = true;
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
            'If the user asks about itinerary highlights or destinations, summarize the meaningful places and experiences from the facts instead of repeating operational lines like airport procedures, breakfast, or hotel checkout.',
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
            'Start the answer with exactly this label in the correct language:',
            $locale === 'en'
                ? 'Reference information outside website data:'
                : 'Thông tin tham khảo ngoài dữ liệu website:',
            'If website facts are provided, keep them separate from general reference knowledge and do not present them as the same thing.',
            'Do not invent specific Travel Plus policies, prices, processing commitments, or promises that are not explicitly in website facts.',
            'Write in plain text. Do not use markdown bold, markdown bullet markers, raw URLs, or citation markers like [1].',
            'Conversation history:',
            $historyLines !== [] ? implode("\n", $historyLines) : '(none)',
            'Reference facts and any relevant website facts:',
            $factsJson ?: '{}',
            'Latest user question:',
            trim($message),
        ]));
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
        if (! in_array($lastChar, ['.', '!', '?', '…'], true) && mb_strlen($text) < 220) {
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
                    ? 'The current website content does not state an exact processing time for this visa destination [1].'
                    : 'Nội dung hiện tại trên website chưa nêu thời gian xử lý cụ thể cho visa này [1].';

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

            $lines[] = $locale === 'en'
                ? 'Travel Plus currently supports visa consultation and document preparation [1].'
                : 'Travel Plus hiện có hỗ trợ tư vấn và chuẩn bị hồ sơ visa [1].';

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

        if (in_array($type, ['payment_support', 'custom_tour_support', 'hotel_service', 'transport_service'], true)) {
            if ($type === 'payment_support') {
                $payment = is_array($facts['payment'] ?? null) ? $facts['payment'] : [];
                $summary = trim((string) ($payment['summary'] ?? ''));
                $methods = array_values(array_filter((array) ($payment['methods'] ?? []), 'is_string'));
                $lines = [$summary !== '' ? $summary . ' [1]' : (($locale === 'en' ? 'The website supports several payment methods.' : 'Website hiện có nhiều phương thức thanh toán.') . ' [1]')];

                if ($methods !== []) {
                    $lines[] = '';
                    $lines[] = ($locale === 'en' ? 'Available methods:' : 'Các phương thức hiện có:') . ' ' . implode(', ', $methods) . '.';
                }

                return trim(implode("\n", $lines));
            }

            if ($type === 'custom_tour_support') {
                $customTour = is_array($facts['custom_tour'] ?? null) ? $facts['custom_tour'] : [];
                return trim(((string) ($customTour['summary'] ?? ($locale === 'en'
                    ? 'Travel Plus supports custom tour requests.'
                    : 'Travel Plus có hỗ trợ tạo tour theo yêu cầu.'))) . ' [1]');
            }

            $service = is_array($facts['service'] ?? null) ? $facts['service'] : [];
            $summary = [];

            if (! empty($service['description'])) {
                $summary[] = (string) $service['description'] . ' [1]';
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

            return trim(implode("\n", $summary));
        }

        $tour = is_array($facts['tour'] ?? null) ? $facts['tour'] : [];
        $selectedTour = is_array($facts['selected_tour'] ?? null) ? $facts['selected_tour'] : [];
        $intent = (string) ($facts['intent'] ?? 'itinerary');
        $title = (string) ($tour['title'] ?? $selectedTour['title'] ?? '');

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
            ($locale === 'en'
                ? 'The most relevant tour is: '
                : 'Tour phù hợp nhất là: ') . $title,
        ];

        if (! empty($tour['overview'])) {
            $lines[] = '';
            $lines[] = (string) $tour['overview'];
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

            $lines = [
                $locale === 'en'
                    ? 'Reference information outside website data: Visa processing time often depends on the destination, season, document completeness, and whether additional documents are requested.'
                    : 'Thông tin tham khảo ngoài dữ liệu website: Thời gian xử lý visa thường phụ thuộc vào điểm đến, mùa nộp hồ sơ, độ đầy đủ của giấy tờ và việc có bị yêu cầu bổ sung hay không.',
            ];

            if ($matchedDestination !== '') {
                $lines[] = $locale === 'en'
                    ? 'For ' . $matchedDestination . ', processing time is usually something travelers should confirm close to the submission date because it can change.'
                    : 'Với ' . $matchedDestination . ', thời gian xử lý thường nên được xác nhận sát ngày nộp vì có thể thay đổi theo từng thời điểm.';
            }

            $lines[] = '';
            $lines[] = $locale === 'en'
                ? 'Website data: Travel Plus currently supports visa consultation and document preparation for this type of request [1].'
                : 'Dữ liệu website: Travel Plus hiện có hỗ trợ tư vấn và chuẩn bị hồ sơ visa cho nhu cầu này [1].';

            return trim(implode("\n", $lines));
        }

        return $locale === 'en'
            ? 'Reference information outside website data: This topic is better treated as general travel guidance, but the AI reference answer is temporarily unavailable.'
            : 'Thông tin tham khảo ngoài dữ liệu website: Chủ đề này phù hợp với kiến thức tham khảo chung, nhưng phần trả lời tham khảo của AI đang tạm thời chưa sẵn sàng.';
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
                'maxOutputTokens' => 600,
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
            CURLOPT_TIMEOUT => 45,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorNo = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException('Gemini request failed. cURL #' . $errorNo . ': ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300 || ! is_array($data)) {
            $detail = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : (string) $response;
            throw new RuntimeException('Gemini returned an invalid response. HTTP ' . $httpCode . ': ' . $detail);
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        return trim($text);
    }
}
