<?php

namespace App\Services;

class VisitorCounterService
{
    private const SESSION_KEY = 'site_view_counted';
    private const BASELINE_TOTAL = 5000;

    private string $counterPath;

    public function __construct(?string $counterPath = null)
    {
        $this->counterPath = $counterPath ?? WRITEPATH . 'stats/site-views.json';
    }

    public function getTotalViews(): int
    {
        $session = session();

        if (! $session->get(self::SESSION_KEY)) {
            $total = $this->incrementCounter();
            $session->set(self::SESSION_KEY, true);

            return $total;
        }

        return $this->readCounter();
    }

    private function readCounter(): int
    {
        if (! is_dir(dirname($this->counterPath))) {
            return self::BASELINE_TOTAL;
        }

        $lockHandle = @fopen($this->getLockPath(), 'c+');

        if ($lockHandle === false) {
            return $this->readBestAvailableTotal();
        }

        if (! flock($lockHandle, LOCK_SH)) {
            fclose($lockHandle);

            return $this->readBestAvailableTotal();
        }

        try {
            return $this->readBestAvailableTotal();
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function incrementCounter(): int
    {
        if (! $this->ensureCounterDirectory()) {
            return self::BASELINE_TOTAL;
        }

        $lockHandle = @fopen($this->getLockPath(), 'c+');

        if ($lockHandle === false) {
            return $this->readBestAvailableTotal();
        }

        if (! flock($lockHandle, LOCK_EX)) {
            fclose($lockHandle);

            return $this->readBestAvailableTotal();
        }

        try {
            $currentTotal = $this->readBestAvailableTotal();
            $nextTotal = $currentTotal + 1;

            // Keep the previous valid total before replacing the primary snapshot.
            if (! $this->writeSnapshot($this->getBackupPath(), $currentTotal)) {
                return $currentTotal;
            }

            if (! $this->writeSnapshot($this->counterPath, $nextTotal)) {
                return $currentTotal;
            }

            return $nextTotal;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    private function readBestAvailableTotal(): int
    {
        $totals = [self::BASELINE_TOTAL];

        foreach ([$this->counterPath, $this->getBackupPath()] as $path) {
            $total = $this->readSnapshot($path);

            if ($total !== null) {
                $totals[] = $total;
            }
        }

        return max($totals);
    }

    private function readSnapshot(string $path): ?int
    {
        if (! is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);

        if (! is_string($raw)) {
            return null;
        }

        $data = json_decode($raw, true);

        if (! is_array($data) || ! array_key_exists('total', $data) || ! is_numeric($data['total'])) {
            return null;
        }

        $total = (int) $data['total'];

        return $total >= 0 ? $total : null;
    }

    private function writeSnapshot(string $path, int $total): bool
    {
        $payload = json_encode(
            ['total' => max(self::BASELINE_TOTAL, $total)],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if (! is_string($payload)) {
            return false;
        }

        $directory = dirname($path);
        $temporaryPath = @tempnam($directory, basename($path) . '.tmp-');

        if ($temporaryPath === false) {
            return false;
        }

        $handle = @fopen($temporaryPath, 'wb');

        if ($handle === false) {
            @unlink($temporaryPath);

            return false;
        }

        $written = 0;
        $payloadLength = strlen($payload);
        $writeSucceeded = true;

        try {
            while ($written < $payloadLength) {
                $bytes = fwrite($handle, substr($payload, $written));

                if ($bytes === false || $bytes === 0) {
                    $writeSucceeded = false;
                    break;
                }

                $written += $bytes;
            }

            if ($writeSucceeded && ! fflush($handle)) {
                $writeSucceeded = false;
            }

            if ($writeSucceeded && function_exists('fsync')) {
                @fsync($handle);
            }
        } finally {
            fclose($handle);
        }

        if (! $writeSucceeded || $written !== $payloadLength) {
            @unlink($temporaryPath);

            return false;
        }

        if (@rename($temporaryPath, $path)) {
            @chmod($path, 0644);

            return true;
        }

        // Windows cannot always replace an existing file with rename(). Reads
        // still remain protected by the separate lock while using this fallback.
        if (DIRECTORY_SEPARATOR === '\\' && @copy($temporaryPath, $path)) {
            @unlink($temporaryPath);
            @chmod($path, 0644);

            return true;
        }

        @unlink($temporaryPath);

        return false;
    }

    private function ensureCounterDirectory(): bool
    {
        $directory = dirname($this->counterPath);

        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        return is_dir($directory);
    }

    private function getBackupPath(): string
    {
        return $this->counterPath . '.bak';
    }

    private function getLockPath(): string
    {
        return $this->counterPath . '.lock';
    }
}
