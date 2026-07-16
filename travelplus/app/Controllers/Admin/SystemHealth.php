<?php

namespace App\Controllers\Admin;

use App\Services\SystemHealthService;

class SystemHealth extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $report = (new SystemHealthService())->inspect();

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody(view('admin/system-health/index', ['report' => $report]));
    }
}
