<?php

namespace App\Controllers\Admin;

use App\Services\WebsiteSettingsService;

class WebsiteSettings extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('admin/website-settings/index', [
            'settings' => (new WebsiteSettingsService())->all(),
            'success' => session()->getFlashdata('success'),
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function update()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $values = [
            'hotline_e164' => trim((string) $this->request->getPost('hotline_e164')),
            'hotline_vi' => trim((string) $this->request->getPost('hotline_vi')),
            'hotline_en' => trim((string) $this->request->getPost('hotline_en')),
            'email' => trim((string) $this->request->getPost('email')),
            'facebook_url' => trim((string) $this->request->getPost('facebook_url')),
            'messenger_url' => trim((string) $this->request->getPost('messenger_url')),
            'youtube_url' => trim((string) $this->request->getPost('youtube_url')),
            'zalo_url' => trim((string) $this->request->getPost('zalo_url')),
        ];
        $errors = $this->validateValues($values);

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        if (! (new WebsiteSettingsService())->save($values)) {
            return redirect()->back()->withInput()->with('error', 'Không thể lưu cấu hình. Hãy kiểm tra quyền ghi thư mục writable/stats.');
        }

        return redirect()->to(site_url('admin/website-settings'))->with('success', 'Đã cập nhật thông tin liên hệ trên website.');
    }

    /**
     * @param array<string, string> $values
     * @return list<string>
     */
    private function validateValues(array $values): array
    {
        $errors = [];
        if (preg_match('/^\+[1-9]\d{7,14}$/', $values['hotline_e164']) !== 1) {
            $errors[] = 'Số gọi điện phải ở dạng quốc tế, ví dụ +84795681568.';
        }
        if ($values['hotline_vi'] === '' || mb_strlen($values['hotline_vi']) > 40) {
            $errors[] = 'Số hiển thị tiếng Việt không hợp lệ.';
        }
        if ($values['hotline_en'] === '' || mb_strlen($values['hotline_en']) > 40) {
            $errors[] = 'Số hiển thị tiếng Anh không hợp lệ.';
        }
        if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Email liên hệ không hợp lệ.';
        }

        foreach (['facebook_url', 'messenger_url', 'youtube_url', 'zalo_url'] as $key) {
            $url = $values[$key];
            if (filter_var($url, FILTER_VALIDATE_URL) === false || ! str_starts_with(strtolower($url), 'https://')) {
                $errors[] = 'Các liên kết mạng xã hội phải là URL HTTPS đầy đủ.';
                break;
            }
        }

        return $errors;
    }
}
