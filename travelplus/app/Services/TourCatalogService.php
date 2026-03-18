<?php

namespace App\Services;

use App\Data\TourCard;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class TourCatalogService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getHomeTours(string $locale = 'vi', int $limit = 6, ?string $tourType = null): array
    {
        return $this->fetchTours($locale, $limit, 0, $tourType);
    }

    /**
     * @return array{tours: array<int, array<string, mixed>>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function getPagedTours(string $locale = 'vi', int $perPage = 9, int $page = 1, ?string $tourType = null): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        if (!$this->hasSchemaForTourCatalog()) {
            $fallback = $this->fallbackTours($offset, $perPage);
            $total = count(TourCard::getAll());
            $lastPage = max(1, (int) ceil($total / $perPage));

            return [
                'tours'    => $fallback,
                'total'    => $total,
                'page'     => min($page, $lastPage),
                'perPage'  => $perPage,
                'lastPage' => $lastPage,
            ];
        }

        $countBuilder = $this->baseToursBuilder($locale, $tourType);
        $total = (int) $countBuilder->countAllResults();
        $lastPage = max(1, (int) ceil(max(1, $total) / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $rows = $this->baseToursBuilder($locale, $tourType)
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(td.price) AS min_price,' .
                'COALESCE(dltn.name, t.tour_type) AS departure_name'
            )
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug, dltn.name')
            ->orderBy($this->getSortField(), 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'tours'    => $this->mapRowsToCards($rows),
            'total'    => $total,
            'page'     => $page,
            'perPage'  => $perPage,
            'lastPage' => $lastPage,
        ];
    }

    private function fetchTours(string $locale, int $limit, int $offset, ?string $tourType = null): array
    {
        if (!$this->hasSchemaForTourCatalog()) {
            return $this->fallbackTours($offset, $limit);
        }

        $rows = $this->baseToursBuilder($locale, $tourType)
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(td.price) AS min_price,' .
                'COALESCE(dltn.name, t.tour_type) AS departure_name'
            )
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug, dltn.name')
            ->orderBy($this->getSortField(), 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return $this->fallbackTours($offset, $limit);
        }

        return $this->mapRowsToCards($rows);
    }

    private function baseToursBuilder(string $locale, ?string $tourType = null): BaseBuilder
    {
        $builder = $this->db->table('tours t')
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $this->db->escape($locale), 'inner')
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'left')
            ->join('location_translations dltn', 'dltn.location_id = t.departure_location_id AND dltn.locale = ' . $this->db->escape($locale), 'left')
            ->where('t.status', 'published');

        if ($tourType !== null) {
            $builder->where('t.tour_type', $tourType);
        }

        return $builder;
    }

    private function getSortField(): string
    {
        if ($this->db->fieldExists('created_at', 'tours')) {
            return 't.created_at';
        }

        return 't.id';
    }

    private function hasSchemaForTourCatalog(): bool
    {
        return $this->db->tableExists('tours')
            && $this->db->tableExists('tour_translations')
            && $this->db->tableExists('tour_departures')
            && $this->db->tableExists('location_translations');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackTours(int $offset, int $limit): array
    {
        return array_slice(TourCard::getAll(), $offset, $limit);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function mapRowsToCards(array $rows): array
    {
        $cards = [];

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $days = (int) ($row['duration_days'] ?? 0);
            $nights = (int) ($row['duration_nights'] ?? 0);
            $price = (float) ($row['min_price'] ?? 0);

            $cards[] = [
                'id'        => $id,
                'title'     => (string) ($row['title'] ?? ('Tour #' . $id)),
                'slug'      => (string) ($row['slug'] ?? ('tour-' . $id)),
                'link'      => localized_url('tour-nuoc-ngoai'),
                'image'     => $this->resolveImage((string) ($row['thumbnail'] ?? '')),
                'badge'     => !empty($row['is_featured']) ? 'Hot Sale!' : null,
                'continent' => (string) ($row['departure_name'] ?? 'International'),
                'departure' => $this->formatDate((string) ($row['departure_date'] ?? '')),
                'duration'  => [
                    'days'   => $days,
                    'nights' => $nights,
                    'label'  => sprintf('%02d Days / %02d Nights', max(0, $days), max(0, $nights)),
                ],
                'price'     => [
                    'amount'   => $price,
                    'currency' => 'VND',
                    'label'    => number_format($price, 0, ',', '.') . ' VND',
                ],
            ];
        }

        return $cards;
    }

    private function resolveImage(string $thumbnail): string
    {
        if ($thumbnail === '') {
            return base_url('assets/images/avt-tour-01.jpg');
        }

        if (str_starts_with($thumbnail, 'http://') || str_starts_with($thumbnail, 'https://')) {
            return $thumbnail;
        }

        if (str_starts_with($thumbnail, 'assets/')) {
            return base_url($thumbnail);
        }

        return base_url('assets/images/' . ltrim($thumbnail, '/'));
    }

    private function formatDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return '';
        }

        return date('d/m/Y', $timestamp);
    }
}
