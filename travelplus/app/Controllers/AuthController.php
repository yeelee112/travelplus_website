<?php

namespace App\Controllers;

use App\Models\UserModel;
use Config\SocialAuth;

class AuthController extends BaseController
{
    public function register()
    {
        if ($this->request->is('post')) {
            return $this->handleRegister();
        }

        return view('auth/register', [
            'googleEnabled' => $this->googleEnabled(),
            'authSuccess' => session()->getFlashdata('auth_success'),
        ]);
    }

    public function login()
    {
        $rules = [
            'identity' => 'required|min_length[3]|max_length[255]',
            'password' => 'required|min_length[6]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAuthError('Thông tin đăng nhập chưa hợp lệ.', 422);
        }

        $identity = trim((string) $this->request->getPost('identity'));
        $password = (string) $this->request->getPost('password');
        $userModel = new UserModel();
        $user = $userModel
            ->groupStart()
            ->where('email', $identity)
            ->orWhere('username', $identity)
            ->groupEnd()
            ->where('status', 'active')
            ->first();

        if ($user === null || ! password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            return $this->respondAuthError('Sai tài khoản hoặc mật khẩu.', 401);
        }

        $userModel->update((int) $user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set('auth_user', $this->buildAuthSessionUser($user));
        session()->remove('checkout_mode');

        return $this->respondAuthSuccess();
    }

    public function logout()
    {
        session()->remove(['auth_user', 'checkout_mode']);

        return redirect()->to(localized_url('/'));
    }

    public function google()
    {
        $config = config(SocialAuth::class);

        if (! $this->googleEnabled()) {
            return redirect()->back()->with('auth_error', 'Google login chưa được cấu hình.');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('google_oauth_state', $state);

        $params = [
            'client_id' => $config->googleClientId,
            'redirect_uri' => localized_url('auth/google/callback'),
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
        if (! $this->googleEnabled()) {
            return redirect()->to(localized_url('account/register'))->with('auth_error', 'Google login chưa được cấu hình.');
        }

        $state = (string) $this->request->getGet('state');
        $storedState = (string) session()->get('google_oauth_state');
        session()->remove('google_oauth_state');

        if ($state === '' || $storedState === '' || ! hash_equals($storedState, $state)) {
            return redirect()->to(localized_url('account/register'))->with('auth_error', 'Phiên xác thực Google không hợp lệ.');
        }

        $code = (string) $this->request->getGet('code');

        if ($code === '') {
            return redirect()->to(localized_url('account/register'))->with('auth_error', 'Không nhận được mã xác thực Google.');
        }

        $config = config(SocialAuth::class);
        $client = service('curlrequest');

        try {
            $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code' => $code,
                    'client_id' => $config->googleClientId,
                    'client_secret' => $config->googleClientSecret,
                    'redirect_uri' => localized_url('auth/google/callback'),
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
            return redirect()->to(localized_url('account/register'))->with('auth_error', 'Không thể đăng nhập bằng Google lúc này.');
        }

        $googleId = (string) ($profile['id'] ?? '');
        $email = strtolower(trim((string) ($profile['email'] ?? '')));

        if ($googleId === '' || $email === '') {
            return redirect()->to(localized_url('account/register'))->with('auth_error', 'Google không trả về đủ thông tin tài khoản.');
        }

        $db = db_connect();
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
            return redirect()->to(localized_url('account/register'))->with('auth_error', 'Không thể khởi tạo tài khoản Google.');
        }

        $userModel->update((int) $user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set('auth_user', $this->buildAuthSessionUser($user));
        session()->remove('checkout_mode');

        return redirect()->to(localized_url('booking/checkout'));
    }

    private function handleRegister()
    {
        $db = db_connect();

        if (! $db->tableExists('users')) {
            return redirect()->back()->withInput()->with('auth_error', 'Bảng users chưa tồn tại. Hãy chạy file SQL tạo tài khoản trước.');
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
            return redirect()->back()->withInput()->with('auth_error', 'Email này đã tồn tại.');
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
            return redirect()->back()->withInput()->with('auth_error', 'Không thể tạo tài khoản lúc này: ' . $exception->getMessage());
        }

        $user = $userModel->find((int) $userId);

        if ($user !== null) {
            session()->set('auth_user', $this->buildAuthSessionUser($user));
            session()->remove('checkout_mode');
        }

        if (session()->has('pending_booking')) {
            return redirect()->to(localized_url('booking/checkout'));
        }

        return redirect()->to(localized_url('account/register'))->with('auth_success', 'Tạo tài khoản thành công. Bạn đã được đăng nhập.');
    }

    private function buildAuthSessionUser(array $user): array
    {
        return [
            'id' => (int) ($user['id'] ?? 0),
            'full_name' => (string) ($user['full_name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
        ];
    }

    private function respondAuthSuccess()
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'redirect' => localized_url('booking/checkout'),
            ]);
        }

        return redirect()->to(localized_url('booking/checkout'));
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
}
