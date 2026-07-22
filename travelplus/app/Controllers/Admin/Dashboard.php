<?php

namespace App\Controllers\Admin;

use App\Models\BookingModel;
use App\Services\AnalyticsReportService;
use App\Services\DatabaseSchemaCacheService;
use CodeIgniter\Database\BaseConnection;
use Throwable;

class Dashboard extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();
        $capabilities = $this->capabilities(new DatabaseSchemaCacheService($db));
        $metrics = $this->dashboardMetrics($db, $capabilities, new AnalyticsReportService());
        $recentBookings = $capabilities['bookings']
            ? (new BookingModel())->orderBy('created_at', 'DESC')->findAll(8)
            : [];

        return view('admin/dashboard/index', [
            'stats' => $metrics['stats'] ?? [],
            'analyticsReady' => ! empty($metrics['analyticsReady']),
            'recentBookings' => $recentBookings,
            'topTours' => $metrics['topTours'] ?? [],
            'topBlogs' => $metrics['topBlogs'] ?? [],
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function capabilities(DatabaseSchemaCacheService $schema): array
    {
        $hasTours = $schema->tableExists('tours');
        $hasBlogs = $schema->tableExists('blogs');

        return [
            'bookings' => $schema->tableExists('bookings'),
            'reviews' => $schema->tableExists('tour_reviews'),
            'tours' => $hasTours,
            'blogs' => $hasBlogs,
            'tour_views' => $hasTours && $schema->fieldExists('view_count', 'tours'),
            'blog_views' => $hasBlogs && $schema->fieldExists('view_count', 'blogs'),
        ];
    }

    /**
     * @param array<string, bool> $capabilities
     * @return array<string, mixed>
     */
    private function dashboardMetrics(
        BaseConnection $db,
        array $capabilities,
        AnalyticsReportService $analyticsReport
    ): array {
        $cacheKey = 'admin_dashboard_metrics_' . sha1((string) $db->getDatabase());

        try {
            $cached = cache()->get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        } catch (Throwable) {
        }

        $analyticsReady = $analyticsReport->isReady();
        $analyticsSummary = $analyticsReady ? $analyticsReport->getSummary(30) : [];
        $bookingSummary = $capabilities['bookings']
            ? ($db->table('bookings')
                ->select("COUNT(*) AS total, COALESCE(SUM(CASE WHEN payment_status = 'pending_transfer' THEN 1 ELSE 0 END), 0) AS pending_transfer", false)
                ->get()
                ->getRowArray() ?? [])
            : [];
        $tourSummary = $capabilities['tours']
            ? ($db->table('tours')
                ->select('COUNT(*) AS total, ' . ($capabilities['tour_views'] ? 'COALESCE(SUM(view_count), 0)' : '0') . ' AS views', false)
                ->get()
                ->getRowArray() ?? [])
            : [];
        $blogSummary = $capabilities['blogs']
            ? ($db->table('blogs')
                ->select('COUNT(*) AS total, ' . ($capabilities['blog_views'] ? 'COALESCE(SUM(view_count), 0)' : '0') . ' AS views', false)
                ->get()
                ->getRowArray() ?? [])
            : [];

        $metrics = [
            'stats' => [
                'bookings_total' => (int) ($bookingSummary['total'] ?? 0),
                'bookings_pending_transfer' => (int) ($bookingSummary['pending_transfer'] ?? 0),
                'reviews_pending' => $capabilities['reviews']
                    ? $db->table('tour_reviews')->where('status', 'pending')->countAllResults()
                    : 0,
                'tours_total' => (int) ($tourSummary['total'] ?? 0),
                'blogs_total' => (int) ($blogSummary['total'] ?? 0),
                'tour_views_total' => (int) ($tourSummary['views'] ?? 0),
                'blog_views_total' => (int) ($blogSummary['views'] ?? 0),
                'analytics_pageviews_30d' => (int) ($analyticsSummary['pageviews'] ?? 0),
                'analytics_visits_30d' => (int) ($analyticsSummary['visits'] ?? 0),
            ],
            'analyticsReady' => $analyticsReady,
            'topTours' => $this->topTours($db, $capabilities),
            'topBlogs' => $this->topBlogs($db, $capabilities),
        ];

        try {
            cache()->save($cacheKey, $metrics, 30);
        } catch (Throwable) {
        }

        return $metrics;
    }

    /**
     * @param array<string, bool> $capabilities
     * @return array<int, array<string, mixed>>
     */
    private function topTours(BaseConnection $db, array $capabilities): array
    {
        if (! $capabilities['tours']) {
            return [];
        }

        return $db->table('tours t')
            ->select('t.id, t.status, ' . ($capabilities['tour_views'] ? 't.view_count' : '0 AS view_count') . ', COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", t.id)) AS name', false)
            ->join('tour_translations tt_vi', 'tt_vi.tour_id = t.id AND tt_vi.locale = "vi"', 'left')
            ->join('tour_translations tt_en', 'tt_en.tour_id = t.id AND tt_en.locale = "en"', 'left')
            ->orderBy($capabilities['tour_views'] ? 't.view_count' : 't.id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<string, bool> $capabilities
     * @return array<int, array<string, mixed>>
     */
    private function topBlogs(BaseConnection $db, array $capabilities): array
    {
        if (! $capabilities['blogs']) {
            return [];
        }

        return $db->table('blogs b')
            ->select('b.id, b.status, ' . ($capabilities['blog_views'] ? 'b.view_count' : '0 AS view_count') . ', COALESCE(bt_vi.title, bt_en.title, CONCAT("Blog #", b.id)) AS title', false)
            ->join('blog_translations bt_vi', 'bt_vi.blog_id = b.id AND bt_vi.locale = "vi"', 'left')
            ->join('blog_translations bt_en', 'bt_en.blog_id = b.id AND bt_en.locale = "en"', 'left')
            ->orderBy($capabilities['blog_views'] ? 'b.view_count' : 'b.id', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }
}
