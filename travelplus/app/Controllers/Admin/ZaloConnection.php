<?php

namespace App\Controllers\Admin;

use App\Services\ZaloOaTokenService;
use App\Services\ZaloOtpService;
use App\Services\AccountVerificationService;
use App\Services\VietnamPhoneService;

final class ZaloConnection extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('admin/zalo/index', [
            'status' => (new ZaloOaTokenService())->status(),
            'otpReadiness' => (new ZaloOtpService())->readiness(),
            'latestZaloDelivery' => $this->latestZaloDelivery(),
            'testResult' => session()->getFlashdata('zalo_test_result'),
            'testPhone' => session()->getFlashdata('zalo_test_phone'),
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

    public function testOtp()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $target = site_url('admin/zalo');
        $phone = VietnamPhoneService::normalize((string) $this->request->getPost('phone'));
        session()->setFlashdata('zalo_test_phone', $phone);

        if ((string) $this->request->getPost('confirm_cost') !== '1') {
            return redirect()->to($target)->with('error', 'Hãy xác nhận chi phí trước khi gửi OTP thử.');
        }

        if (! VietnamPhoneService::isValid($phone)) {
            return redirect()->to($target)->with('error', 'Số điện thoại thử không hợp lệ.');
        }

        $lastTestAt = (int) session()->get('zalo_otp_test_last_at');
        if ($lastTestAt > time() - 30) {
            return redirect()->to($target)->with('error', 'Vui lòng chờ 30 giây trước khi gửi lại OTP thử.');
        }

        $service = new ZaloOtpService();
        $readiness = $service->readiness();
        if (! $readiness['ready']) {
            session()->setFlashdata('zalo_test_result', [
                'ok' => false,
                'reason' => $readiness['reason'],
                'error_code' => '',
                'provider_message' => '',
            ]);

            return redirect()->to($target);
        }

        session()->set('zalo_otp_test_last_at', time());
        $otp = (string) random_int(100000, 999999);
        $authUser = session()->get('auth_user');
        $trackingId = 'tvpotptest' . (int) ($authUser['id'] ?? 0) . date('YmdHis');
        $result = $service->send($phone, $otp, $trackingId);
        session()->setFlashdata('zalo_test_result', $result);

        return redirect()->to($target);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function latestZaloDelivery(): ?array
    {
        try {
            $db = db_connect();
            if (! $db->tableExists('account_verification_requests')) {
                return null;
            }

            $row = $db->table('account_verification_requests')
                ->select('recipient, delivery_status, provider_message_id, provider_error_code, created_at')
                ->where('channel', AccountVerificationService::CHANNEL_ZALO)
                ->orderBy('id', 'DESC')
                ->get(1)
                ->getRowArray();

            if (! is_array($row)) {
                return null;
            }

            $row['recipient'] = AccountVerificationService::maskPhone((string) ($row['recipient'] ?? ''));

            return $row;
        } catch (\Throwable $exception) {
            log_message('error', 'Unable to load latest Zalo OTP delivery: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
