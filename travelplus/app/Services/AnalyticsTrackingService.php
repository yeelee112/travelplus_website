<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RequestInterface;
use Throwable;

class AnalyticsTrackingService
{
    private const VISITOR_TOKEN_KEY = 'analytics_visitor_token';
    private const VISIT_TOKEN_KEY = 'analytics_visit_token';
    private const VISIT_ID_KEY = 'analytics_visit_id';
    private const LAST_SEEN_KEY = 'analytics_last_seen_at';
    private const VISIT_TIMEOUT_SECONDS = 1800;
    private const SCHEMA_CACHE_KEY = 'analytics_schema_ready_v1';
    private const SCHEMA_READY_TTL = 3600;
    private const SCHEMA_MISSING_TTL = 300;

    private ?BaseConnection $db = null;
    private static ?bool $analyticsTablesReady = null;

    public function track(RequestInterface $request, string $controllerClass, ?array $authUser = null): void
    {
        if (DatabaseAvailabilityService::isUnavailable()) {
            return;
        }

        try {
            if (! (new CookieConsentService())->allowsAnalytics($request)) {
                return;
            }

            if (! $this->shouldTrack($request, $controllerClass)) {
                return;
            }

            if (! $this->isAnalyticsReady()) {
                return;
            }

            $session = session();
            $now = date('Y-m-d H:i:s');
            $path = $this->normalizePath((string) $request->getUri()->getPath());
            $fullUrl = (string) $request->getUri();
            $referrer = trim((string) ($request->getServer('HTTP_REFERER') ?? ''));
            $visitorToken = (string) $session->get(self::VISITOR_TOKEN_KEY);
            $userId = (int) (($authUser['id'] ?? 0) ?: 0) ?: null;

            if ($visitorToken === '') {
                $visitorToken = bin2hex(random_bytes(16));
                $session->set(self::VISITOR_TOKEN_KEY, $visitorToken);
            }

            $lastSeenAt = strtotime((string) $session->get(self::LAST_SEEN_KEY));
            $visitToken = (string) $session->get(self::VISIT_TOKEN_KEY);
            $visitId = (int) $session->get(self::VISIT_ID_KEY);
            $hasActiveWindow = $lastSeenAt !== false
                && (time() - $lastSeenAt) <= self::VISIT_TIMEOUT_SECONDS;

            // Existing sessions created before VISIT_ID_KEY was introduced need
            // one lookup; subsequent page views reuse the ID stored in session.
            if ($visitToken !== '' && $visitId <= 0 && $hasActiveWindow) {
                $visitId = $this->findVisitId($visitToken);
            }

            $visitPayload = [
                'visitor_token' => $visitorToken,
                'user_id' => $userId,
                'landing_path' => $path,
                'landing_url' => $fullUrl,
                'last_path' => $path,
                'referrer' => $referrer !== '' ? substr($referrer, 0, 255) : null,
                'locale' => (string) ($request->getLocale() ?: 'vi'),
                'pageviews' => 1,
                'is_bounce' => 1,
                'started_at' => $now,
                'last_seen_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $needNewVisit = $visitToken === '' || $visitId <= 0 || ! $hasActiveWindow;

            if ($needNewVisit) {
                $visitToken = bin2hex(random_bytes(12));
                $visitPayload['visit_token'] = $visitToken;
                $visitId = $this->createVisit($visitPayload);
            } else {
                $this->db()->table('analytics_visits')
                    ->set('pageviews', 'COALESCE(pageviews, 0) + 1', false)
                    ->set([
                        'user_id' => $userId,
                        'last_path' => $path,
                        'last_seen_at' => $now,
                        'updated_at' => $now,
                        'is_bounce' => 0,
                    ])
                    ->where('id', $visitId)
                    ->where('visit_token', $visitToken)
                    ->update();

                // The row may have been pruned while the browser session remained.
                if ($this->db()->affectedRows() <= 0) {
                    $visitToken = bin2hex(random_bytes(12));
                    $visitPayload['visit_token'] = $visitToken;
                    $visitId = $this->createVisit($visitPayload);
                }
            }

            if ($visitId <= 0) {
                return;
            }

            $this->db()->table('analytics_page_views')->insert([
                'visit_id' => $visitId,
                'visitor_token' => $visitorToken,
                'user_id' => $userId,
                'path' => $path,
                'full_url' => $fullUrl,
                'page_type' => $this->resolvePageType($controllerClass, $path),
                'referrer' => $referrer !== '' ? substr($referrer, 0, 255) : null,
                'locale' => (string) ($request->getLocale() ?: 'vi'),
                'viewed_at' => $now,
                'created_at' => $now,
            ]);

            $session->set(self::VISIT_TOKEN_KEY, $visitToken);
            $session->set(self::VISIT_ID_KEY, $visitId);
            $session->set(self::LAST_SEEN_KEY, $now);
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Analytics tracking failed');
        }
    }

    private function shouldTrack(RequestInterface $request, string $controllerClass): bool
    {
        if (strtolower($request->getMethod()) !== 'get' || $request->isAJAX()) {
            return false;
        }

        if ($this->isAutomatedClient((string) ($request->getServer('HTTP_USER_AGENT') ?? ''))) {
            return false;
        }

        $path = $this->normalizePath((string) $request->getUri()->getPath());

        if (preg_match('#^/(admin|api|assets|uploads)(/|$)#i', $path)) {
            return false;
        }

        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|webp|svg|ico|map|xml|txt|woff|woff2)$/i', $path)) {
            return false;
        }

