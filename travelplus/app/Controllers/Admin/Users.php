<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Services\AuthSessionControlService;
use App\Services\RememberLoginService;
use App\Services\VietnamPhoneService;

class Users extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $status = trim((string) $this->request->getGet('status'));
        $keyword = trim((string) $this->request->getGet('q'));

        $builder = (new UserModel())
            ->select('id, full_name, email, username, phone, is_admin, status, last_login_at, created_at');

        if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
            $builder->where('status', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('full_name', $keyword)
                ->orLike('email', $keyword)
                ->orLike('username', $keyword)
                ->orLike('phone', $keyword)
                ->groupEnd();
        }

        $users = $builder->orderBy('created_at', 'DESC')->findAll();

        return view('admin/users/index', [
            'users' => $users,
            'status' => $status,
            'keyword' => $keyword,
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('admin/users/form', [
            'user' => [],
            'logs' => [],
            'errors' => session()->getFlashdata('errors') ?? [],
            'pageTitle' => 'Create user',
            'pageDesc' => 'Tạo tài khoản mới cho hệ thống Travel Plus.',
            'formAction' => site_url('admin/users'),
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $userModel = new UserModel();

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $username = trim((string) $this->request->getPost('username'));
        $fullName = trim((string) $this->request->getPost('full_name'));
        $phone = VietnamPhoneService::normalize((string) $this->request->getPost('phone'));
        $status = trim((string) $this->request->getPost('status'));
        $password = (string) $this->request->getPost('password');
        $isAdmin = $this->request->getPost('is_admin') ? 1 : 0;

        $errors = $this->validateUserPayload($userModel, $fullName, $email, $username, $phone, $status, $password, null);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $userModel->insert([
            'full_name' => $fullName,
            'email' => $email,
            'username' => $username,
            'phone' => $phone,
            'status' => $status,
            'is_admin' => $isAdmin,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $userId = (int) $userModel->getInsertID();
        $this->logUserChange($userId, 'created', [
            'full_name' => $fullName,
            'email' => $email,
            'username' => $username,
            'phone' => $phone,
            'status' => $status,
            'is_admin' => $isAdmin,
        ]);

        return redirect()->to(site_url('admin/users'))->with('success', 'Đã tạo tài khoản mới.');
    }

    public function edit(int $userId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $user = (new UserModel())->find($userId);
        if (! is_array($user)) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Không tìm thấy tài khoản.');
        }

        return view('admin/users/form', [
            'user' => $user,
            'logs' => $this->getUserLogs($userId),
            'errors' => session()->getFlashdata('errors') ?? [],
            'pageTitle' => 'Edit user',
            'pageDesc' => 'Cập nhật thông tin tài khoản, quyền admin và trạng thái hoạt động.',
            'formAction' => site_url('admin/users/' . $userId),
        ]);
    }

    public function update(int $userId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (! is_array($user)) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Không tìm thấy tài khoản.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $username = trim((string) $this->request->getPost('username'));
        $fullName = trim((string) $this->request->getPost('full_name'));
        $phone = VietnamPhoneService::normalize((string) $this->request->getPost('phone'));
        $status = trim((string) $this->request->getPost('status'));
        $newPassword = (string) $this->request->getPost('new_password');
        $isAdmin = $this->request->getPost('is_admin') ? 1 : 0;

        $errors = $this->validateUserPayload($userModel, $fullName, $email, $username, $phone, $status, $newPassword, $userId, true);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $payload = [
            'full_name' => $fullName,
            'email' => $email,
            'username' => $username,
            'phone' => $phone,
            'status' => $status,
            'is_admin' => $isAdmin,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($newPassword !== '') {
            $payload['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $changes = $this->collectUserChanges($user, $payload, $newPassword !== '');

        $userModel->update($userId, $payload);
        if ($newPassword !== '') {
            (new AuthSessionControlService())->invalidateAllSessions($userId);
            (new RememberLoginService())->revokeAllForUser($userId);
        }

        if ($changes !== []) {
            $this->logUserChange($userId, 'updated', $changes);
        }

        return redirect()->to(site_url('admin/users/' . $userId . '/edit'))->with('success', 'Đã cập nhật tài khoản #' . $userId . '.');
    }

    private function validateUserPayload(
        UserModel $userModel,
        string $fullName,
        string $email,
        string $username,
        string $phone,
        string $status,
        string $password,
        ?int $ignoreUserId = null,
        bool $passwordOptional = false
    ): array {
        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Họ và tên là bắt buộc.';
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }
        if ($username === '') {
            $errors[] = 'Username là bắt buộc.';
        }
        if (! in_array($status, ['active', 'inactive'], true)) {
            $errors[] = 'Trạng thái không hợp lệ.';
        }
        if ($phone !== '' && ! VietnamPhoneService::isValid($phone)) {
            $errors[] = 'Số điện thoại Việt Nam không hợp lệ.';
        }
        if ((! $passwordOptional && strlen($password) < 6) || ($passwordOptional && $password !== '' && strlen($password) < 6)) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        $emailBuilder = $userModel->where('email', $email);
        $usernameBuilder = $userModel->where('username', $username);
        if ($ignoreUserId !== null) {
            $emailBuilder->where('id !=', $ignoreUserId);
            $usernameBuilder->where('id !=', $ignoreUserId);
        }

        if ($emailBuilder->first() !== null) {
            $errors[] = 'Email đã được dùng bởi tài khoản khác.';
        }
        if ($usernameBuilder->first() !== null) {
            $errors[] = 'Username đã được dùng bởi tài khoản khác.';
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    private function collectUserChanges(array $current, array $payload, bool $passwordChanged): array
    {
        $changes = [];

        foreach (['full_name', 'email', 'username', 'phone', 'status', 'is_admin'] as $field) {
            $before = $current[$field] ?? null;
            $after = $payload[$field] ?? null;
            if ((string) $before !== (string) $after) {
                $changes[$field] = [
                    'before' => $before,
                    'after' => $after,
                ];
            }
        }

        if ($passwordChanged) {
            $changes['password'] = [
                'before' => '***',
                'after' => 'updated',
            ];
        }

        return $changes;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getUserLogs(int $userId): array
    {
        $db = db_connect();

        if (! $db->tableExists('user_change_logs')) {
            return [];
        }

        return $db->table('user_change_logs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function logUserChange(int $userId, string $action, array $changes, string $note = ''): void
    {
        $db = db_connect();

        if (! $db->tableExists('user_change_logs')) {
            return;
        }

        $authUser = session()->get('auth_user');

        $db->table('user_change_logs')->insert([
            'user_id' => $userId,
            'actor_user_id' => is_array($authUser) ? ((int) ($authUser['id'] ?? 0) ?: null) : null,
            'actor_name' => is_array($authUser) ? ((string) ($authUser['full_name'] ?? '') ?: null) : null,
            'actor_email' => is_array($authUser) ? ((string) ($authUser['email'] ?? '') ?: null) : null,
            'action' => $action,
            'changes_json' => json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'note' => $note !== '' ? $note : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
