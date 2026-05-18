<?php

namespace App\Services;

use App\Data\FeaturedDestinationCatalog;
use App\Data\FeaturedDestinationImageMap;
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

    public function getFeaturedTours(string $locale = 'vi', int $limit = 6, ?string $tourType = null): array
    {
        return $this->fetchTours($locale, $limit, 0, $tourType, [], true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFeaturedDestinations(string $locale = 'vi', int $itemsPerTab = 6): array
    {
        if (! $this->hasSchemaForTourCatalog()) {
            return [];
        }

        $tabs = [];
        $domesticItems = $this->getDomesticFeaturedDestinations($locale, $itemsPerTab);

        if ($domesticItems !== []) {
            $tabs[] = [
                'key' => 'vietnam',
                'label' => $locale === 'en' ? 'Vietnam' : 'Việt Nam',
                'items' => $domesticItems,
            ];
        }

        foreach ($this->getOutboundFeaturedDestinations($locale, $itemsPerTab) as $tab) {
            if (($tab['items'] ?? []) === []) {
                continue;
            }

            $tabs[] = $tab;
        }

        return $tabs;
    }

    public function findTourBySlug(string $locale, string $slug, ?string $tourType = null): ?array
    {
        if (!$this->hasSchemaForTourCatalog()) {
            return null;
        }

        $row = $this->baseToursBuilder($locale, $tourType)
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(dl.id) AS destination_id,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(td.price) AS min_price,' .
                'MIN(COALESCE(dlgptn.name, dlptn.name, dltn.name, t.tour_type)) AS continent_name,' .
                'MIN(COALESCE(dlgptn.slug, dlptn.slug, dltn.slug)) AS continent_slug'
            )
            ->where('tt.slug', $slug)
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return null;
        }

        $cards = $this->mapRowsToCards([$row]);
        $card = $cards[0] ?? null;

        if ($card === null) {
            return null;
        }

        return $this->hydrateTourDetail($card, $locale);
    }

    /**
     * @param array<string, mixed> $tour
     * @return array<int, array<string, mixed>>
     */
    public function getRelatedTours(string $locale, array $tour, int $limit = 6): array
    {
        if (!$this->hasSchemaForTourCatalog()) {
            return [];
        }

        $tourId = (int) ($tour['id'] ?? 0);
        $tourType = (string) ($tour['tour_type'] ?? '');
        $locationFilter = $this->buildRelatedLocationFilter($locale, $tour);

        $rows = $this->baseToursBuilder($locale, $tourType !== '' ? $tourType : null, $locationFilter)
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(dl.id) AS destination_id,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(t.base_price) AS min_price,' .
                'MIN(COALESCE(dlgptn.name, dlptn.name, dltn.name, t.tour_type)) AS continent_name,' .
                'MIN(COALESCE(dlgptn.slug, dlptn.slug, dltn.slug)) AS continent_slug'
            )
            ->where('t.id !=', $tourId)
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug')
            ->orderBy($this->getSortField(), 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        return $this->mapRowsToCards($rows);
    }

    /**
     * @return array{tours: array<int, array<string, mixed>>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function getPagedTours(
        string $locale = 'vi',
        int $perPage = 9,
        int $page = 1,
        ?string $tourType = null,
        array $locationFilter = []
    ): array
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

        $countRow = $this->baseToursBuilder($locale, $tourType, $locationFilter)
            ->select('COUNT(DISTINCT t.id) AS total', false)
            ->get()
            ->getRowArray();
        $total = (int) ($countRow['total'] ?? 0);
        $lastPage = max(1, (int) ceil(max(1, $total) / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $rows = $this->baseToursBuilder($locale, $tourType, $locationFilter)
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(dl.id) AS destination_id,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(t.base_price) AS min_price,' .
                'MIN(COALESCE(dlgptn.name, dlptn.name, dltn.name, t.tour_type)) AS continent_name,' .
                'MIN(COALESCE(dlgptn.slug, dlptn.slug, dltn.slug)) AS continent_slug'
            )
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug')
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

    /**
     * @return array{tours: array<int, array<string, mixed>>, total: int, page: int, perPage: int, lastPage: int}
     */
    public function searchTours(
        string $locale,
        string $query,
        string $departureDate = '',
        int $perPage = 9,
        int $page = 1
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $query = trim($query);

        if ($query === '' && $departureDate === '') {
            return $this->getPagedTours($locale, $perPage, $page);
        }

        if (!$this->hasSchemaForTourCatalog()) {
            return [
                'tours' => [],
                'total' => 0,
                'page' => 1,
                'perPage' => $perPage,
                'lastPage' => 1,
            ];
        }

        $builder = $this->baseToursBuilder($locale)
            ->join('tour_media tm', 'tm.tour_id = t.id AND tm.type = "gallery"', 'left');

        if ($query !== '') {
            $slugKeyword = str_replace(' ', '-', mb_strtolower($query));

            $builder->groupStart()
                ->like('tt.name', $query)
                ->orLike('dltn.name', $query)
                ->orLike('dlptn.name', $query)
                ->orLike('dlgptn.name', $query)
                ->orLike('tm.alt_text', $query)
                ->orLike('tt.slug', $slugKeyword)
                ->groupEnd();
        }

        $normalizedDate = $this->normalizeSearchDate($departureDate);

        if ($normalizedDate !== null) {
            $builder->where('DATE(td.departure_date)', $normalizedDate);
        }

        $countRow = (clone $builder)
            ->select('COUNT(DISTINCT t.id) AS total', false)
            ->get()
            ->getRowArray();

        $total = (int) ($countRow['total'] ?? 0);
        $lastPage = max(1, (int) ceil(max(1, $total) / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $rows = $builder
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(dl.id) AS destination_id,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(t.base_price) AS min_price,' .
                'MIN(COALESCE(dlgptn.name, dlptn.name, dltn.name, t.tour_type)) AS continent_name,' .
                'MIN(COALESCE(dlgptn.slug, dlptn.slug, dltn.slug)) AS continent_slug'
            )
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug')
            ->orderBy($this->getSortField(), 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'tours' => $this->mapRowsToCards($rows),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
        ];
    }

    private function fetchTours(
        string $locale,
        int $limit,
        int $offset,
        ?string $tourType = null,
        array $locationFilter = [],
        bool $featuredOnly = false
    ): array
    {
        if (!$this->hasSchemaForTourCatalog()) {
            return $this->fallbackTours($offset, $limit);
        }

        $rows = $this->baseToursBuilder($locale, $tourType, $locationFilter, $featuredOnly)
            ->select(
                't.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type,' .
                'tt.name AS title, tt.slug AS slug,' .
                'MIN(dl.id) AS destination_id,' .
                'MIN(td.departure_date) AS departure_date,' .
                'MIN(t.base_price) AS min_price,' .
                'MIN(COALESCE(dlgptn.name, dlptn.name, dltn.name, t.tour_type)) AS continent_name,' .
                'MIN(COALESCE(dlgptn.slug, dlptn.slug, dltn.slug)) AS continent_slug'
            )
            ->groupBy('t.id, t.duration_days, t.duration_nights, t.thumbnail, t.is_featured, t.tour_type, tt.name, tt.slug')
            ->orderBy($this->getSortField(), 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return $this->fallbackTours($offset, $limit);
        }

        return $this->mapRowsToCards($rows);
    }

    private function baseToursBuilder(string $locale, ?string $tourType = null, array $locationFilter = [], bool $featuredOnly = false): BaseBuilder
    {
        $builder = $this->db->table('tours t')
            ->join('tour_translations tt', 'tt.tour_id = t.id AND tt.locale = ' . $this->db->escape($locale), 'inner')
            ->join('tour_departures td', 'td.tour_id = t.id AND td.status = "open"', 'left')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'left')
            ->join('locations dl', 'dl.id = tdst.location_id', 'left')
            ->join('locations dlp', 'dlp.id = dl.parent_id', 'left')
            ->join('locations dlgp', 'dlgp.id = dlp.parent_id', 'left')
            ->join('location_translations dltn', 'dltn.location_id = dl.id AND dltn.locale = ' . $this->db->escape($locale), 'left')
            ->join('location_translations dlptn', 'dlptn.location_id = dlp.id AND dlptn.locale = ' . $this->db->escape($locale), 'left')
            ->join('location_translations dlgptn', 'dlgptn.location_id = dlgp.id AND dlgptn.locale = ' . $this->db->escape($locale), 'left')
            ->where('t.status', 'published');

        if ($tourType !== null) {
            $builder->where('t.tour_type', $tourType);
        }

        if ($featuredOnly && $this->db->fieldExists('is_featured', 'tours')) {
            $builder->where('t.is_featured', 1);
        }

        $this->applyLocationFilter($builder, $locationFilter);

        return $builder;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDomesticFeaturedDestinations(string $locale, int $limit): array
    {
        $rows = $this->db->table('tours t')
            ->select('
                dl.id AS province_id,
                dl.code AS province_code,
                dltn.name AS province_name,
                dltn.slug AS province_slug,
                COUNT(DISTINCT t.id) AS tour_count,
                MIN(t.id) AS sample_tour_id
            ')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'inner')
            ->join('locations dl', 'dl.id = tdst.location_id AND dl.type = "province"', 'inner')
            ->join('location_translations dltn', 'dltn.location_id = dl.id AND dltn.locale = ' . $this->db->escape($locale), 'inner')
            ->where('t.status', 'published')
            ->where('t.tour_type', 'inbound')
            ->groupBy('dl.id, dl.code, dltn.name, dltn.slug')
            ->orderBy('tour_count', 'DESC')
            ->orderBy('dltn.name', 'ASC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        $regionService = new DomesticRegionService();
        $items = [];

        foreach ($rows as $row) {
            $provinceId = (int) ($row['province_id'] ?? 0);
            $region = $regionService->getRegionByProvinceId($locale, $provinceId);

            if ($region === null) {
                continue;
            }

            $items[] = [
                'title' => (string) ($row['province_name'] ?? ''),
                'subtitle' => (string) ($region['name'] ?? ''),
                'image' => $this->resolveFeaturedDestinationImage(
                    (string) ($row['province_slug'] ?? ''),
                    $this->getTourCoverPath((int) ($row['sample_tour_id'] ?? 0))
                ),
                'link' => localized_url('tour-trong-nuoc/' . $region['slug'] . '/' . (string) ($row['province_slug'] ?? '')),
                'count_label' => ((int) ($row['tour_count'] ?? 0)) . ' tours',
                'col' => $this->featuredDestinationColClass(count($items)),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getOutboundFeaturedDestinations(string $locale, int $limit): array
    {
        $rows = $this->db->table('tours t')
            ->select('
                continent.id AS continent_id,
                continent_tr.name AS continent_name,
                continent_tr.slug AS continent_slug,
                country.id AS country_id,
                country_tr.name AS country_name,
                country_tr.slug AS country_slug,
                COUNT(DISTINCT t.id) AS tour_count,
                MIN(t.id) AS sample_tour_id
            ')
            ->join('tour_destinations tdst', 'tdst.tour_id = t.id', 'inner')
            ->join('locations dl', 'dl.id = tdst.location_id', 'inner')
            ->join('locations country', 'country.id = CASE WHEN dl.type = "country" THEN dl.id WHEN dl.type = "province" THEN dl.parent_id ELSE 0 END', 'inner', false)
            ->join('locations continent', 'continent.id = CASE WHEN dl.type = "country" THEN dl.parent_id WHEN dl.type = "province" THEN country.parent_id ELSE 0 END', 'inner', false)
            ->join('location_translations country_tr', 'country_tr.location_id = country.id AND country_tr.locale = ' . $this->db->escape($locale), 'inner')
            ->join('location_translations continent_tr', 'continent_tr.location_id = continent.id AND continent_tr.locale = ' . $this->db->escape($locale), 'inner')
            ->where('t.status', 'published')
            ->where('t.tour_type', 'outbound')
            ->groupBy('continent.id, continent_tr.name, continent_tr.slug, country.id, country_tr.name, country_tr.slug')
            ->orderBy('continent_tr.name', 'ASC')
            ->orderBy('tour_count', 'DESC')
            ->orderBy('country_tr.name', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];

        foreach ($rows as $row) {
            $continentId = (int) ($row['continent_id'] ?? 0);
            if ($continentId <= 0) {
                continue;
            }

            if (! isset($grouped[$continentId])) {
                $grouped[$continentId] = [
                    'key' => (string) ($row['continent_slug'] ?? ('continent-' . $continentId)),
                    'label' => (string) ($row['continent_name'] ?? ''),
                    'items' => [],
                ];
            }

            if (count($grouped[$continentId]['items']) >= $limit) {
                continue;
            }

            $grouped[$continentId]['items'][] = [
                'title' => (string) ($row['country_name'] ?? ''),
                'subtitle' => (string) ($row['continent_name'] ?? ''),
                'image' => $this->resolveFeaturedDestinationImage(
                    (string) ($row['country_slug'] ?? ''),
                    $this->getTourCoverPath((int) ($row['sample_tour_id'] ?? 0))
                ),
                'link' => localized_url((string) ($row['continent_slug'] ?? '') . '/' . (string) ($row['country_slug'] ?? '')),
                'count_label' => ((int) ($row['tour_count'] ?? 0)) . ' tours',
                'col' => $this->featuredDestinationColClass(count($grouped[$continentId]['items'])),
            ];
        }

        return array_values($grouped);
    }

    private function applyLocationFilter(BaseBuilder $builder, array $locationFilter): void
    {
        $type = (string) ($locationFilter['type'] ?? '');
        $id = isset($locationFilter['id']) ? (int) $locationFilter['id'] : 0;

        if ($type === '') {
            return;
        }

        if ($type === 'region') {
            $ids = array_values(array_filter(array_map('intval', $locationFilter['ids'] ?? [])));

            if ($ids !== []) {
                $builder->whereIn('dl.id', $ids);
            }

            return;
        }

        if ($id <= 0) {
            return;
        }

        if ($type === 'continent') {
            $builder->groupStart()
                ->where('dl.id', $id)
                ->orWhere('dl.parent_id', $id)
                ->orWhere('dlp.parent_id', $id)
                ->groupEnd();

            return;
        }

        if ($type === 'country') {
            $builder->groupStart()
                ->where('dl.id', $id)
                ->orWhere('dl.parent_id', $id)
                ->groupEnd();

            return;
        }

        if ($type === 'province') {
            $builder->where('dl.id', $id);
            return;
        }
    }

    private function getSortField(): string
    {
        if ($this->db->fieldExists('created_at', 'tours')) {
            return 't.created_at';
        }

        return 't.id';
    }

    private function normalizeSearchDate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $formats = ['d/m/Y', 'Y-m-d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);

            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function hasSchemaForTourCatalog(): bool
    {
        return $this->db->tableExists('tours')
            && $this->db->tableExists('tour_translations')
            && $this->db->tableExists('tour_departures')
            && $this->db->tableExists('tour_destinations')
            && $this->db->tableExists('locations')
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
        $domesticRegionService = new DomesticRegionService();
        $locale = service('request')->getLocale() === 'en' ? 'en' : 'vi';

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            $days = (int) ($row['duration_days'] ?? 0);
            $nights = (int) ($row['duration_nights'] ?? 0);
            $price = (float) ($row['min_price'] ?? 0);
            $tourType = (string) ($row['tour_type'] ?? '');
            $destinationId = (int) ($row['destination_id'] ?? 0);

            $locationName = (string) ($row['continent_name'] ?? 'International');
            $locationLink = !empty($row['continent_slug']) ? localized_url((string) $row['continent_slug']) : '#';

            if ($tourType === 'inbound' && $destinationId > 0) {
                $region = $domesticRegionService->getRegionByProvinceId(service('request')->getLocale(), $destinationId);

                if ($region !== null) {
                    $locationName = (string) $region['name'];
                    $locationLink = localized_url('tour-trong-nuoc/' . $region['slug']);
                }
            }

            $tourSlug = (string) ($row['slug'] ?? ('tour-' . $id));
            $tourLink = $tourType === 'inbound'
                ? localized_url('tour-trong-nuoc/' . ($region['slug'] ?? 'viet-nam') . '/tour/' . $tourSlug)
                : localized_url('tour-nuoc-ngoai/' . ((string) ($row['continent_slug'] ?? '') ?: 'diem-den') . '/' . $tourSlug);

            $cards[] = [
                'id'        => $id,
                'title'     => (string) ($row['title'] ?? ('Tour #' . $id)),
                'slug'      => $tourSlug,
                'tour_type' => $tourType,
                'link'      => $tourLink,
                'image'     => $this->resolveImage($this->getTourCoverPath($id, (string) ($row['thumbnail'] ?? ''))),
                'badge'     => !empty($row['is_featured']) ? 'Hot Sale!' : null,
                'destination_id' => $destinationId,
                'continent' => $locationName,
                'continent_slug' => (string) ($row['continent_slug'] ?? ''),
                'continent_link' => $locationLink,
                'region_slug' => $tourType === 'inbound' ? (string) ($region['slug'] ?? '') : '',
                'departure' => $this->formatDate((string) ($row['departure_date'] ?? '')),
                'duration'  => [
                    'days'   => $days,
                    'nights' => $nights,
                    'label'  => $locale === 'en'
                        ? sprintf('%02d Days / %02d Nights', max(0, $days), max(0, $nights))
                        : sprintf('%02d Ngày / %02d Đêm', max(0, $days), max(0, $nights)),
                ],
                'price'     => [
                    'amount'   => $price,
                    'currency' => 'VND',
                    'label'    => number_format($price, 0, ',', '.') . 'đ',
                ],
            ];
        }

        return $cards;
    }

    /**
     * @param array<string, mixed> $tour
     * @return array<string, mixed>
     */
    private function buildRelatedLocationFilter(string $locale, array $tour): array
    {
        $tourType = (string) ($tour['tour_type'] ?? '');
        $destinationId = (int) ($tour['destination_id'] ?? 0);

        if ($tourType === 'inbound' && $destinationId > 0) {
            $region = (new DomesticRegionService())->getRegionByProvinceId($locale, $destinationId);
            $provinceIds = array_values(array_filter(array_map(
                static fn(array $province): int => (int) ($province['id'] ?? 0),
                $region['provinces'] ?? []
            )));

            if ($provinceIds !== []) {
                return [
                    'type' => 'region',
                    'ids' => $provinceIds,
                ];
            }
        }

        if ($tourType === 'outbound' && $destinationId > 0) {
            $continentId = $this->resolveContinentIdFromDestination($destinationId);

            if ($continentId > 0) {
                return [
                    'type' => 'continent',
                    'id' => $continentId,
                ];
            }
        }

        return [];
    }

    private function resolveContinentIdFromDestination(int $destinationId): int
    {
        if ($destinationId <= 0) {
            return 0;
        }

        $row = $this->db->table('locations dl')
            ->select(
                'CASE ' .
                'WHEN dl.type = "continent" THEN dl.id ' .
                'WHEN dl.type = "country" THEN dlp.id ' .
                'WHEN dl.type = "province" THEN dlgp.id ' .
                'ELSE 0 END AS continent_id',
                false
            )
            ->join('locations dlp', 'dlp.id = dl.parent_id', 'left')
            ->join('locations dlgp', 'dlgp.id = dlp.parent_id', 'left')
            ->where('dl.id', $destinationId)
            ->limit(1)
            ->get()
            ->getRowArray();

        return (int) ($row['continent_id'] ?? 0);
    }

    private function resolveImage(string $thumbnail): string
    {
        if ($thumbnail === '') {
            return base_url('assets/images/avt-tour-01.jpg');
        }

        if (str_starts_with($thumbnail, 'http://') || str_starts_with($thumbnail, 'https://')) {
            return $thumbnail;
        }

        if (str_starts_with($thumbnail, 'assets/') || str_starts_with($thumbnail, 'uploads/')) {
            return base_url($thumbnail);
        }

        return base_url('assets/images/' . ltrim($thumbnail, '/'));
    }

    private function resolveFeaturedDestinationImage(string $slug, string $fallback = ''): string
    {
        $map = FeaturedDestinationImageMap::getAll();
        $custom = trim((string) ($map[$slug] ?? ''));

        if ($custom !== '') {
            return $this->resolveImage($custom);
        }

        return $this->resolveImage($fallback);
    }

    private function getTourCoverPath(int $tourId, string $fallback = ''): string
    {
        if ($tourId <= 0 || !$this->db->tableExists('tour_media')) {
            return $fallback;
        }

        $row = $this->db->table('tour_media')
            ->select('file_path')
            ->where('tour_id', $tourId)
            ->where('type', 'cover')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return (string) ($row['file_path'] ?? $fallback);
    }

    private function hydrateTourDetail(array $tour, string $locale): array
    {
        $tourId = (int) $tour['id'];
        $tourRow = $this->db->table('tours')->where('id', $tourId)->get()->getRowArray() ?? [];
        $translation = $this->db->table('tour_translations')
            ->where('tour_id', $tourId)
            ->where('locale', $locale)
            ->get()
            ->getRowArray() ?? [];

        $fallbackTranslation = [];
        if ($locale !== 'vi') {
            $fallbackTranslation = $this->db->table('tour_translations')
                ->where('tour_id', $tourId)
                ->where('locale', 'vi')
                ->get()
                ->getRowArray() ?? [];
        }

        $detail = $tour;
        $detail['meta_title'] = (string) ($translation['meta_title'] ?? $fallbackTranslation['meta_title'] ?? '');
        $detail['meta_description'] = (string) ($translation['meta_description'] ?? $fallbackTranslation['meta_description'] ?? '');
        $detail['short_description'] = (string) ($translation['short_description'] ?? $fallbackTranslation['short_description'] ?? '');
        $detail['overview'] = (string) ($translation['overview'] ?? $fallbackTranslation['overview'] ?? '');
        $detail['description'] = (string) ($translation['description'] ?? $fallbackTranslation['description'] ?? '');
        $detail['max_travelers'] = (int) ($tourRow['max_travelers'] ?? 15);

        $basePrice = (float) ($tourRow['sale_price'] ?? 0) ?: (float) ($tourRow['base_price'] ?? 0);
        if ((float) ($detail['price']['amount'] ?? 0) <= 0 && $basePrice > 0) {
            $detail['price'] = [
                'amount' => $basePrice,
                'currency' => (string) ($tourRow['currency'] ?? 'VND'),
                'label' => number_format($basePrice, 0, ',', '.') . ' VND',
            ];
        }

        $detail['departures'] = $this->getTourDepartures($tourId);
        $detail['media'] = $this->getTourMedia($tourId);
        $detail['itinerary_days'] = $this->getTourItineraryDays($tourId, $locale);
        $detail['faqs'] = $this->getTourFaqs($tourId, $locale);
        $detail['review_summary'] = $this->getTourReviewSummary($tourId);
        $detail['reviews'] = $this->getTourReviews($tourId);

        $cover = $this->firstMediaByTypes($detail['media'], ['banner', 'cover']);
        if ($cover !== null) {
            $detail['image'] = $cover['url'];
        }

        return $detail;
    }

    private function getTourDepartures(int $tourId): array
    {
        if (!$this->db->tableExists('tour_departures')) {
            return [];
        }

        $rows = $this->db->table('tour_departures')
            ->where('tour_id', $tourId)
            ->where('status', 'open')
            ->orderBy('departure_date', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(function (array $row): array {
            $price = (float) ($row['price'] ?? 0);

            return [
                'date' => (string) ($row['departure_date'] ?? ''),
                'date_label' => $this->formatDate((string) ($row['departure_date'] ?? '')),
                'available_slots' => (int) ($row['available_slots'] ?? 0),
                'status' => (string) ($row['status'] ?? 'open'),
                'price' => $price,
                'price_label' => number_format($price, 0, ',', '.') . ' VND',
            ];
        }, $rows);
    }

    private function getTourMedia(int $tourId): array
    {
        if (!$this->db->tableExists('tour_media')) {
            return [];
        }

        $rows = $this->db->table('tour_media')
            ->where('tour_id', $tourId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(fn(array $row): array => [
            'type' => (string) ($row['type'] ?? 'gallery'),
            'file_path' => (string) ($row['file_path'] ?? ''),
            'url' => $this->resolveImage((string) ($row['file_path'] ?? '')),
            'alt_text' => (string) ($row['alt_text'] ?? ''),
        ], $rows);
    }

    private function getTourItineraryDays(int $tourId, string $locale): array
    {
        if (!$this->db->tableExists('tour_itinerary_days') || !$this->db->tableExists('tour_itinerary_day_translations')) {
            return [];
        }

        return $this->db->table('tour_itinerary_days tid')
            ->select('tid.day_number, tid.meals, tid.hotel_name, tid.transport_summary, COALESCE(tidt.title, tidt_vi.title) AS title, COALESCE(tidt.description, tidt_vi.description) AS description')
            ->join('tour_itinerary_day_translations tidt', 'tidt.itinerary_day_id = tid.id AND tidt.locale = ' . $this->db->escape($locale), 'left')
            ->join('tour_itinerary_day_translations tidt_vi', 'tidt_vi.itinerary_day_id = tid.id AND tidt_vi.locale = "vi"', 'left')
            ->where('tid.tour_id', $tourId)
            ->orderBy('tid.sort_order', 'ASC')
            ->orderBy('tid.day_number', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function getTourFaqs(int $tourId, string $locale): array
    {
        if (!$this->db->tableExists('tour_faqs') || !$this->db->tableExists('tour_faq_translations')) {
            return [];
        }

        return $this->db->table('tour_faqs tf')
            ->select('COALESCE(tft.question, tft_vi.question) AS question, COALESCE(tft.answer, tft_vi.answer) AS answer')
            ->join('tour_faq_translations tft', 'tft.faq_id = tf.id AND tft.locale = ' . $this->db->escape($locale), 'left')
            ->join('tour_faq_translations tft_vi', 'tft_vi.faq_id = tf.id AND tft_vi.locale = "vi"', 'left')
            ->where('tf.tour_id', $tourId)
            ->where('tf.is_active', 1)
            ->orderBy('tf.sort_order', 'ASC')
            ->orderBy('tf.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function firstMediaByTypes(array $media, array $types): ?array
    {
        foreach ($media as $item) {
            if (in_array($item['type'], $types, true)) {
                return $item;
            }
        }

        return null;
    }

    private function getTourReviewSummary(int $tourId): array
    {
        $empty = [
            'count' => 0,
            'overall' => 0.0,
            'destination' => 0.0,
            'transport' => 0.0,
            'value' => 0.0,
        ];

        if (!$this->db->tableExists('tour_reviews')) {
            return $empty;
        }

        $row = $this->db->table('tour_reviews')
            ->select('COUNT(*) AS review_count, AVG(rating_overall) AS avg_overall, AVG(rating_destination) AS avg_destination, AVG(rating_transport) AS avg_transport, AVG(rating_value) AS avg_value')
            ->where('tour_id', $tourId)
            ->where('status', 'approved')
            ->get()
            ->getRowArray();

        if ($row === null || (int) ($row['review_count'] ?? 0) <= 0) {
            return $empty;
        }

        return [
            'count' => (int) ($row['review_count'] ?? 0),
            'overall' => round((float) ($row['avg_overall'] ?? 0), 1),
            'destination' => round((float) ($row['avg_destination'] ?? 0), 1),
            'transport' => round((float) ($row['avg_transport'] ?? 0), 1),
            'value' => round((float) ($row['avg_value'] ?? 0), 1),
        ];
    }

    private function getTourReviews(int $tourId, int $limit = 10): array
    {
        if (!$this->db->tableExists('tour_reviews')) {
            return [];
        }

        return array_map(function (array $row): array {
            $name = (string) ($row['reviewer_name'] ?? 'Guest');
            $createdAt = (string) ($row['created_at'] ?? '');
            $timestamp = $createdAt !== '' ? strtotime($createdAt) : false;

            return [
                'reviewer_name' => $name,
                'reviewer_email' => (string) ($row['reviewer_email'] ?? ''),
                'content' => (string) ($row['content'] ?? ''),
                'created_at' => $createdAt,
                'created_label' => $timestamp ? date('d/m/Y', $timestamp) : '',
                'initials' => $this->getInitials($name),
                'rating_overall' => round((float) ($row['rating_overall'] ?? 0), 1),
                'rating_destination' => round((float) ($row['rating_destination'] ?? 0), 1),
                'rating_transport' => round((float) ($row['rating_transport'] ?? 0), 1),
                'rating_value' => round((float) ($row['rating_value'] ?? 0), 1),
            ];
        }, $this->db->table('tour_reviews')
            ->where('tour_id', $tourId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray());
    }

    private function getInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $letters = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $letters[] = strtoupper(substr($part, 0, 1));

            if (count($letters) === 2) {
                break;
            }
        }

        return implode('', $letters) ?: 'G';
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

    private function featuredDestinationColClass(int $index): string
    {
        $patterns = [
            'col-lg-5 col-md-7',
            'col-lg-3 col-md-5',
            'col-lg-4 col-md-6',
            'col-lg-4 col-md-6',
            'col-lg-3 col-md-5',
            'col-lg-5 col-md-7',
        ];

        return $patterns[$index % count($patterns)];
    }
}


