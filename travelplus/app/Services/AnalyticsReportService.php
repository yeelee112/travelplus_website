<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class AnalyticsReportService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function isReady(): bool
    {
        return $this->db->tableExists('analytics_visits')
            && $this->db->tableExists('analytics_page_views');
    }

    public function isSearchReady(): bool
    {
        return $this->db->tableExists('analytics_search_queries');
    }

    /**
     * @return array<string, int|float>
     */
    public function getSummary(int $days = 30): array
    {
        if (! $this->isReady()) {
            return [
                'pageviews' => 0,
                'visits' => 0,
                'visitors' => 0,
                'avg_pages_per_visit' => 0,
            ];
        }

        $since = $this->sinceDate($days);
        $pageviews = (int) $this->db->table('analytics_page_views')
            ->where('viewed_at >=', $since)
            ->countAllResults();

        $visits = (int) $this->db->table('analytics_visits')
            ->where('started_at >=', $since)
            ->countAllResults();

        $visitorRow = $this->db->table('analytics_visits')
            ->select('COUNT(DISTINCT visitor_token) AS total', false)
            ->where('started_at >=', $since)
            ->get()
            ->getRowArray();

        $visitors = (int) ($visitorRow['total'] ?? 0);
        $avgPages = $visits > 0 ? round($pageviews / $visits, 1) : 0;

        return [
            'pageviews' => $pageviews,
            'visits' => $visits,
            'visitors' => $visitors,
            'avg_pages_per_visit' => $avgPages,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopPages(int $days = 30, int $limit = 15): array
    {
        if (! $this->isReady()) {
            return [];
        }

        $since = $this->sinceDate($days);

        return $this->db->table('analytics_page_views')
            ->select('path, page_type, COUNT(*) AS views, COUNT(DISTINCT visitor_token) AS visitors, MAX(viewed_at) AS last_viewed_at', false)
            ->where('viewed_at >=', $since)
            ->groupBy('path, page_type')
            ->orderBy('views', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopReferrers(int $days = 30, int $limit = 10): array
    {
        if (! $this->isReady()) {
            return [];
        }

        $since = $this->sinceDate($days);

        return $this->db->table('analytics_visits')
            ->select('referrer, COUNT(*) AS visits', false)
            ->where('started_at >=', $since)
            ->where('referrer IS NOT NULL', null, false)
            ->where('referrer !=', '')
            ->groupBy('referrer')
            ->orderBy('visits', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentJourneys(int $days = 7, int $limit = 10, int $stepsPerVisit = 8): array
    {
        if (! $this->isReady()) {
            return [];
        }

        $since = $this->sinceDate($days);
        $visits = $this->db->table('analytics_visits')
            ->select('id, visitor_token, user_id, landing_path, last_path, referrer, pageviews, started_at, last_seen_at')
            ->where('started_at >=', $since)
            ->orderBy('last_seen_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        if ($visits === []) {
            return [];
        }

        $visitIds = array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $visits);
        $pageViews = $this->db->table('analytics_page_views')
            ->select('visit_id, path, page_type, viewed_at')
            ->whereIn('visit_id', $visitIds)
            ->orderBy('visit_id', 'ASC')
            ->orderBy('viewed_at', 'ASC')
            ->get()
            ->getResultArray();

        $searchesByVisit = [];
        if ($this->isSearchReady()) {
            $searchRows = $this->db->table('analytics_search_queries')
                ->select('visit_id, query_term, departure_from, departure_to, tour_type, promotion_only, results_total, searched_at')
                ->whereIn('visit_id', $visitIds)
                ->orderBy('visit_id', 'ASC')
                ->orderBy('searched_at', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($searchRows as $searchRow) {
                $visitId = (int) ($searchRow['visit_id'] ?? 0);
                if ($visitId <= 0) {
                    continue;
                }
                $searchesByVisit[$visitId][] = $searchRow;
            }
        }

        $pagesByVisit = [];
        foreach ($pageViews as $view) {
            $visitId = (int) ($view['visit_id'] ?? 0);
            if ($visitId <= 0) {
                continue;
            }
            $pagesByVisit[$visitId][] = $view;
        }

        foreach ($visits as &$visit) {
            $visitId = (int) ($visit['id'] ?? 0);
            $pages = $pagesByVisit[$visitId] ?? [];
            $searches = $searchesByVisit[$visitId] ?? [];
            $pages = $this->attachSearchContextToPages($pages, $searches);
            if (count($pages) > $stepsPerVisit) {
                $pages = array_slice($pages, -1 * $stepsPerVisit);
            }
            $visit['pages'] = $pages;
            $visit['searches'] = $searches;
        }
        unset($visit);

        return $visits;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopSearchTerms(int $days = 30, int $limit = 15): array
    {
        if (! $this->isSearchReady()) {
            return [];
        }

        $since = $this->sinceDate($days);

        return $this->db->table('analytics_search_queries')
            ->select('query_term, tour_type, promotion_only, COUNT(*) AS searches, AVG(results_total) AS avg_results, MAX(searched_at) AS last_searched_at', false)
            ->where('searched_at >=', $since)
            ->where('query_term IS NOT NULL', null, false)
            ->where('query_term !=', '')
            ->groupBy('query_term, tour_type, promotion_only')
            ->orderBy('searches', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentSearches(int $days = 7, int $limit = 15): array
    {
        if (! $this->isSearchReady()) {
            return [];
        }

        $since = $this->sinceDate($days);

        return $this->db->table('analytics_search_queries')
            ->select('query_term, departure_from, departure_to, tour_type, promotion_only, results_total, path, searched_at')
            ->where('searched_at >=', $since)
            ->orderBy('searched_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    private function sinceDate(int $days): string
    {
        $days = max(1, $days);
        return date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
    }

    /**
     * @param array<int, array<string, mixed>> $pages
     * @param array<int, array<string, mixed>> $searches
     * @return array<int, array<string, mixed>>
     */
    private function attachSearchContextToPages(array $pages, array $searches): array
    {
        if ($pages === [] || $searches === []) {
            return $pages;
        }

        $searchIndex = 0;
        $searchCount = count($searches);

        foreach ($pages as &$page) {
            $page['search_label'] = '';

            if (($page['page_type'] ?? '') !== 'search') {
                continue;
            }

            $pageTs = strtotime((string) ($page['viewed_at'] ?? ''));
            if ($pageTs === false) {
                continue;
            }

            while (
                $searchIndex + 1 < $searchCount
                && strtotime((string) ($searches[$searchIndex + 1]['searched_at'] ?? '')) !== false
                && strtotime((string) ($searches[$searchIndex + 1]['searched_at'] ?? '')) <= $pageTs
            ) {
                $searchIndex++;
            }

            $matched = $searches[$searchIndex] ?? null;
            if (! is_array($matched)) {
                continue;
            }

            $parts = [];
            $queryTerm = trim((string) ($matched['query_term'] ?? ''));
            if ($queryTerm !== '') {
                $parts[] = $queryTerm;
            }
            if (! empty($matched['promotion_only'])) {
                $parts[] = 'promo';
            }

            $page['search_label'] = implode(' · ', $parts);
            $page['results_total'] = (int) ($matched['results_total'] ?? 0);
        }
        unset($page);

        return $pages;
    }
}
