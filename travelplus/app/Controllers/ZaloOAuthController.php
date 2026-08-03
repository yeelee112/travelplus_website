<?php

namespace App\Controllers;

use App\Services\AdminAccessService;
use App\Services\ZaloOaTokenService;

final class ZaloOAuthController extends BaseController
{
    public function callback()
    {
        $redirect = redirect()->to(site_url('admin/zalo'));
        $authUser = session()->get('auth_user');

        if (! is_array($authUser) || empty($authUser['id']) || ! (new AdminAccessService())->isAdmin($authUser)) {
            return $redirect->with('error', 'Phiên quản trị đã hết hạn. Hãy đăng nhập lại rồi kết nối OA.');
        }

        $expectedState = (string) session()->get('zalo_oa_oauth_state');
        $codeVerifier = (string) session()->get('zalo_oa_oauth_code_verifier');
        $expiresAt = (int) session()->get('zalo_oa_oauth_expires_at');
        $receivedState = trim((string) $this->request->getGet('state'));
        session()->remove(['zalo_oa_oauth_state', 'zalo_oa_oauth_code_verifier', 'zalo_oa_oauth_expires_at']);

        if (
            $expectedState === ''
            || strlen($codeVerifier) < 43
            || $expiresAt < time()
            || ! hash_equals($expectedState, $receivedState)
        ) {
            return $redirect->with('error', 'Yêu cầu kết nối Zalo không hợp lệ hoặc đã hết hạn.');
        }

        $providerError = trim((string) ($this->request->getGet('error') ?? $this->request->getGet('error_code')));
        if ($providerError !== '' && $providerError !== '0') {
            return $redirect->with('error', 'Zalo từ chối cấp quyền. Mã lỗi: ' . $providerError);
        }

        $code = trim((string) $this->request->getGet('code'));
        if ($code === '') {
            return $redirect->with('error', 'Zalo không trả về authorization code.');
        }

        $result = (new ZaloOaTokenService())->connect(
            $code,
            trim((string) $this->request->getGet('oa_id')),
            (int) $authUser['id'],
            $codeVerifier
        );

        if (! $result['ok']) {
            $detail = $result['error_code'] !== '' ? ' (' . $result['error_code'] . ')' : '';

            return $redirect->with('error', 'Không thể kết nối Zalo OA: ' . $result['reason'] . $detail);
        }

        $oaLabel = $result['oa_name'] !== '' ? $result['oa_name'] : $result['oa_id'];

        return $redirect->with('success', 'Đã kết nối Zalo OA: ' . $oaLabel . '.');
    }
}
