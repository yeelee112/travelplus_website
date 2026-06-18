<?php

namespace App\Controllers\Admin;

use App\Services\AnalyticsReportService;

class Analytics extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $days = max(1, min(90, (int) ($this->request->getGet('days') ?: 30)));
        $report = new AnalyticsReportService();

        return view('admin/analytics/index', [
            'days' => $days,
            'isReady' => $report->isReady(),
            'isSearchReady' => $report->isSearchReady(),
            'summary' => $report->getSummary($days),
            'topPages' => $report->getTopPages($days),
            'topReferrers' => $report->getTopReferrers($days),
            'recentJourneys' => $report->getRecentJourneys(min($days, 14)),
            'topSearchTerms' => $report->getTopSearchTerms($days),
            'recentSearches' => $report->getRecentSearches(min($days, 14)),
        ]);
    }
}
