<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\GeminiWebsiteChatService;
use RuntimeException;

class ChatController extends BaseController
{
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
        $message = trim((string) ($payload['message'] ?? ''));
        $history = is_array($payload['history'] ?? null) ? $payload['history'] : [];

        if ($message === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'message' => $locale === 'en' ? 'Please enter a message.' : 'Vui lòng nhập nội dung.',
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
                    ? 'AI chat is not configured yet.'
                    : 'Chat AI chưa được cấu hình.');
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

            return $this->response->setJSON($response);
        } catch (RuntimeException $exception) {
            return $this->response->setStatusCode(500)->setJSON([
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
