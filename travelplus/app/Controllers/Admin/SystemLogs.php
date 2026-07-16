<?php

namespace App\Controllers\Admin;

use App\Services\SystemLogService;

class SystemLogs extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $report = (new SystemLogService())->search(
            (int) $this->request->getGet('days'),
            (string) $this->request->getGet('level'),
            (string) $this->request->getGet('q')
        );

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody(view('admin/system-logs/index', ['report' => $report]));
    }
}
