<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CrmLeadCaptureService;
use App\Services\GeminiWebsiteChatService;
use RuntimeException;
use Throwable;

class ChatController extends BaseController
{
    private const MAX_MESSAGE_LENGTH = 1000;
    private const RATE_LIMIT_WINDOW_SECONDS = 60;
    private const RATE_LIMIT_MAX_MESSAGES = 12;
    private const RATE_LIMIT_MAX_MESSAGES_PER_IP = 30;
    private const LOG_RETENTION_DAYS = 14;
    private const LOG_MAX_FILE_BYTES = 5242880;
    private const LOG_MAX_MESSAGE_LENGTH = 2000;

    private bool $chatDebugEnabled;

    public function __construct()
    {
        $this->chatDebugEnabled = filter_var(env('gemini.debug', false), FILTER_VALIDATE_BOOL);
    }

    public function message()
    {
        $payload = $this->request->getJSON(true);

        if (! is_array($payload)) {
            return $this->response->setStatusCode(400)->setJSON([
                'message' => 'Invalid request payload.',
            ]);
        }

        $locale = in_array(($payload['locale'] ?? 'vi'), ['vi', 'en'], true) ? (string) $payload['locale'] : 'vi';
        $message = mb_substr(trim((string) ($payload['message'] ?? '')), 0, self::MAX_MESSAGE_LENGTH);
        $history = is_array($payload['history'] ?? null) ? $payload['history'] : [];

        if ($message === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => $locale === 'en' ? 'Please enter a message.' : 'Vui lòng nhập nội dung.',
            ]);
        }

        if (! $this->passesRateLimit()) {
            return $this->response->setStatusCode(429)->setJSON([
                'message' => $locale === 'en'
                    ? 'You are sending messages too quickly. Please try again in a moment.'
                    : 'Bạn đang gửi tin nhắn quá nhanh. Vui lòng thử lại sau ít phút.',
            ]);
        }

        $this->logChatEntry('user', $locale, $message, $payload);
        $this->captureLeadFromChat($locale, $message, $payload);

        $normalizedHistory = [];

        foreach (array_slice($history, -8) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $role = (string) ($item['role'] ?? 'user');
            $text = trim((string) ($item['text'] ?? ''));

            if ($text === '' || ! in_array($role, ['user', 'assistant'], true)) {
                continue;
            }

            $normalizedHistory[] = [
                'role' => $role,
                'text' => $text,
            ];
        }

        try {
            $service = new GeminiWebsiteChatService();
            $session = session();
            $chatState = $session->get('ai_chat_state');

            if (! is_array($chatState)) {
                $chatState = [];
            }

            if (! $service->isConfigured()) {
                throw new RuntimeException($locale === 'en'
                    ? 'The AI assistant is temporarily unavailable. Please try again later.'
                    : 'AI Travel Plus đang tạm thời không khả dụng. Vui lòng thử lại sau.');
            }

            $result = $service->answer($locale, $message, $normalizedHistory, $chatState);

            if (is_array($result['chat_state'] ?? null) && $result['chat_state'] !== []) {
                $session->set('ai_chat_state', $result['chat_state']);
            }

            $assistantMessage = $this->appendLeadCaptureCta(
                $locale,
                $message,
                $normalizedHistory,
                (string) $result['message']
            );

            $response = [
                'message' => $assistantMessage,
                'sources' => $result['sources'],
            ];

            if ($this->chatDebugEnabled && is_array($result['debug_meta'] ?? null)) {
                $response['debug'] = $result['debug_meta'];
            }

            $this->logChatEntry('assistant', $locale, $assistantMessage, $payload, [
                'sources_count' => is_array($result['sources'] ?? null) ? count($result['sources']) : 0,
            ]);

            return $this->response->setJSON($response);
        } catch (RuntimeException $exception) {
            log_message('error', 'AI chat request failed: ' . $exception->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'message' => $locale === 'en'
                    ? 'The AI assistant is temporarily unavailable. Please try again later.'
                    : 'AI Travel Plus đang tạm thời không khả dụng. Vui lòng thử lại sau.',
            ]);
        }
    }

    /**
     * @param list<array{role: string, text: string}> $history
     */
    private function appendLeadCaptureCta(string $locale, string $latestMessage, array $history, string $assistantMessage): string
    {
        $session = session();

        if ((bool) $session->get('ai_chat_lead_cta_asked')) {
            return $assistantMessage;
        }

        if ($this->conversationHasContact($latestMessage, $history)) {
            $session->set('ai_chat_lead_cta_asked', true);
            return $assistantMessage;
        }

        if (! $this->looksLikeLeadCaptureMoment($latestMessage, $history, $assistantMessage)) {
            return $assistantMessage;
        }

        $session->set('ai_chat_lead_cta_asked', true);

        $cta = $locale === 'en'
            ? 'If you want Travel Plus to advise directly, please leave your phone number or email here so a consultant can follow up.'
            : 'Nếu anh/chị muốn Travel Plus tư vấn trực tiếp, mình có thể để lại SĐT hoặc email tại đây để tư vấn viên liên hệ lại.';

        return rtrim($assistantMessage) . "\n\n" . $cta;
    }

    /**
     * @param list<array{role: string, text: string}> $history
     */
    private function conversationHasContact(string $latestMessage, array $history): bool
    {
        $leadService = new CrmLeadCaptureService();
        $contact = $leadService->extractContactFromText($latestMessage);

        if ($contact['email'] !== '' || $contact['phone'] !== '') {
            return true;
        }

        foreach (array_slice($history, -8) as $item) {
            if (($item['role'] ?? '') !== 'user') {
                continue;
            }

            $contact = $leadService->extractContactFromText((string) ($item['text'] ?? ''));

            if ($contact['email'] !== '' || $contact['phone'] !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array{role: string, text: string}> $history
     */
    private function looksLikeLeadCaptureMoment(string $latestMessage, array $history, string $assistantMessage): bool
    {
        $text = $this->normalizeIntentText($latestMessage . ' ' . $assistantMessage);

        $consultationSignals = [
            'tu van',
            'bao gia',
            'dat tour',
            'booking',
            'giu cho',
            'lien he',
            'goi lai',
            'visa',
            'mice',
            'proposal',
            'khach san',
            'hotel',
            've may bay',
            'van chuyen',
            'tour rieng',
            'tour theo yeu cau',
            'custom tour',
            'lich trinh',
            'chi phi',
            'gia',
            'ngan sach',
            'doan',
            'cong ty',
            'gia dinh',
            'so luong khach',
            'travel plus can advise',
            'consultant',
            'consultation',
            'call back',
            'phone',
            'email',
        ];

        foreach ($consultationSignals as $signal) {
            if (str_contains($text, $signal)) {
                return true;
            }
        }

        foreach (array_slice($history, -4) as $item) {
            if (($item['role'] ?? '') !== 'user') {
                continue;
            }

            $historyText = $this->normalizeIntentText((string) ($item['text'] ?? ''));
            foreach (['tour', 'visa', 'mice', 'booking', 'khach san', 'hotel'] as $signal) {
                if (str_contains($historyText, $signal)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalizeIntentText(string $text): string
    {
        $text = mb_strtolower($text);
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = is_string($ascii) && $ascii !== '' ? $ascii : $text;
        $text = preg_replace('/[^a-z0-9\s]+/i', ' ', $text) ?? '';

        return preg_replace('/\s+/', ' ', trim($text)) ?? '';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function captureLeadFromChat(string $locale, string $message, array $payload): void
    {
        $leadService = new CrmLeadCaptureService();
        $contact = $leadService->extractContactFromText($message);

        if ($contact['email'] === '' && $contact['phone'] === '') {
            return;
        }

        $leadService->capture([
            'source' => 'ai_chat',
            'stage' => 'new',
            'priority' => 'normal',
            'customer_email' => $contact['email'],
            'customer_phone' => $contact['phone'],
            'service_type' => 'chat',
            'interest_title' => $locale === 'en' ? 'AI chat lead' : 'Lead từ AI chat',
            'interest_url' => trim((string) ($payload['page_url'] ?? $payload['url'] ?? '')),
            'message' => $message,
            'metadata' => [
                'locale' => $locale,
                'session_id' => session_id(),
                'ip_address' => $this->request->getIPAddress(),
            ],
        ]);
    }

    private function passesRateLimit(): bool
    {
        $session = session();
        $now = time();
        $hits = $session->get('ai_chat_rate_hits');

        if (! is_array($hits)) {
            $hits = [];
        }

        $hits = array_values(array_filter(
            $hits,
            static fn ($timestamp): bool => is_int($timestamp) && $timestamp >= $now - self::RATE_LIMIT_WINDOW_SECONDS
        ));

        if (count($hits) >= self::RATE_LIMIT_MAX_MESSAGES) {
            $session->set('ai_chat_rate_hits', $hits);

            return false;
        }

        $hits[] = $now;
        $session->set('ai_chat_rate_hits', $hits);

        try {
            $ipKey = 'ai-chat-ip-' . hash('sha256', $this->request->getIPAddress());

            return service('throttler')->check(
                $ipKey,
                self::RATE_LIMIT_MAX_MESSAGES_PER_IP,
                self::RATE_LIMIT_WINDOW_SECONDS
            );
        } catch (Throwable) {
            // The session limit still protects the endpoint if the cache is unavailable.
            return true;
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function logChatEntry(string $role, string $locale, string $message, array $payload, array $extra = []): void
    {
        $directory = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'ai-chat';

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            log_message('error', 'Unable to create AI chat log directory: ' . $directory);
            return;
        }

        $this->cleanupChatLogs($directory);

        $session = session();
        $pageUrl = trim((string) ($payload['page_url'] ?? $payload['url'] ?? ''));
        $entry = [
            'timestamp' => date('c'),
            'role' => $role,
            'locale' => $locale,
            'message' => $this->redactLogMessage(mb_substr($message, 0, self::LOG_MAX_MESSAGE_LENGTH)),
            'session_hash' => $this->hashLogIdentifier(session_id()),
            'ip_hash' => $this->hashLogIdentifier($this->request->getIPAddress()),
            'user_agent' => mb_substr((string) $this->request->getUserAgent(), 0, 180),
            'page_path' => $this->normalizeLogPagePath($pageUrl),
            'history_count' => is_array($payload['history'] ?? null) ? count($payload['history']) : 0,
        ];

        $customerContext = $session->get('auth_user');
        if (is_array($customerContext) && ! empty($customerContext['id'])) {
            $entry['customer_id'] = (int) $customerContext['id'];
        }

        if ($extra !== []) {
            $entry += $extra;
        }

        $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            log_message('error', 'Unable to encode AI chat log entry.');
            return;
        }

        $file = $this->resolveChatLogFile($directory);
        if (file_put_contents($file, $json . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            log_message('error', 'Unable to write AI chat log file: ' . $file);
        }
    }

    private function redactLogMessage(string $message): string
    {
        $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '[email]', $message) ?? $message;

        return preg_replace('/(?<!\d)(?:\+?84|0)(?:[\s.()-]*\d){8,10}(?!\d)/u', '[phone]', $message) ?? $message;
    }

    private function hashLogIdentifier(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $key = (string) (config('Encryption')->key ?? '');
        if ($key === '') {
            $key = (string) config('App')->baseURL;
        }

        return substr(hash_hmac('sha256', $value, $key), 0, 20);
    }

    private function normalizeLogPagePath(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) ? mb_substr('/' . ltrim($path, '/'), 0, 500) : '';
    }

    private function resolveChatLogFile(string $directory): string
    {
        $baseName = date('Y-m-d');

        for ($part = 1; $part <= 99; $part++) {
            $suffix = $part === 1 ? '' : '-' . $part;
            $file = $directory . DIRECTORY_SEPARATOR . $baseName . $suffix . '.jsonl';

            if (! is_file($file) || (int) filesize($file) < self::LOG_MAX_FILE_BYTES) {
                return $file;
            }
        }

        return $directory . DIRECTORY_SEPARATOR . $baseName . '-' . date('His') . '.jsonl';
    }

    private function cleanupChatLogs(string $directory): void
    {
        $marker = $directory . DIRECTORY_SEPARATOR . '.cleanup';
        $today = strtotime('today');
        $lastCleanup = is_file($marker) ? (int) filemtime($marker) : 0;

        if ($lastCleanup >= $today) {
            return;
        }

        @touch($marker);
        $cutoff = time() - (self::LOG_RETENTION_DAYS * 86400);

        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.jsonl') ?: [] as $file) {
            if (is_file($file) && (int) filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
