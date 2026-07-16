<?php

namespace App\Controllers\Admin;

use App\Models\BookingModel;
use App\Services\AnalyticsReportService;

class Dashboard extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $db = db_connect();
        $bookingModel = new BookingModel();
        $analyticsReport = new AnalyticsReportService();
        $analyticsSummary = $analyticsReport->getSummary(30);

        $hasBookings = $db->tableExists('bookings');
        $hasReviews = $db->tableExists('tour_reviews');
        $hasTours = $db->tableExists('tours');
        $hasBlogs = $db->tableExists('blogs');
        $toursHaveViewCount = $hasTours && $db->fieldExists('view_count', 'tours');
        $blogsHaveViewCount = $hasBlogs && $db->fieldExists('view_count', 'blogs');

        $bookingSummary = $hasBookings
            ? ($db->table('bookings')
                ->select("COUNT(*) AS total, COALESCE(SUM(CASE WHEN payment_status = 'pending_transfer' THEN 1 ELSE 0 END), 0) AS pending_transfer", false)
                ->get()
                ->getRowArray() ?? [])
            : [];
        $tourSummary = $hasTours
            ? ($db->table('tours')
                ->select('COUNT(*) AS total, ' . ($toursHaveViewCount ? 'COALESCE(SUM(view_count), 0)' : '0') . ' AS views', false)
                ->get()
                ->getRowArray() ?? [])
            : [];
        $blogSummary = $hasBlogs
            ? ($db->table('blogs')
                ->select('COUNT(*) AS total, ' . ($blogsHaveViewCount ? 'COALESCE(SUM(view_count), 0)' : '0') . ' AS views', false)
                ->get()
                ->getRowArray() ?? [])
            : [];

        $stats = [
            'bookings_total' => (int) ($bookingSummary['total'] ?? 0),
            'bookings_pending_transfer' => (int) ($bookingSummary['pending_transfer'] ?? 0),
            'reviews_pending' => $hasReviews
                ? $db->table('tour_reviews')->where('status', 'pending')->countAllResults()
                : 0,
            'tours_total' => (int) ($tourSummary['total'] ?? 0),
            'blogs_total' => (int) ($blogSummary['total'] ?? 0),
            'tour_views_total' => (int) ($tourSummary['views'] ?? 0),
            'blog_views_total' => (int) ($blogSummary['views'] ?? 0),
            'analytics_pageviews_30d' => (int) ($analyticsSummary['pageviews'] ?? 0),
            'analytics_visits_30d' => (int) ($analyticsSummary['visits'] ?? 0),
        ];

        $recentBookings = $hasBookings
            ? $bookingModel->orderBy('created_at', 'DESC')->findAll(8)
            : [];

        $topTours = [];
        if ($hasTours) {
            $topTours = $db->table('tours t')
                ->select('t.id, t.status, ' . ($toursHaveViewCount ? 't.view_count' : '0 AS view_count') . ', COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", t.id)) AS name', false)
                ->join('tour_translations tt_vi', 'tt_vi.tour_id = t.id AND tt_vi.locale = "vi"', 'left')
                ->join('tour_translations tt_en', 'tt_en.tour_id = t.id AND tt_en.locale = "en"', 'left')
                ->orderBy($toursHaveViewCount ? 't.view_count' : 't.id', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        }

        $topBlogs = [];
        if ($hasBlogs) {
            $topBlogs = $db->table('blogs b')
                ->select('b.id, b.status, ' . ($blogsHaveViewCount ? 'b.view_count' : '0 AS view_count') . ', COALESCE(bt_vi.title, bt_en.title, CONCAT("Blog #", b.id)) AS title', false)
                ->join('blog_translations bt_vi', 'bt_vi.blog_id = b.id AND bt_vi.locale = "vi"', 'left')
                ->join('blog_translations bt_en', 'bt_en.blog_id = b.id AND bt_en.locale = "en"', 'left')
                ->orderBy($blogsHaveViewCount ? 'b.view_count' : 'b.id', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        }

        return view('admin/dashboard/index', [
            'stats' => $stats,
            'analyticsReady' => $analyticsReport->isReady(),
            'recentBookings' => $recentBookings,
            'topTours' => $topTours,
            'topBlogs' => $topBlogs,
        ]);
    }
}
