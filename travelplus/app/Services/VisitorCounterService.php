<?php

namespace App\Services;

class VisitorCounterService
{
    private const SESSION_KEY = 'site_view_counted';

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
        $path = $this->getCounterPath();

        if (! is_file($path)) {
            return 0;
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            return 0;
        }

        try {
            if (! flock($handle, LOCK_SH)) {
                return 0;
            }

            $raw = stream_get_contents($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        return $this->parseCounterTotal(is_string($raw) ? $raw : '');
    }

    private function incrementCounter(): int
    {
        $path = $this->getCounterPath();
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (! is_dir($dir)) {
            return 0;
        }

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            return 0;
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                return $this->readCounter();
            }

            $raw = stream_get_contents($handle);
            $total = $this->parseCounterTotal(is_string($raw) ? $raw : '');
            $total++;

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode(['total' => $total], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            fflush($handle);
            flock($handle, LOCK_UN);

            return $total;
        } finally {
            fclose($handle);
        }
    }

    private function parseCounterTotal(string $raw): int
    {
        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return 0;
        }

        return max(0, (int) ($data['total'] ?? 0));
    }

    private function getCounterPath(): string
    {
        return WRITEPATH . 'stats/site-views.json';
    }
}
