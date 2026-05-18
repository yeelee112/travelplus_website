<?php

namespace App\Services;

class VisitorCounterService
{
    private const SESSION_KEY = 'site_view_counted';

    public function getTotalViews(): int
    {
        $session = session();
        $data = $this->readCounter();

        if (! $session->get(self::SESSION_KEY)) {
            $data['total'] = max(0, (int) ($data['total'] ?? 0)) + 1;
            $this->writeCounter($data);
            $session->set(self::SESSION_KEY, true);
        }

        return max(0, (int) ($data['total'] ?? 0));
    }

    /**
     * @return array{total:int}
     */
    private function readCounter(): array
    {
        $path = $this->getCounterPath();

        if (! is_file($path)) {
            return ['total' => 0];
        }

        $raw = file_get_contents($path);
        $data = json_decode((string) $raw, true);

        return is_array($data) ? ['total' => (int) ($data['total'] ?? 0)] : ['total' => 0];
    }

    /**
     * @param array{total:int} $data
     */
    private function writeCounter(array $data): void
    {
        $path = $this->getCounterPath();
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            return;
        }

        try {
            if (flock($handle, LOCK_EX)) {
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                fflush($handle);
                flock($handle, LOCK_UN);
            }
        } finally {
            fclose($handle);
        }
    }

    private function getCounterPath(): string
    {
        return WRITEPATH . 'stats/site-views.json';
    }
}
