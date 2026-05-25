<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\LocationModel;
use App\Services\DomesticRegionService;
use App\Services\AdminAccessService;
use App\Services\AuthSessionControlService;
use App\Services\RememberLoginService;

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
        if (is_array($authUser) && ! empty($authUser['id']) && ! $sessionControl->isSessionUserValid($authUser)) {
            (new RememberLoginService())->clear();
            session()->remove(['auth_user', 'checkout_mode']);
            $authUser = null;
        }

        if (! is_array($authUser) || empty($authUser['id'])) {
            $rememberedUser = (new RememberLoginService())->restoreUser();
            if (is_array($rememberedUser) && ! empty($rememberedUser['id'])) {
                $authUser = $this->buildAuthSessionUser($rememberedUser);
                session()->set('auth_user', $authUser);
            }
        }

        $locationModel = new LocationModel();
        $menu = $locationModel->getMegaMenu($locale);
        $domesticRegionService = new DomesticRegionService();
        $domesticMenu = $domesticRegionService->getMenu($locale);
        $isAdminUser = (new AdminAccessService())->isAdmin(is_array($authUser) ? $authUser : null);

        service('renderer')->setVar('menu', $menu);
        service('renderer')->setVar('domesticMenu', $domesticMenu);
        service('renderer')->setVar('authUser', is_array($authUser) ? $authUser : null);
        service('renderer')->setVar('isAdminUser', $isAdminUser);
        service('renderer')->setVar('currentLocale', $locale);
        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    protected function buildAuthSessionUser(array $user): array
    {
        $isAdmin = array_key_exists('is_admin', $user)
            ? (bool) $user['is_admin']
            : (new AdminAccessService())->isAdmin($user);

        return [
            'id' => (int) ($user['id'] ?? 0),
            'full_name' => (string) ($user['full_name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'username' => (string) ($user['username'] ?? ''),
            'phone' => (string) ($user['phone'] ?? ''),
            'is_admin' => $isAdmin,
            'auth_session_version' => (new AuthSessionControlService())->buildSessionVersion($user),
        ];
    }
}
