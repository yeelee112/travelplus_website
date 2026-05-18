<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Data\LocalizedPathCatalog;
use App\Services\AdminAccessService;
use CodeIgniter\HTTP\RedirectResponse;

abstract class BaseAdminController extends BaseController
{
    protected function requireAdmin(): ?RedirectResponse
    {
        $locale = $this->request->getLocale() === 'en' ? 'en' : 'vi';
        $authUser = session()->get('auth_user');

        if (! is_array($authUser) || empty($authUser['id'])) {
            session()->setFlashdata('auth_error', lang('Frontend.admin.loginRequired', [], $locale));

            return redirect()->to(LocalizedPathCatalog::url('auth.login', $locale));
        }

        $isAdmin = (new AdminAccessService())->isAdmin($authUser);

        if (! $isAdmin) {
            session()->setFlashdata('auth_error', lang('Frontend.admin.accessDenied', [], $locale));

            return redirect()->to(LocalizedPathCatalog::url('auth.profile', $locale));
        }

        return null;
    }
}
