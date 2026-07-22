<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RequestInterface;

class SearchAnalyticsService
{
    private const VISITOR_TOKEN_KEY = 'analytics_visitor_token';
    private const VISIT_TOKEN_KEY = 'analytics_visit_token';
    private const VISIT_ID_KEY = 'analytics_visit_id';

    private BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function track(
        RequestInterface $request,
        string $query,
        string $departureFrom,
        string $departureTo,
        string $tourType,
        bool $promotionOnly,
        int $resultsTotal,
        ?array $authUser = null
    ): void {
        if (! (new CookieConsentService())->allowsAnalytics($request)) {
            return;
        }

        $schema = new DatabaseSchemaCacheService($this->db);
        if (! $schema->tableExists('analytics_search_queries')) {
            return;
        }

        if ($query === '' && $departureFrom === '' && $departureTo === '' && $tourType === '' && ! $promotionOnly) {
            return;
        }

        $session = session();
        $visitorToken = (string) $session->get(self::VISITOR_TOKEN_KEY);
        $visitToken = (string) $session->get(self::VISIT_TOKEN_KEY);
        $visitId = (int) $session->get(self::VISIT_ID_KEY);
        if ($visitId <= 0) {
            $visitId = $this->resolveVisitId($visitToken, $schema) ?? 0;
        }
        $userId = (int) (($authUser['id'] ?? 0) ?: 0) ?: null;
        $now = date('Y-m-d H:i:s');

        $this->db->table('analytics_search_queries')->insert([
            'visit_id' => $visitId > 0 ? $visitId : null,
            'visitor_token' => $visitorToken !== '' ? $visitorToken : null,
            'user_id' => $userId,
            'query_term' => $query !== '' ? $query : null,
            'departure_from' => $departureFrom !== '' ? $departureFrom : null,
            'departure_to' => $departureTo !== '' ? $departureTo : null,
            'tour_type' => $tourType !== '' ? $tourType : null,
            'promotion_only' => $promotionOnly ? 1 : 0,
            'results_total' => max(0, $resultsTotal),
            'path' => '/' . trim((string) $request->getUri()->getPath(), '/'),
            'locale' => (string) ($request->getLocale() ?: 'vi'),
            'searched_at' => $now,
            'created_at' => $now,
        ]);
    }

    private function resolveVisitId(string $visitToken, DatabaseSchemaCacheService $schema): ?int
    {
        if ($visitToken === '' || ! $schema->tableExists('analytics_visits')) {
            return null;
        }

        $row = $this->db->table('analytics_visits')
            ->select('id')
            ->where('visit_token', $visitToken)
            ->limit(1)
            ->get()
            ->getRowArray();

        $visitId = (int) ($row['id'] ?? 0);

        return $visitId > 0 ? $visitId : null;
    }
}
