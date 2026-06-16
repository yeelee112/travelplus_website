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
        $keyword = $this->normalizeSearchText($keyword);

        if ($keyword === '' || mb_strlen($keyword) < 2) {
            return $this->response->setJSON([]);
        }

        $locale = $this->request->getLocale() ?: 'vi';
        $index = $this->getDestinationIndex($locale);

        $results = [];

        foreach ($index as $item) {
            $searchText = (string) ($item['search_text'] ?? '');

            if (! str_contains($searchText, $keyword)) {
                continue;
            }

            $results[] = [
                'score' => $this->scoreMatch($searchText, $keyword),
                'payload' => $item['payload'],
            ];
        }

        usort($results, static function (array $a, array $b): int {
            $scoreCompare = ($b['score'] ?? 0) <=> ($a['score'] ?? 0);

            if ($scoreCompare !== 0) {
                return $scoreCompare;
            }

            $nameA = mb_strlen((string) (($a['payload']['name'] ?? '')));
            $nameB = mb_strlen((string) (($b['payload']['name'] ?? '')));

            return $nameA <=> $nameB;
        });

        $results = array_slice($results, 0, 10);
        $payload = array_map(static fn(array $item): array => $item['payload'], $results);

        return $this->response->setJSON($payload);
    }

    /**
     * @return array<int, array{search_text: string, payload: array<string, mixed>}>
     */
    private function getDestinationIndex(string $locale): array
    {
        $cache = Services::cache();
        $cacheKey = 'destination_search_index_v6_' . $locale . '_' . $this->getCacheSignature();
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
                'search_text' => $this->normalizeSearchText($text),
                'payload' => $payload,
            ];
        }

        if (
            $db->tableExists('tour_media')
            && $db->tableExists('tour_translations')
            && $db->tableExists('tours')
        ) {
            $mediaRows = $db->table('tour_media tm')
                ->select('tm.alt_text, tm.file_path, tt.name AS tour_name')
                ->join('tours t', 't.id = tm.tour_id', 'inner')
                ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $db->escape($locale), 'inner')
                ->where('tm.type', 'gallery')
                ->where('t.status', 'published')
                ->get()
                ->getResultArray();

            foreach ($mediaRows as $row) {
                $altText = trim((string) ($row['alt_text'] ?? ''));
                $filePath = trim((string) ($row['file_path'] ?? ''));
                $tourName = trim((string) ($row['tour_name'] ?? ''));
                $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                $fileName = trim(str_replace(['-', '_'], ' ', (string) $fileName));
                $name = $altText !== '' ? $altText : $fileName;

                if ($name === '') {
                    continue;
                }

                $index[] = [
                    'search_text' => $this->normalizeSearchText(trim($name . ' ' . $altText . ' ' . $fileName . ' ' . $tourName)),
                    'payload' => [
                        'type' => 'gallery',
                        'name' => $name,
                        'tour' => $tourName,
                    ],
                ];
            }
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
                'search_text' => $this->normalizeSearchText($text),
                'payload' => $item,
            ];
        }

        return $index;
    }

    private function getCacheSignature(): string
    {
        $db = Database::connect();
        $tables = [
            'locations',
            'location_translations',
            'tours',
            'tour_translations',
            'tour_media',
        ];
        $parts = [];

        foreach ($tables as $table) {
            if (! $db->tableExists($table)) {
                $parts[] = $table . ':0:0';
                continue;
            }

            $row = $db->table($table)
                ->select('COUNT(*) AS total, COALESCE(MAX(id), 0) AS max_id', false)
                ->get()
                ->getRowArray();

            $parts[] = $table . ':' . (int) ($row['total'] ?? 0) . ':' . (int) ($row['max_id'] ?? 0);
        }

        return md5(implode('|', $parts));
    }

    private function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $map = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ];

        return strtr($value, $map);
    }

    private function scoreMatch(string $searchText, string $keyword): int
    {
        if ($searchText === $keyword) {
            return 400;
        }

        if (str_starts_with($searchText, $keyword)) {
            return 320;
        }

        if (preg_match('/\b' . preg_quote($keyword, '/') . '/u', $searchText) === 1) {
            return 240;
        }

        return 100;
    }
}
