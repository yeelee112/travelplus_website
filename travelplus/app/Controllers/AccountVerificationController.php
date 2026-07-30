<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Models\UserModel;
use App\Services\AccountVerificationService;

class AccountVerificationController extends BaseController
{
    public function index()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        if (session()->has('auth_user')) {
            return redirect()->to(LocalizedPathCatalog::url('auth.profile', $locale));
        }

        $user = $this->pendingUser();
        if ($user === null) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'session_missing'));
        }

        $service = new AccountVerificationService();
        if (! $service->schemaReady()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'schema_missing'));
        }

        $request = $service->latestRequestForUser((int) $user['id']);
        if ($request === null) {
            $service->start($user, $locale);
            $request = $service->latestRequestForUser((int) $user['id']);
        }

        $channel = (string) ($request['channel'] ?? AccountVerificationService::CHANNEL_EMAIL);
        $recipient = $channel === AccountVerificationService::CHANNEL_ZALO
            ? AccountVerificationService::maskPhone((string) ($user['phone'] ?? ''))
            : AccountVerificationService::maskEmail((string) ($user['email'] ?? ''));

        return view('auth/verify-account', [
            'channel' => $channel,
            'recipient' => $recipient,
            'deliveryFailed' => ($request['delivery_status'] ?? '') === 'failed',
        ]);
    }

    public function verifyOtp()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $user = $this->pendingUser();
        if ($user === null) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'session_missing'));
        }

        $service = new AccountVerificationService();
        if (! $service->schemaReady()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'schema_missing'));
        }

        $result = $service->verifyOtp(
            (int) $user['id'],
            (string) $this->request->getPost('otp')
        );

        if (! $result['ok'] || ! is_array($result['user'])) {
            return redirect()->to(LocalizedPathCatalog::url('auth.verify', $locale))
                ->with('auth_error', $this->message($locale, (string) $result['reason']));
        }

        return $this->complete($result['user'], $locale);
    }

    public function verifyEmail(string $token)
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $service = new AccountVerificationService();
        if (! $service->schemaReady()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'schema_missing'));
        }

        $result = $service->verifyEmailToken($token);
        if (! $result['ok'] || ! is_array($result['user'])) {
            $target = session()->has('pending_verification_user_id')
                ? LocalizedPathCatalog::url('auth.verify', $locale)
                : LocalizedPathCatalog::url('auth.login', $locale);

            return redirect()->to($target)
                ->with('auth_error', $this->message($locale, (string) $result['reason']));
        }

        return $this->complete($result['user'], $locale);
    }

    public function resend()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $user = $this->pendingUser();
        if ($user === null) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'session_missing'));
        }

        $service = new AccountVerificationService();
        if (! $service->schemaReady()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'schema_missing'));
        }
        $latest = $service->latestRequestForUser((int) $user['id']);
        $preferZalo = ($latest['channel'] ?? '') !== AccountVerificationService::CHANNEL_EMAIL;
        $result = $service->start($user, $locale, $preferZalo);

        return $this->deliveryRedirect($locale, $result);
    }

    public function useEmail()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $user = $this->pendingUser();
        if ($user === null) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'session_missing'));
        }

        $service = new AccountVerificationService();
        if (! $service->schemaReady()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $this->message($locale, 'schema_missing'));
        }

        $result = $service->sendEmailFallback($user, $locale);
        return $this->deliveryRedirect($locale, $result);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function pendingUser(): ?array
    {
        $userId = (int) session()->get('pending_verification_user_id');
        if ($userId <= 0) {
            return null;
        }

        $user = (new UserModel())->find($userId);
        return is_array($user) && ($user['status'] ?? '') === AccountVerificationService::STATUS_PENDING
            ? $user
            : null;
    }

    /**
     * @param array{sent:bool,channel:string,recipient:string,reason:string,cooldown:int} $result
     */
    private function deliveryRedirect(string $locale, array $result)
    {
        $redirect = redirect()->to(LocalizedPathCatalog::url('auth.verify', $locale));
        if ($result['sent']) {
            return $redirect->with('auth_success', $this->message($locale, 'sent_' . $result['channel']));
        }

        $key = $result['reason'] === 'cooldown' ? 'cooldown' : (string) $result['reason'];
        return $redirect->with('auth_error', $this->message($locale, $key, (int) $result['cooldown']));
    }

    /**
     * @param array<string, mixed> $user
     */
    private function complete(array $user, string $locale)
    {
        session()->set('auth_user', $this->buildAuthSessionUser($user));
        session()->remove(['pending_verification_user_id', 'checkout_mode']);

        $returnTo = trim((string) session()->get('auth_return_to'));
        session()->remove('auth_return_to');
        $target = session()->has('pending_booking')
            ? LocalizedPathCatalog::url('booking.checkout', $locale)
            : ($returnTo !== '' ? $returnTo : LocalizedPathCatalog::url('auth.profile', $locale));

        return redirect()->to($target)->with(
            'auth_success',
            $locale === 'en'
                ? 'Your account has been verified successfully.'
                : 'Tài khoản đã được xác thực thành công.'
        );
    }

    private function message(string $locale, string $key, int $seconds = 0): string
    {
        $messages = $locale === 'en' ? [
            'session_missing' => 'Your verification session has expired. Please sign in again.',
            'schema_missing' => 'Account verification is not installed yet. Please contact Travel Plus.',
            'invalid' => 'The verification code is incorrect.',
            'not_found' => 'No active verification code was found. Please request a new code.',
            'expired' => 'The verification code has expired. Please request a new one.',
            'attempts_exceeded' => 'Too many incorrect attempts. Please request a new code.',
            'database_error' => 'Unable to verify the account right now. Please try again.',
            'not_pending' => 'This account is no longer awaiting verification.',
            'sent_zalo' => 'A new verification code has been sent via Zalo.',
            'sent_email' => 'A new verification code has been sent to your email.',
            'cooldown' => 'Please wait ' . max(1, $seconds) . ' seconds before requesting another message.',
            'daily_limit' => 'The daily verification message limit has been reached. Please try again tomorrow.',
            'delivery_failed' => 'The verification message could not be sent. Please try again later.',
            'invalid_email' => 'The account email address is invalid.',
        ] : [
            'session_missing' => 'Phiên xác thực đã hết hạn. Vui lòng đăng nhập lại.',
            'schema_missing' => 'Chức năng xác thực tài khoản chưa được cài đặt. Vui lòng liên hệ Travel Plus.',
            'invalid' => 'Mã xác thực không chính xác.',
            'not_found' => 'Không tìm thấy mã xác thực còn hiệu lực. Vui lòng yêu cầu mã mới.',
            'expired' => 'Mã xác thực đã hết hạn. Vui lòng yêu cầu gửi lại.',
            'attempts_exceeded' => 'Bạn đã nhập sai quá số lần cho phép. Vui lòng yêu cầu mã mới.',
            'database_error' => 'Chưa thể xác thực tài khoản lúc này. Vui lòng thử lại.',
            'not_pending' => 'Tài khoản này không còn ở trạng thái chờ xác thực.',
            'sent_zalo' => 'Mã xác thực mới đã được gửi qua Zalo.',
            'sent_email' => 'Mã xác thực mới đã được gửi tới email.',
            'cooldown' => 'Vui lòng chờ ' . max(1, $seconds) . ' giây trước khi yêu cầu gửi lại.',
            'daily_limit' => 'Đã đạt giới hạn gửi mã trong ngày. Vui lòng thử lại vào ngày mai.',
            'delivery_failed' => 'Chưa thể gửi thông tin xác thực. Vui lòng thử lại sau.',
            'invalid_email' => 'Email của tài khoản không hợp lệ.',
        ];

        return $messages[$key] ?? ($locale === 'en'
            ? 'Unable to complete verification right now.'
            : 'Chưa thể hoàn tất xác thực lúc này.');
    }
}
