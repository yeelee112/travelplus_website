<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use Config\Database;
use Config\Services;

class Destination extends Controller
{
    public function search()
    {
        $keyword = trim((string) $this->request->getGet('q'));
        $keyword = mb_strtolower($keyword);

        if ($keyword === '' || mb_strlen($keyword) < 2) {
            return $this->response->setJSON([]);
        }

        $locale = $this->request->getLocale() ?: 'vi';
        $index = $this->getDestinationIndex($locale);

        $results = array_filter($index, static function (array $item) use ($keyword): bool {
            return str_contains($item['search_text'], $keyword);
        });

        $results = array_slice(array_values($results), 0, 10);
        $payload = array_map(static fn(array $item): array => $item['payload'], $results);

        return $this->response->setJSON($payload);
    }

    /**
     * @return array<int, array{search_text: string, payload: array<string, mixed>}>
     */
    private function getDestinationIndex(string $locale): array
    {
        $cache = Services::cache();
        $cacheKey = 'destination_search_index_v2_' . $locale;
        $cached = $cache->get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $index = $this->buildFromDatabase($locale);

        if (empty($index)) {
            $index = $this->buildFromFileFallback();
        }

        $cache->save($cacheKey, $index, 86400);

        return $index;
    }

    /**
     * @return array<int, array{search_text: string, payload: array<string, mixed>}>
     */
    private function buildFromDatabase(string $locale): array
    {
        $db = Database::connect();

        if (
            !$db->tableExists('locations')
            || !$db->tableExists('location_translations')
        ) {
            return [];
        }

        $rows = $db->table('locations l')
            ->select('l.type, l.code, lt.name, plt.name AS parent_name')
            ->join('location_translations lt', 'lt.location_id = l.id AND lt.locale = ' . $db->escape($locale), 'inner')
            ->join('locations pl', 'pl.id = l.parent_id', 'left')
            ->join('location_translations plt', 'plt.location_id = pl.id AND plt.locale = ' . $db->escape($locale), 'left')
            ->whereIn('l.type', ['country', 'province'])
            ->get()
            ->getResultArray();

        $index = [];

        foreach ($rows as $row) {
            $isCountry = ($row['type'] ?? '') === 'country';

            $payload = [
                'type' => $isCountry ? 'country' : 'city',
                'name' => (string) ($row['name'] ?? ''),
                'code' => $row['code'] ?? null,
            ];

            if (!$isCountry) {
                $payload['country'] = (string) ($row['parent_name'] ?? '');
            }

            $text = $isCountry
                ? $payload['name']
                : trim($payload['name'] . ' ' . ($payload['country'] ?? ''));

            $index[] = [
                'search_text' => mb_strtolower($text),
                'payload' => $payload,
            ];
        }

        return $index;
    }

    /**
     * @return array<int, array{search_text: string, payload: array<string, mixed>}>
     */
    private function buildFromFileFallback(): array
    {
        $data = include APPPATH . 'Views/data/destinations.php';
        $index = [];

        foreach ($data as $item) {
            $text = $item['type'] === 'country'
                ? (string) ($item['name'] ?? '')
                : trim((string) ($item['name'] ?? '') . ' ' . (string) ($item['country'] ?? ''));

            $index[] = [
                'search_text' => mb_strtolower($text),
                'payload' => $item,
            ];
        }

        return $index;
    }
}
