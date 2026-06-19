<?php

namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;

class CookieConsentService
{
    public const COOKIE_NAME = 'tp_cookie_consent';

    public function allowsAnalytics(RequestInterface $request): bool
    {
        return $this->hasCategory($request, 'analytics');
    }

    public function allowsMarketing(RequestInterface $request): bool
    {
        return $this->hasCategory($request, 'marketing');
    }

    private function hasCategory(RequestInterface $request, string $category): bool
    {
        $rawConsent = trim((string) $request->getCookie(self::COOKIE_NAME));

        if ($rawConsent === '') {
            return false;
        }

        $categories = array_filter(array_map('trim', explode(',', strtolower($rawConsent))));

        return in_array('all', $categories, true) || in_array(strtolower($category), $categories, true);
    }
}
