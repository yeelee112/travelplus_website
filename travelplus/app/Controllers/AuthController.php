<?php

namespace App\Controllers;

use App\Data\LocalizedPathCatalog;
use App\Models\BookingModel;
use App\Models\UserModel;
use App\Services\AuthSessionControlService;
use App\Services\RememberLoginService;
use Config\SocialAuth;

class AuthController extends BaseController
{
    private const LOGIN_ATTEMPT_LIMIT = 5;
    private const LOGIN_ATTEMPT_DECAY = 600;

    public function register()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $returnTo = $this->resolveReturnTo();

        if (session()->has('auth_user')) {
            return redirect()->to($returnTo ?: LocalizedPathCatalog::url('auth.profile', $locale));
        }

        if ($this->request->is('post')) {
            return $this->handleRegister();
        }

        return view('auth/register', [
            'googleEnabled' => $this->googleEnabled(),
            'authSuccess' => session()->getFlashdata('auth_success'),
            'returnTo' => $returnTo,
        ]);
    }

    public function login()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $returnTo = $this->resolveReturnTo();

        if ($this->request->is('get')) {
            if (session()->has('auth_user')) {
                return redirect()->to($returnTo ?: LocalizedPathCatalog::url('auth.profile', $locale));
            }

            return view('auth/login', [
                'googleEnabled' => $this->googleEnabled(),
                'authSuccess' => session()->getFlashdata('auth_success'),
                'returnTo' => $returnTo,
            ]);
        }

        $rules = [
            'identity' => 'required|min_length[3]|max_length[255]',
            'password' => 'required|min_length[6]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAuthError(lang('Frontend.auth.loginInvalid', [], $locale), 422);
        }

        $identity = trim((string) $this->request->getPost('identity'));
        $password = (string) $this->request->getPost('password');
        $rememberMe = (bool) $this->request->getPost('remember_me');
        $throttleSeconds = $this->throttleSecondsRemaining($identity, (string) $this->request->getIPAddress());

        if ($throttleSeconds > 0) {
            $message = $locale === 'en'
                ? 'Too many login attempts. Try again in ' . $throttleSeconds . ' seconds.'
                : 'Đăng nhập thất bại quá nhiều lần. Hãy thử lại sau ' . $throttleSeconds . ' giây.';

            return $this->respondAuthError($message, 429);
        }

        $userModel = new UserModel();
        $user = $userModel
            ->groupStart()
            ->where('email', $identity)
            ->orWhere('username', $identity)
            ->groupEnd()
            ->where('status', 'active')
            ->first();

        if ($user === null || ! password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            $this->recordFailedLoginAttempt($identity, (string) $this->request->getIPAddress());
            return $this->respondAuthError(lang('Frontend.auth.loginCredentialsInvalid', [], $locale), 401);
        }

        $this->clearFailedLoginAttempt($identity, (string) $this->request->getIPAddress());

        $userModel->update((int) $user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set('auth_user', $this->buildAuthSessionUser($user));
        session()->remove('checkout_mode');

        $rememberService = new RememberLoginService();
        if ($rememberMe) {
            $rememberService->issue($user);
        } else {
            $rememberService->clear();
        }

        return $this->respondAuthSuccess();
    }

    public function profile()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $authUser = session()->get('auth_user');

        if (! is_array($authUser) || empty($authUser['id'])) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', lang('Frontend.auth.profile.loginRequired', [], $locale));
        }

        $user = (new UserModel())->find((int) $authUser['id']);

        if ($user === null) {
            session()->remove(['auth_user', 'checkout_mode']);

            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', lang('Frontend.auth.profile.notFound', [], $locale));
        }

        if ($this->request->is('post')) {
            $fullName = trim((string) $this->request->getPost('full_name'));
            $phone = trim((string) $this->request->getPost('phone'));
            $newPassword = (string) $this->request->getPost('new_password');
            $confirmPassword = (string) $this->request->getPost('new_password_confirm');

            if ($fullName === '') {
                return redirect()->back()->with('auth_error', $locale === 'en' ? 'Full name is required.' : 'Họ và tên là bắt buộc.');
            }

            if ($newPassword !== '') {
                if (strlen($newPassword) < 6) {
                    return redirect()->back()->with('auth_error', $locale === 'en' ? 'New password must be at least 6 characters.' : 'Mật khẩu mới phải có ít nhất 6 ký tự.');
                }
                if ($newPassword !== $confirmPassword) {
                    return redirect()->back()->with('auth_error', $locale === 'en' ? 'Password confirmation does not match.' : 'Xác nhận mật khẩu không khớp.');
                }
            }

            $payload = [
                'full_name' => $fullName,
                'phone' => $phone,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($newPassword !== '') {
                $payload['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            (new UserModel())->update((int) $authUser['id'], $payload);
            if ($newPassword !== '') {
                (new AuthSessionControlService())->invalidateAllSessions((int) $authUser['id']);
                (new RememberLoginService())->revokeAllForUser((int) $authUser['id']);
            }

            $freshUser = (new UserModel())->find((int) $authUser['id']);
            if (is_array($freshUser)) {
                session()->set('auth_user', $this->buildAuthSessionUser($freshUser));
            }

            return redirect()->to(LocalizedPathCatalog::url('auth.profile', $locale))
                ->with('auth_success', $locale === 'en' ? 'Account updated successfully.' : 'Đã cập nhật tài khoản thành công.');
        }

        $bookings = (new BookingModel())
            ->where('user_id', (int) $authUser['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        return view('auth/profile', [
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }

    public function forgotPassword()
    {
        $locale = $this->request->getLocale() ?: 'vi';

        if ($this->request->is('get')) {
            return view('auth/forgot-password');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('auth_error', $locale === 'en' ? 'Invalid email address.' : 'Email không hợp lệ.');
        }

        $db = db_connect();
        if (! $db->tableExists('password_reset_tokens')) {
            return redirect()->back()->withInput()->with(
                'auth_error',
                $locale === 'en' ? 'The password_reset_tokens table does not exist.' : 'Bảng password_reset_tokens chưa tồn tại.'
            );
        }

        $user = (new UserModel())->where('email', $email)->first();
        if ($user !== null) {
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);
            $now = date('Y-m-d H:i:s');
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 minutes'));

            $db->table('password_reset_tokens')->where('email', $email)->delete();
            $db->table('password_reset_tokens')->insert([
                'user_id' => (int) $user['id'],
                'email' => $email,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'used_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->sendPasswordResetEmail($locale, $email, (string) ($user['full_name'] ?? $email), $plainToken);
        }

        $message = $locale === 'en'
            ? 'If the account exists, a password reset link has been sent to your email.'
            : 'Nếu tài khoản tồn tại, liên kết đặt lại mật khẩu đã được gửi tới email của bạn.';

        return redirect()->to(LocalizedPathCatalog::url('auth.forgotPassword', $locale))->with('auth_success', $message);
    }

    public function resetPassword(string $token)
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $db = db_connect();

        if (! $db->tableExists('password_reset_tokens')) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $locale === 'en' ? 'The password reset table does not exist.' : 'Bảng đặt lại mật khẩu chưa tồn tại.');
        }

        $row = $db->table('password_reset_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->get()
            ->getRowArray();

        $invalidMessage = $locale === 'en'
            ? 'The password reset link is invalid or has expired.'
            : 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';

        if (! is_array($row) || ! empty($row['used_at']) || strtotime((string) ($row['expires_at'] ?? '')) < time()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.forgotPassword', $locale))->with('auth_error', $invalidMessage);
        }

        if ($this->request->is('get')) {
            return view('auth/reset-password');
        }

        $password = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        if (strlen($password) < 6) {
            return redirect()->back()->with('auth_error', $locale === 'en' ? 'Password must be at least 6 characters.' : 'Mật khẩu phải có ít nhất 6 ký tự.');
        }

        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('auth_error', $locale === 'en' ? 'Password confirmation does not match.' : 'Xác nhận mật khẩu không khớp.');
        }

        (new UserModel())->update((int) $row['user_id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        (new AuthSessionControlService())->invalidateAllSessions((int) $row['user_id']);
        (new RememberLoginService())->revokeAllForUser((int) $row['user_id']);

        $db->table('password_reset_tokens')
            ->where('id', (int) $row['id'])
            ->update([
                'used_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with(
            'auth_success',
            $locale === 'en' ? 'Password updated successfully. Please sign in again.' : 'Đã cập nhật mật khẩu thành công. Vui lòng đăng nhập lại.'
        );
    }

    public function logout()
    {
        (new RememberLoginService())->clear();
        session()->remove(['auth_user', 'checkout_mode']);

        return redirect()->to(localized_url('/'))
            ->with('auth_success', lang('Frontend.auth.logoutSuccess', [], $this->request->getLocale() ?: 'vi'));
    }

    public function logoutAllDevices()
    {
        $locale = $this->request->getLocale() ?: 'vi';
        $authUser = session()->get('auth_user');

        if (! is_array($authUser) || empty($authUser['id'])) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale));
        }

        $rememberService = new RememberLoginService();
        (new AuthSessionControlService())->invalidateAllSessions((int) $authUser['id']);
        $rememberService->revokeAllForUser((int) $authUser['id']);
        $rememberService->clear();
        session()->remove(['auth_user', 'checkout_mode']);

        return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with(
            'auth_success',
            $locale === 'en'
                ? 'All remembered devices have been signed out. Please sign in again.'
                : 'Đã đăng xuất tất cả thiết bị đã ghi nhớ. Vui lòng đăng nhập lại.'
        );
    }

    public function google()
    {
        $config = config(SocialAuth::class);
        $locale = $this->request->getLocale() ?: 'vi';
        $returnTo = $this->resolveReturnTo();

        if (! $this->googleEnabled()) {
            return redirect()->back()->with('auth_error', lang('Frontend.auth.googleNotConfigured'));
        }

        $state = bin2hex(random_bytes(16));
        session()->set('google_oauth_state', $state);
        if ($returnTo !== null) {
            session()->set('auth_return_to', $returnTo);
        } else {
            session()->remove('auth_return_to');
        }

        $params = [
            'client_id' => $config->googleClientId,
            'redirect_uri' => LocalizedPathCatalog::url('auth.googleCallback', $locale),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state,
        ];

        return redirect()->to('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }

    public function googleCallback()
    {
        $locale = $this->request->getLocale() ?: 'vi';

        if (! $this->googleEnabled()) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with('auth_error', lang('Frontend.auth.googleNotConfigured'));
        }

        $state = (string) $this->request->getGet('state');
        $storedState = (string) session()->get('google_oauth_state');
        session()->remove('google_oauth_state');

        if ($state === '' || $storedState === '' || ! hash_equals($storedState, $state)) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with('auth_error', lang('Frontend.auth.googleStateInvalid'));
        }

        $code = (string) $this->request->getGet('code');

        if ($code === '') {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with('auth_error', lang('Frontend.auth.googleCodeMissing'));
        }

        $config = config(SocialAuth::class);
        $client = service('curlrequest');
        $db = db_connect();

        if (! $db->tableExists('user_social_accounts')) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))
                ->with('auth_error', $locale === 'en'
                    ? 'The user_social_accounts table does not exist.'
                    : 'Bảng user_social_accounts chưa tồn tại.');
        }

        try {
            $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code' => $code,
                    'client_id' => $config->googleClientId,
                    'client_secret' => $config->googleClientSecret,
                    'redirect_uri' => LocalizedPathCatalog::url('auth.googleCallback', $locale),
                    'grant_type' => 'authorization_code',
                ],
            ]);

            $tokenPayload = json_decode((string) $tokenResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $accessToken = (string) ($tokenPayload['access_token'] ?? '');

            if ($accessToken === '') {
                throw new \RuntimeException('Google access token missing.');
            }

            $profileResponse = $client->get('https://www.googleapis.com/oauth2/v2/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            $profile = json_decode((string) $profileResponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with('auth_error', lang('Frontend.auth.googleLoginFailed'));
        }

        $googleId = (string) ($profile['id'] ?? '');
        $email = strtolower(trim((string) ($profile['email'] ?? '')));

        if ($googleId === '' || $email === '') {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with('auth_error', lang('Frontend.auth.googleProfileIncomplete'));
        }

        $userModel = new UserModel();
        $socialTable = $db->table('user_social_accounts');
        $social = $socialTable
            ->where('provider', 'google')
            ->where('provider_user_id', $googleId)
            ->get()
            ->getRowArray();

        if ($social !== null) {
            $user = $userModel->find((int) $social['user_id']);
        } else {
            $user = $userModel->where('email', $email)->first();

            if ($user === null) {
                $userId = $userModel->insert([
                    'full_name' => trim((string) ($profile['name'] ?? $email)),
                    'email' => $email,
                    'username' => $this->makeUniqueUsername($userModel, strstr($email, '@', true) ?: 'user'),
                    'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'status' => 'active',
                    'last_login_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], true);

                $user = $userModel->find((int) $userId);
            }

            if ($user !== null) {
                $socialTable->insert([
                    'user_id' => (int) $user['id'],
                    'provider' => 'google',
                    'provider_user_id' => $googleId,
                    'provider_email' => $email,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($user === null) {
            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale))->with('auth_error', lang('Frontend.auth.googleCreateFailed'));
        }

        $userModel->update((int) $user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set('auth_user', $this->buildAuthSessionUser($user));
        session()->remove('checkout_mode');

        return $this->redirectAfterAuth($locale);
    }

    private function handleRegister()
    {
        $locale = $this->request->getLocale();
        $db = db_connect();

        if (! $db->tableExists('users')) {
            return redirect()->back()->withInput()->with('auth_error', lang('Frontend.auth.usersTableMissing', [], $locale));
        }

        $rules = [
            'full_name' => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email|max_length[255]',
            'password' => 'required|min_length[6]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('auth_error', implode(' ', $this->validator->getErrors()));
        }

        $userModel = new UserModel();
        $email = strtolower(trim((string) $this->request->getPost('email')));

        if ($userModel->where('email', $email)->first() !== null) {
            return redirect()->back()->withInput()->with(
                'auth_error',
                lang('Frontend.auth.emailExists', [], $locale)
            );
        }

        $fullName = trim((string) $this->request->getPost('full_name'));
        $baseUsername = preg_replace('/[^a-z0-9]+/i', '.', strtolower($fullName)) ?: strstr($email, '@', true);
        $username = $this->makeUniqueUsername($userModel, (string) $baseUsername);
        $now = date('Y-m-d H:i:s');

        try {
            $userId = $userModel->insert([
                'full_name' => $fullName,
                'email' => $email,
                'username' => $username,
                'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'status' => 'active',
                'last_login_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ], true);
        } catch (\Throwable $exception) {
            log_message('error', 'Account registration failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()->withInput()->with('auth_error', lang('Frontend.auth.registerFailed', [], $locale));
        }

        $user = $userModel->find((int) $userId);

        if ($user !== null) {
            session()->set('auth_user', $this->buildAuthSessionUser($user));
            session()->remove('checkout_mode');
        }

        return $this->redirectAfterAuth($locale)->with('auth_success', lang('Frontend.auth.registerSuccess', [], $locale));
    }

    private function respondAuthSuccess()
    {
        $locale = $this->request->getLocale() ?: 'vi';

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'redirect' => $this->resolvePostAuthRedirectUrl($locale),
            ]);
        }

        return $this->redirectAfterAuth($locale);
    }

    private function respondAuthError(string $message, int $statusCode)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($statusCode)->setJSON([
                'ok' => false,
                'message' => $message,
            ]);
        }

        return redirect()->back()->withInput()->with('auth_error', $message);
    }

    private function makeUniqueUsername(UserModel $userModel, string $base): string
    {
        $base = trim($base, '. ');
        $base = preg_replace('/[^a-z0-9._-]+/i', '', $base) ?: 'user';
        $username = $base;
        $suffix = 1;

        while ($userModel->where('username', $username)->first() !== null) {
            $suffix++;
            $username = $base . $suffix;
        }

        return $username;
    }

    private function googleEnabled(): bool
    {
        $config = config(SocialAuth::class);

        return $config->googleEnabled
            && $config->googleClientId !== ''
            && $config->googleClientSecret !== '';
    }

    private function sendPasswordResetEmail(string $locale, string $email, string $fullName, string $plainToken): void
    {
        $emailService = service('email');
        $path = 'account/reset-password/' . $plainToken;
        $resetUrl = $locale === 'en' ? base_url('en/' . $path) : base_url($path);

        $subject = $locale === 'en' ? 'Reset your Travel Plus password' : 'Đặt lại mật khẩu Travel Plus';
        $greeting = $locale === 'en' ? 'Hello' : 'Xin chào';
        $line1 = $locale === 'en'
            ? 'We received a request to reset the password for your Travel Plus account.'
            : 'Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản Travel Plus của bạn.';
        $line2 = $locale === 'en'
            ? 'This link will expire in 60 minutes.'
            : 'Liên kết này sẽ hết hạn sau 60 phút.';

        $body = '<p>' . esc($greeting . ' ' . $fullName) . ',</p>'
            . '<p>' . esc($line1) . '</p>'
            . '<p><a href="' . esc($resetUrl) . '">' . esc($resetUrl) . '</a></p>'
            . '<p>' . esc($line2) . '</p>';

        try {
            $emailService->setTo($email);
            $emailService->setSubject($subject);
            $emailService->setMessage($body);
            $emailService->send();
        } catch (\Throwable $exception) {
        }
    }

    private function throttleSecondsRemaining(string $identity, string $ipAddress): int
    {
        $record = cache()->get($this->loginThrottleKey($identity, $ipAddress));
        if (! is_array($record)) {
            return 0;
        }

        $expiresAt = (int) ($record['expires_at'] ?? 0);
        $count = (int) ($record['count'] ?? 0);

        if ($expiresAt <= time()) {
            cache()->delete($this->loginThrottleKey($identity, $ipAddress));
            return 0;
        }

        if ($count < self::LOGIN_ATTEMPT_LIMIT) {
            return 0;
        }

        return max(0, $expiresAt - time());
    }

    private function recordFailedLoginAttempt(string $identity, string $ipAddress): void
    {
        $key = $this->loginThrottleKey($identity, $ipAddress);
        $record = cache()->get($key);
        $now = time();

        if (! is_array($record) || (int) ($record['expires_at'] ?? 0) <= $now) {
            $record = [
                'count' => 0,
                'expires_at' => $now + self::LOGIN_ATTEMPT_DECAY,
            ];
        }

        $record['count'] = (int) $record['count'] + 1;
        $ttl = max(60, (int) $record['expires_at'] - $now);

        cache()->save($key, $record, $ttl);
    }

    private function clearFailedLoginAttempt(string $identity, string $ipAddress): void
    {
        cache()->delete($this->loginThrottleKey($identity, $ipAddress));
    }

    private function loginThrottleKey(string $identity, string $ipAddress): string
    {
        return 'auth_login_attempts_' . sha1(strtolower(trim($identity)) . '|' . trim($ipAddress));
    }

    private function redirectAfterAuth(string $locale)
    {
        return redirect()->to($this->resolvePostAuthRedirectUrl($locale));
    }

    private function resolvePostAuthRedirectUrl(string $locale): string
    {
        if (session()->has('pending_booking')) {
            return LocalizedPathCatalog::url('booking.checkout', $locale);
        }

        return $this->consumeReturnTo()
            ?: LocalizedPathCatalog::url('auth.profile', $locale);
    }

    private function resolveReturnTo(): ?string
    {
        $candidates = [
            trim((string) $this->request->getPost('return_to')),
            trim((string) $this->request->getGet('return_to')),
            trim((string) session()->get('auth_return_to')),
        ];

        foreach ($candidates as $candidate) {
            $sanitized = $this->sanitizeReturnTo($candidate);
            if ($sanitized !== null) {
                session()->set('auth_return_to', $sanitized);
                return $sanitized;
            }
        }

        session()->remove('auth_return_to');
        return null;
    }

    private function consumeReturnTo(): ?string
    {
        $returnTo = $this->resolveReturnTo();
        session()->remove('auth_return_to');
        return $returnTo;
    }

    private function sanitizeReturnTo(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $baseUrl = rtrim((string) base_url('/'), '/');

        if (str_starts_with($url, '/')) {
            $candidate = base_url(ltrim($url, '/'));
            return str_starts_with($candidate, $baseUrl) ? $candidate : null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return str_starts_with($url, $baseUrl) ? $url : null;
    }
}
