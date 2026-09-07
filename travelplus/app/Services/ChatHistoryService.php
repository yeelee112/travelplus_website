<?php

namespace App\Services;

class ChatHistoryService
{
    public function __construct(private ?string $directory = null)
    {
        $this->directory ??= WRITEPATH . 'logs/ai-chat';
    }

    public function search(string $date, string $query = '', string $selected = '', int $page = 1): array
    {
        $files = glob($this->directory . '/*.jsonl') ?: [];
        $dates = [];
        foreach ($files as $file) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2})(?:-\d+)?\.jsonl$/', basename($file), $match)) {
                $dates[$match[1]] = $match[1];
            }
        }
        rsort($dates);
        $date = in_array($date, $dates, true) ? $date : ($dates[0] ?? date('Y-m-d'));
        $query = mb_substr(trim($query), 0, 200);
        $groups = [];
        $bytes = 0;
        $limited = false;
        foreach ($files as $file) {
            if (! preg_match('/^' . preg_quote($date, '/') . '(?:-\d+)?\.jsonl$/', basename($file))) {
                continue;
            }
            $handle = @fopen($file, 'rb');
            if ($handle === false) {
                continue;
            }
            while (($line = fgets($handle, 65537)) !== false) {
                $bytes += strlen($line);
                if ($bytes > 20 * 1024 * 1024) {
                    $limited = true;
                    break;
                }
                $entry = json_decode($line, true);
                if (! is_array($entry) || ! is_string($entry['message'] ?? null)
                    || ! in_array($entry['role'] ?? '', ['user', 'assistant'], true)
                    || ! is_string($entry['timestamp'] ?? null) || strtotime($entry['timestamp']) === false) {
                    continue;
                }
                $identity = $entry['session_hash'] ?? $entry['session_id'] ?? '';
                // Never merge anonymous records or expose legacy session identifiers.
                $id = hash('sha256', is_string($identity) && $identity !== '' ? $identity : $file . ':' . ftell($handle));
                $message = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu', '[email]', $entry['message']);
                $message = preg_replace('/(?<!\d)(?:\+?84|0)(?:[\s.()-]*\d){8,10}(?!\d)/u', '[phone]', $message ?? '');
                $path = parse_url((string) ($entry['page_path'] ?? $entry['page_url'] ?? ''), PHP_URL_PATH);
                $item = ['role' => $entry['role'], 'message' => $message ?? '',
                    'timestamp' => $entry['timestamp'], 'time' => strtotime($entry['timestamp']),
                    'path' => is_string($path) ? $path : '', 'locale' => ($entry['locale'] ?? '') === 'en' ? 'EN' : 'VI'];
                $groups[$id]['id'] = $id;
                $groups[$id]['messages'][] = $item;
            }
            fclose($handle);
            if ($limited) {
                break;
            }
        }
        foreach ($groups as &$group) {
            usort($group['messages'], static fn ($a, $b) => $a['time'] <=> $b['time']);
            $group['last'] = end($group['messages']);
            $group['preview'] = $group['messages'][0]['message'];
        }
        unset($group);
        uasort($groups, static fn ($a, $b) => $b['last']['time'] <=> $a['last']['time']);
        $groups = array_filter($groups, static function ($group) use ($query) {
            return $query === '' || mb_stripos(implode("\n", array_column($group['messages'], 'message')), $query) !== false;
        });
        $total = count($groups);
        $pages = max(1, (int) ceil($total / 20));
        $page = max(1, min($pages, $page));
        $conversations = array_slice($groups, ($page - 1) * 20, 20, true);
        $conversation = $groups[$selected] ?? ($selected === '' ? (reset($conversations) ?: null) : null);

        return compact('dates', 'date', 'query', 'conversations', 'conversation', 'total', 'pages', 'page', 'limited');
    }
}
