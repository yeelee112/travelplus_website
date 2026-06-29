<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\LocationModel;
use App\Services\DomesticRegionService;
use App\Services\AdminAccessService;
use App\Services\AnalyticsTrackingService;
use App\Services\AuthSessionControlService;
use App\Services\DatabaseAvailabilityService;
use App\Services\RememberLoginService;
use Throwable;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
        $locale = $request->getLocale() ?: 'vi';
        $sessionControl = new AuthSessionControlService();

        $authUser = session()->get('auth_user');
        try {
            if (is_array($authUser) && ! empty($authUser['id']) && ! $sessionControl->isSessionUserValid($authUser)) {
                (new RememberLoginService())->clear();
                session()->remove(['auth_user', 'checkout_mode']);
                $authUser = null;
            }
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Auth session validation failed');
        }

        if ((! is_array($authUser) || empty($authUser['id'])) && ! DatabaseAvailabilityService::isUnavailable()) {
            try {
                $rememberedUser = (new RememberLoginService())->restoreUser();
                if (is_array($rememberedUser) && ! empty($rememberedUser['id'])) {
                    $authUser = $this->buildAuthSessionUser($rememberedUser);
                    session()->set('auth_user', $authUser);
                }
            } catch (Throwable $exception) {
                DatabaseAvailabilityService::markUnavailable($exception, 'Remember login restore failed');
            }
        }

        $path = '/' . trim((string) $request->getUri()->getPath(), '/');
        $isApiEndpoint = preg_match('#^/api(/|$)#i', $path) === 1;
        $usesSiteNavigation = ! preg_match('#^/(admin|api)(/|$)#i', $path);
        $menu = [];
        $domesticMenu = [];

        if ($usesSiteNavigation) {
            if (! DatabaseAvailabilityService::isUnavailable()) {
                try {
                    $locationModel = new LocationModel();
                    $menu = $locationModel->getMegaMenu($locale);
                } catch (Throwable $exception) {
                    DatabaseAvailabilityService::markUnavailable($exception, 'Site navigation load failed');
                }
            }

            try {
                $domesticRegionService = new DomesticRegionService();
                $domesticMenu = $domesticRegionService->getMenu($locale);
            } catch (Throwable $exception) {
                DatabaseAvailabilityService::markUnavailable($exception, 'Domestic navigation load failed');
            }
        }

        try {
            $isAdminUser = (new AdminAccessService())->isAdmin(is_array($authUser) ? $authUser : null);
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Admin access check failed');
            $isAdminUser = false;
        }

        service('renderer')->setVar('menu', $menu);
        service('renderer')->setVar('domesticMenu', $domesticMenu);
        service('renderer')->setVar('authUser', is_array($authUser) ? $authUser : null);
        service('renderer')->setVar('isAdminUser', $isAdminUser);
        service('renderer')->setVar('currentLocale', $locale);

        if (! $isApiEndpoint && ! DatabaseAvailabilityService::isUnavailable()) {
            try {
                (new AnalyticsTrackingService())->track($request, static::class, is_array($authUser) ? $authUser : null);
            } catch (Throwable $exception) {
                DatabaseAvailabilityService::markUnavailable($exception, 'Analytics tracking failed');
            }
        }
        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function buildAuthSessionUser(array $user): array
    {
        try {
            $isAdmin = array_key_exists('is_admin', $user)
                ? (bool) $user['is_admin']
                : (new AdminAccessService())->isAdmin($user);
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Auth session admin flag failed');
            $isAdmin = false;
        }

        try {
            $authSessionVersion = DatabaseAvailabilityService::isUnavailable()
                ? 0
                : (new AuthSessionControlService())->buildSessionVersion($user);
        } catch (Throwable $exception) {
            DatabaseAvailabilityService::markUnavailable($exception, 'Auth session version build failed');
            $authSessionVersion = 0;
        }

        return [
            'id' => (int) ($user['id'] ?? 0),
            'full_name' => (string) ($user['full_name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
            'phone' => (string) ($user['phone'] ?? ''),
            'is_admin' => $isAdmin,
            'auth_session_version' => $authSessionVersion,
        ];
    }
}
