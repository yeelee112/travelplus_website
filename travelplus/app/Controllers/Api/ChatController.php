<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\GeminiWebsiteChatService;
use RuntimeException;

class ChatController extends BaseController
{
    private const MAX_MESSAGE_LENGTH = 1000;
    private const RATE_LIMIT_WINDOW_SECONDS = 60;
    private const RATE_LIMIT_MAX_MESSAGES = 12;

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

        $this->logChatEntry('user', $locale, $message, $payload);

        if (! $this->passesRateLimit()) {
            return $this->response->setStatusCode(429)->setJSON([
                'message' => $locale === 'en'
                    ? 'You are sending messages too quickly. Please try again in a moment.'
                    : 'Bạn đang gửi tin nhắn quá nhanh. Vui lòng thử lại sau ít phút.',
            ]);
        }

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

            $response = [
                'message' => $result['message'],
                'sources' => $result['sources'],
            ];

            if ($this->chatDebugEnabled && is_array($result['debug_meta'] ?? null)) {
                $response['debug'] = $result['debug_meta'];
            }

            $this->logChatEntry('assistant', $locale, (string) $result['message'], $payload, [
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

        return true;
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

        $session = session();
        $pageUrl = trim((string) ($payload['page_url'] ?? $payload['url'] ?? ''));
        $entry = [
            'timestamp' => date('c'),
            'role' => $role,
            'locale' => $locale,
            'message' => $message,
            'session_id' => session_id(),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => mb_substr((string) $this->request->getUserAgent(), 0, 500),
            'page_url' => $pageUrl,
            'history_count' => is_array($payload['history'] ?? null) ? count($payload['history']) : 0,
        ];

        $customerContext = $session->get('user');
        if (is_array($customerContext)) {
            $entry['customer'] = [
                'id' => $customerContext['id'] ?? null,
                'email' => $customerContext['email'] ?? null,
                'name' => $customerContext['full_name'] ?? null,
            ];
        }

        if ($extra !== []) {
            $entry += $extra;
        }

        $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            log_message('error', 'Unable to encode AI chat log entry.');
            return;
        }

        $file = $directory . DIRECTORY_SEPARATOR . date('Y-m-d') . '.jsonl';
        if (file_put_contents($file, $json . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            log_message('error', 'Unable to write AI chat log file: ' . $file);
        }
    }
}
