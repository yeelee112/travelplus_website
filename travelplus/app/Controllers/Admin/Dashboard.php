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

        $stats = [
            'bookings_total' => $db->tableExists('bookings') ? $bookingModel->countAllResults() : 0,
            'bookings_pending_transfer' => $db->tableExists('bookings')
                ? $bookingModel->where('payment_status', 'pending_transfer')->countAllResults()
                : 0,
            'reviews_pending' => $db->tableExists('tour_reviews')
                ? $db->table('tour_reviews')->where('status', 'pending')->countAllResults()
                : 0,
            'tours_total' => $db->tableExists('tours') ? $db->table('tours')->countAllResults() : 0,
            'blogs_total' => $db->tableExists('blogs') ? $db->table('blogs')->countAllResults() : 0,
            'tour_views_total' => ($db->tableExists('tours') && $db->fieldExists('view_count', 'tours'))
                ? (int) (($db->table('tours')->selectSum('view_count')->get()->getRowArray()['view_count'] ?? 0))
                : 0,
            'blog_views_total' => ($db->tableExists('blogs') && $db->fieldExists('view_count', 'blogs'))
                ? (int) (($db->table('blogs')->selectSum('view_count')->get()->getRowArray()['view_count'] ?? 0))
                : 0,
            'analytics_pageviews_30d' => (int) ($analyticsSummary['pageviews'] ?? 0),
            'analytics_visits_30d' => (int) ($analyticsSummary['visits'] ?? 0),
        ];

        $recentBookings = $db->tableExists('bookings')
            ? $bookingModel->orderBy('created_at', 'DESC')->findAll(8)
            : [];

        $topTours = [];
        if ($db->tableExists('tours')) {
            $topTours = $db->table('tours t')
                ->select('t.id, t.status, ' . ($db->fieldExists('view_count', 'tours') ? 't.view_count' : '0 AS view_count') . ', COALESCE(tt_vi.name, tt_en.name, CONCAT("Tour #", t.id)) AS name', false)
                ->join('tour_translations tt_vi', 'tt_vi.tour_id = t.id AND tt_vi.locale = "vi"', 'left')
                ->join('tour_translations tt_en', 'tt_en.tour_id = t.id AND tt_en.locale = "en"', 'left')
                ->orderBy($db->fieldExists('view_count', 'tours') ? 't.view_count' : 't.id', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        }

        $topBlogs = [];
        if ($db->tableExists('blogs')) {
            $topBlogs = $db->table('blogs b')
                ->select('b.id, b.status, ' . ($db->fieldExists('view_count', 'blogs') ? 'b.view_count' : '0 AS view_count') . ', COALESCE(bt_vi.title, bt_en.title, CONCAT("Blog #", b.id)) AS title', false)
                ->join('blog_translations bt_vi', 'bt_vi.blog_id = b.id AND bt_vi.locale = "vi"', 'left')
                ->join('blog_translations bt_en', 'bt_en.blog_id = b.id AND bt_en.locale = "en"', 'left')
                ->orderBy($db->fieldExists('view_count', 'blogs') ? 'b.view_count' : 'b.id', 'DESC')
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