        if (in_array($controllerClass, [
            'App\\Controllers\\Home',
            'App\\Controllers\\SearchController',
            'App\\Controllers\\SummerTours',
            'App\\Controllers\\TourController',
            'App\\Controllers\\Contact',
        ], true)) {
            return true;
        }

        if ($controllerClass === 'App\\Controllers\\Blog') {
            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            return count($segments) >= 2;
        }

        return false;
    }

    private function isAnalyticsReady(): bool
    {
        if (self::$analyticsTablesReady !== null) {
            return self::$analyticsTablesReady;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            self::$analyticsTablesReady = false;

            return false;
        }

        try {
            $cached = cache()->get(self::SCHEMA_CACHE_KEY);
            if ($cached === 1 || $cached === '1') {
                self::$analyticsTablesReady = true;

                return true;
            }
            if ($cached === 0 || $cached === '0') {
                self::$analyticsTablesReady = false;

                return false;
            }
        } catch (Throwable) {
        }

        try {
            self::$analyticsTablesReady = $this->db()->tableExists('analytics_visits')
                && $this->db()->tableExists('analytics_page_views');

            try {
                cache()->save(
                    self::SCHEMA_CACHE_KEY,
                    self::$analyticsTablesReady ? 1 : 0,
                    self::$analyticsTablesReady ? self::SCHEMA_READY_TTL : self::SCHEMA_MISSING_TTL
                );
            } catch (Throwable) {
            }
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Analytics schema check failed');
            self::$analyticsTablesReady = false;
        }

        return self::$analyticsTablesReady;
    }

    private function createVisit(array $payload): int
    {
        $this->db()->table('analytics_visits')->insert($payload);

        return (int) $this->db()->insertID();
    }

    private function findVisitId(string $visitToken): int
    {
        if ($visitToken === '') {
            return 0;
        }

        if (DatabaseAvailabilityService::isUnavailable()) {
            return 0;
        }

        try {
            $row = $this->db()->table('analytics_visits')
                ->select('id')
                ->where('visit_token', $visitToken)
                ->limit(1)
                ->get()
                ->getRowArray();

            return (int) ($row['id'] ?? 0);
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Analytics visit lookup failed');

            return 0;
        }
    }

    private function isAutomatedClient(string $userAgent): bool
    {
        $userAgent = trim($userAgent);
        if ($userAgent === '') {
            return false;
        }

        return preg_match(
            '/(?:bot|crawler|spider|slurp|headless|preview|facebookexternalhit|whatsapp|telegram|discord|ahrefs|semrush|mj12|dotbot|bytespider|gptbot|claudebot|ccbot)/i',
            $userAgent
        ) === 1;
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        if (preg_match('#^/(en)(/|$)#i', $path)) {
            $path = preg_replace('#^/en#i', '', $path) ?: '/';
        }

        return $path === '' ? '/' : $path;
    }

    private function resolvePageType(string $controllerClass, string $path): string
    {
        $map = [
            'App\\Controllers\\Home' => 'home',
            'App\\Controllers\\SearchController' => 'search',
            'App\\Controllers\\TourController' => 'tour_detail',
            'App\\Controllers\\Blog' => count(array_values(array_filter(explode('/', trim($path, '/'))))) >= 2 ? 'blog_detail' : 'blog',
            'App\\Controllers\\SummerTours' => 'summer_landing',
            'App\\Controllers\\Contact' => 'contact',
            'App\\Controllers\\BookingController' => 'booking',
            'App\\Controllers\\AboutUs' => 'about',
            'App\\Controllers\\Visa' => 'visa',
            'App\\Controllers\\Mice' => 'mice',
            'App\\Controllers\\Services' => 'services',
            'App\\Controllers\\Domestic' => 'domestic',
            'App\\Controllers\\Outbound' => 'outbound',
            'App\\Controllers\\LocationController' => 'location_listing',
            'App\\Controllers\\AuthController' => 'auth',
            'App\\Controllers\\LegalController' => 'legal',
        ];

        if (isset($map[$controllerClass])) {
            return (string) $map[$controllerClass];
        }

        return $path === '/' ? 'home' : 'page';
    }

    private function db(): BaseConnection
    {
        if ($this->db === null) {
            $this->db = db_connect();
        }

        return $this->db;
    }
}
