<?php

namespace App\Services;

use DateTimeImmutable;

class SystemLogService
{
    private const MAX_FILES = 31;
    private const MAX_BYTES_PER_FILE = 1024 * 1024;
    private const MAX_RESULTS = 100;
    private const ISSUE_LEVELS = ['emergency', 'alert', 'critical', 'error', 'warning'];

    /**
     * @return array{
     *     generated_at: string,
     *     filters: array{days: int, level: string, query: string},
     *     entries: list<array<string, string>>,
     *     summary: array{total: int, critical: int, error: int, warning: int},
     *     scan: array{files: int, bytes: int, truncated: bool}
     * }
     */
    public function search(int $days = 7, string $level = 'all', string $query = ''): array
    {
        $days = in_array($days, [1, 3, 7, 14, 30], true) ? $days : 7;
        $level = in_array($level, ['all', 'critical', 'error', 'warning'], true) ? $level : 'all';
        $query = mb_substr(trim($query), 0, 80);
        $cutoff = (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days')->setTime(0, 0);

        $entries = [];
        $summary = ['total' => 0, 'critical' => 0, 'error' => 0, 'warning' => 0];
        $filesScanned = 0;
        $bytesRead = 0;

        foreach ($this->recentLogFiles($cutoff) as $path) {
            [$content, $readBytes] = $this->readTail($path);
            $filesScanned++;
            $bytesRead += $readBytes;

            foreach (self::parseContent($content) as $entry) {
                $timestamp = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $entry['datetime']);
                if (! $timestamp instanceof DateTimeImmutable || $timestamp < $cutoff) {
                    continue;
                }

                $summary['total']++;
                $bucket = $this->summaryBucket($entry['level']);
                $summary[$bucket]++;

                if (! $this->matchesLevel($entry['level'], $level)) {
                    continue;
                }
                if ($query !== '' && ! str_contains($entry['search_text'], mb_strtolower($query, 'UTF-8'))) {
                    continue;
                }

                unset($entry['search_text']);
                $entries[] = $entry;
            }
        }

        usort($entries, static fn (array $left, array $right): int => strcmp($right['datetime'], $left['datetime']));
        $truncated = count($entries) > self::MAX_RESULTS;
        $entries = array_slice($entries, 0, self::MAX_RESULTS);

        return [
            'generated_at' => date('d/m/Y H:i:s'),
            'filters' => ['days' => $days, 'level' => $level, 'query' => $query],
            'entries' => $entries,
            'summary' => $summary,
            'scan' => ['files' => $filesScanned, 'bytes' => $bytesRead, 'truncated' => $truncated],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function parseContent(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $pattern = '/^(EMERGENCY|ALERT|CRITICAL|ERROR|WARNING) - (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) --> (.*?)(?=^(?:EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG) - \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} --> |\z)/ms';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER) < 1) {
            return [];
        }

        $entries = [];
        foreach ($matches as $match) {
            $level = strtolower((string) $match[1]);
            $body = trim((string) $match[3]);
            $lines = preg_split('/\R/', $body) ?: [];
            $message = self::redact(trim((string) array_shift($lines)));
            $details = self::redact(trim(implode("\n", $lines)));
            $method = '';
            $route = '';

            if (preg_match('/\[Method:\s*([^,\]]+),\s*Route:\s*([^\]]+)\]/i', $body, $context) === 1) {
                $method = strtoupper(trim((string) $context[1]));
                $route = self::redact(trim((string) $context[2]));
            }

            $message = mb_substr($message !== '' ? $message : 'Không có nội dung lỗi.', 0, 600);
            $details = mb_substr($details, 0, 12000);
            $entries[] = [
                'level' => $level,
                'datetime' => (string) $match[2],
                'message' => $message,
                'details' => $details,
                'method' => $method,
                'route' => $route,
                'search_text' => mb_strtolower($message . ' ' . $details . ' ' . $method . ' ' . $route, 'UTF-8'),
            ];
        }

        return $entries;
    }

    public static function redact(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = preg_replace(
            '/(?i)\b(password|passwd|pwd|secret|token|api[_-]?key|authorization|cookie|session[_-]?id)(\s*[=:]\s*|%3D)[^\s,;&]+/',
            '$1$2[REDACTED]',
            $text
        ) ?? $text;
        $text = preg_replace_callback(
            '/\b([A-Z0-9._%+\-]{2,})@([A-Z0-9.\-]+\.[A-Z]{2,})\b/i',
            static fn (array $match): string => mb_substr((string) $match[1], 0, 1) . '***@' . (string) $match[2],
            $text
        ) ?? $text;
        $text = preg_replace('/(?<!\d)(?:\+?84|0)(?:[ .-]?\d){9,10}(?!\d)/', '[PHONE REDACTED]', $text) ?? $text;

        return $text;
    }

    /**
     * @return list<string>
     */
    private function recentLogFiles(DateTimeImmutable $cutoff): array
    {
        $files = glob(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'log-????-??-??.log') ?: [];
        rsort($files, SORT_STRING);
        $files = array_slice($files, 0, self::MAX_FILES);

        return array_values(array_filter($files, static function (string $path) use ($cutoff): bool {
            if (preg_match('/log-(\d{4}-\d{2}-\d{2})\.log$/', str_replace('\\', '/', $path), $match) !== 1) {
                return false;
            }

            $fileDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $match[1]);

            return $fileDate instanceof DateTimeImmutable && $fileDate >= $cutoff;
        }));
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function readTail(string $path): array
    {
        $size = @filesize($path);
        if (! is_int($size) || $size < 1) {
            return ['', 0];
        }

        $readBytes = min($size, self::MAX_BYTES_PER_FILE);
        $handle = @fopen($path, 'rb');
        if (! is_resource($handle)) {
            return ['', 0];
        }

        $offset = max(0, $size - $readBytes);
        if ($offset > 0) {
            fseek($handle, $offset);
        }
        $content = (string) fread($handle, $readBytes);
        fclose($handle);

        if ($offset > 0) {
            $firstLineBreak = strpos($content, "\n");
            $content = $firstLineBreak === false ? '' : substr($content, $firstLineBreak + 1);
        }

        return [$content, $readBytes];
    }

    private function matchesLevel(string $entryLevel, string $filter): bool
    {
        if ($filter === 'all') {
            return in_array($entryLevel, self::ISSUE_LEVELS, true);
        }
        if ($filter === 'critical') {
            return in_array($entryLevel, ['emergency', 'alert', 'critical'], true);
        }

        return $entryLevel === $filter;
    }

    private function summaryBucket(string $level): string
    {
        if (in_array($level, ['emergency', 'alert', 'critical'], true)) {
            return 'critical';
        }
        if ($level === 'error') {
            return 'error';
        }

        return 'warning';
    }
}
