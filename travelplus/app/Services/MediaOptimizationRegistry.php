<?php

namespace App\Services;

final class MediaOptimizationRegistry
{
    private string $manifestPath;

    /** @var array<string, array{size: int, modified_at: int}> */
    private array $records = [];

    public function __construct(?string $manifestPath = null)
    {
        $this->manifestPath = $manifestPath ?? WRITEPATH . 'stats/media-optimization.json';
        $this->records = $this->loadRecords();
    }

    public function isCurrent(string $relativePath, string $absolutePath): bool
    {
        $fingerprint = $this->fingerprint($absolutePath);
        $record = $this->records[$this->normalizePath($relativePath)] ?? null;

        return $fingerprint !== null
            && is_array($record)
            && (int) ($record['size'] ?? -1) === $fingerprint['size']
            && (int) ($record['modified_at'] ?? -1) === $fingerprint['modified_at'];
    }

    public function markCurrent(string $relativePath, string $absolutePath): void
    {
        $fingerprint = $this->fingerprint($absolutePath);
        if ($fingerprint === null) {
            return;
        }

        $this->records[$this->normalizePath($relativePath)] = $fingerprint;
    }

    public function persist(): bool
    {
        $directory = dirname($this->manifestPath);
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return false;
        }

        $payload = json_encode([
            'version' => 1,
            'updated_at' => date(DATE_ATOM),
            'files' => $this->records,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return is_string($payload)
            && @file_put_contents($this->manifestPath, $payload, LOCK_EX) !== false;
    }

    /**
     * @return array<string, array{size: int, modified_at: int}>
     */
    private function loadRecords(): array
    {
        if (! is_file($this->manifestPath)) {
            return [];
        }

        $decoded = json_decode((string) @file_get_contents($this->manifestPath), true);
        $files = is_array($decoded) ? ($decoded['files'] ?? []) : [];

        return is_array($files) ? $files : [];
    }

    /**
     * @return array{size: int, modified_at: int}|null
     */
    private function fingerprint(string $absolutePath): ?array
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        clearstatcache(true, $absolutePath);

        return [
            'size' => (int) (filesize($absolutePath) ?: 0),
            'modified_at' => (int) (filemtime($absolutePath) ?: 0),
        ];
    }

    private function normalizePath(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }
}
