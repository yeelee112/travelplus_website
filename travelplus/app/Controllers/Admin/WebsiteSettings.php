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
            'company_tax_id' => trim((string) $this->request->getPost('company_tax_id')),
            'travel_license' => trim((string) $this->request->getPost('travel_license')),
            'office_hcm_address_vi' => trim((string) $this->request->getPost('office_hcm_address_vi')),
            'office_hcm_address_en' => trim((string) $this->request->getPost('office_hcm_address_en')),
            'office_hcm_map_url' => trim((string) $this->request->getPost('office_hcm_map_url')),
            'office_hanoi_address_vi' => trim((string) $this->request->getPost('office_hanoi_address_vi')),
            'office_hanoi_address_en' => trim((string) $this->request->getPost('office_hanoi_address_en')),
            'office_hanoi_map_url' => trim((string) $this->request->getPost('office_hanoi_map_url')),
            'office_danang_address_vi' => trim((string) $this->request->getPost('office_danang_address_vi')),
            'office_danang_address_en' => trim((string) $this->request->getPost('office_danang_address_en')),
            'office_danang_map_url' => trim((string) $this->request->getPost('office_danang_map_url')),
        ];
        $errors = $this->validateValues($values);

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        if (! (new WebsiteSettingsService())->save($values)) {
            return redirect()->back()->withInput()->with('error', 'Không thể lưu cấu hình. Hãy kiểm tra quyền ghi thư mục writable/stats.');
        }

        return redirect()->to(site_url('admin/website-settings'))->with('success', 'Đã cập nhật thông tin công khai trên website.');
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
        if (preg_match('/^[0-9-]{8,20}$/', $values['company_tax_id']) !== 1) {
            $errors[] = 'Mã số thuế không hợp lệ.';
        }
        if ($values['travel_license'] === '' || mb_strlen($values['travel_license']) > 120) {
            $errors[] = 'Số giấy phép lữ hành không hợp lệ.';
        }

        foreach (['facebook_url', 'messenger_url', 'youtube_url', 'zalo_url', 'office_hcm_map_url', 'office_hanoi_map_url', 'office_danang_map_url'] as $key) {
            $url = $values[$key];
            if (filter_var($url, FILTER_VALIDATE_URL) === false || ! str_starts_with(strtolower($url), 'https://')) {
                $errors[] = 'Các liên kết mạng xã hội và bản đồ phải là URL HTTPS đầy đủ.';
                break;
            }
        }

        foreach (['office_hcm_address_vi', 'office_hcm_address_en', 'office_hanoi_address_vi', 'office_hanoi_address_en', 'office_danang_address_vi', 'office_danang_address_en'] as $key) {
            if ($values[$key] === '' || mb_strlen($values[$key]) > 300) {
                $errors[] = 'Địa chỉ văn phòng không được để trống và tối đa 300 ký tự.';
                break;
            }
        }

        return $errors;
    }
}
