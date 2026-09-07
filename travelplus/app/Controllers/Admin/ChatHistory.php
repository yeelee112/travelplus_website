<?php

namespace App\Controllers\Admin;

use App\Services\ChatHistoryService;

class ChatHistory extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }
        $report = (new ChatHistoryService())->search(
            (string) $this->request->getGet('date'),
            (string) $this->request->getGet('q'),
            (string) $this->request->getGet('conversation'),
            (int) $this->request->getGet('page')
        );

        return $this->response->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('admin/chat-history/index', ['report' => $report]));
    }
}
