<?php

namespace App\Controllers\Admin;

use App\Services\AnalyticsReportService;
use Throwable;

class Analytics extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $days = max(1, min(90, (int) ($this->request->getGet('days') ?: 30)));
        $report = new AnalyticsReportService();
        $cacheKey = 'admin_analytics_report_' . sha1(db_connect()->getDatabase() . ':' . $days);
        $reportData = null;

        try {
            $cached = cache()->get($cacheKey);
            if (is_array($cached)) {
                $reportData = $cached;
            }
        } catch (Throwable) {
        }

        if ($reportData === null) {
            $reportData = [
                'isReady' => $report->isReady(),
                'isSearchReady' => $report->isSearchReady(),
                'summary' => $report->getSummary($days),
                'topPages' => $report->getTopPages($days),
                'topReferrers' => $report->getTopReferrers($days),
                'recentJourneys' => $report->getRecentJourneys(min($days, 14)),
                'topSearchTerms' => $report->getTopSearchTerms($days),
                'recentSearches' => $report->getRecentSearches(min($days, 14)),
            ];

            try {
                cache()->save($cacheKey, $reportData, 30);
            } catch (Throwable) {
            }
        }

        return view('admin/analytics/index', ['days' => $days] + $reportData);
    }
}
