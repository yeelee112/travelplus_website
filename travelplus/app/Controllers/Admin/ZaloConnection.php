<?php

namespace App\Controllers\Admin;

use App\Services\ZaloOaTokenService;

final class ZaloConnection extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('admin/zalo/index', [
            'status' => (new ZaloOaTokenService())->status(),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function connect()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $service = new ZaloOaTokenService();
        $status = $service->status();

        if (! $status['secret_configured']) {
            return redirect()->to(site_url('admin/zalo'))
                ->with('error', 'Chưa cấu hình zalo.appSecret trong file .env.');
        }

        if (! $status['storage_ready']) {
            return redirect()->to(site_url('admin/zalo'))
                ->with('error', 'Chưa có bảng zalo_oa_connections. Hãy import file SQL được hướng dẫn trên trang này.');
        }

        $state = bin2hex(random_bytes(32));
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
        session()->set([
            'zalo_oa_oauth_state' => $state,
            'zalo_oa_oauth_code_verifier' => $codeVerifier,
            'zalo_oa_oauth_expires_at' => time() + 600,
        ]);

        return redirect()->to($service->authorizationUrl($state, $codeChallenge));
    }
}
