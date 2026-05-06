<?php
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setAutoRoute(false);

/*
|--------------------------------------------------------------------------
| Default language (VI)
|--------------------------------------------------------------------------
*/

$routes->GET('/', 'Home::index');
$routes->GET('api/destinations', 'Api\Destination::search');
$routes->GET('ve-chung-toi', 'AboutUs::index');
$routes->GET('cam-hung-du-lich', 'Blog::index');
$routes->GET('dich-vu-visa', 'Visa::index');
$routes->GET('dieu-khoan-su-dung', 'LegalController::terms/vi');
$routes->GET('chinh-sach-bao-mat', 'LegalController::privacy/vi');
$routes->GET('tim-kiem-tour', 'SearchController::tours');

$routes->GET('admin/tours/create', 'Admin\Tours::create');
$routes->POST('admin/tours', 'Admin\Tours::store');

$routes->match(['GET', 'POST'], 'account/register', 'AuthController::register');
$routes->POST('auth/login', 'AuthController::login');
$routes->GET('auth/logout', 'AuthController::logout');
$routes->GET('auth/google', 'AuthController::google');
$routes->GET('auth/google/callback', 'AuthController::googleCallback');
$routes->POST('booking/proceed', 'BookingController::proceed');
$routes->match(['GET', 'POST'], 'booking/guest', 'BookingController::continueGuest');
$routes->GET('booking/checkout', 'BookingController::checkout');
$routes->GET('booking/success/(:segment)', 'BookingController::success/$1');
$routes->POST('booking/paypal/create-order', 'BookingController::createPayPalOrder');
$routes->POST('booking/vietqr/generate', 'BookingController::generateVietQr');
$routes->POST('booking/vietqr/complete', 'BookingController::completeVietQr');
$routes->GET('booking/paypal/return', 'BookingController::paypalReturn');
$routes->GET('booking/paypal/cancel', 'BookingController::paypalCancel');

$routes->GET('tour-nuoc-ngoai', 'Outbound::index');
$routes->GET('tour-trong-nuoc', 'Domestic::index');
$routes->GET('tour-preview', 'TourController::preview');
$routes->POST('tour/reviews', 'TourController::submitReview');
$routes->get('tour-nuoc-ngoai/(:segment)/(:segment)', 'TourController::detail/outbound/vi/$1/$2');
$routes->get('tour-trong-nuoc/(:segment)/tour/(:segment)', 'TourController::detail/inbound/vi/$1/$2');
$routes->get('tour-trong-nuoc/(:segment)', 'Domestic::region/vi/$1');
$routes->get('tour-trong-nuoc/(:segment)/(:segment)', 'Domestic::province/vi/$1/$2');



$routes->match(['GET','POST'], 'contact', 'Contact::index');




/*
|--------------------------------------------------------------------------
| English prefix
|--------------------------------------------------------------------------
*/

$routes->group('en', function ($routes) {
    $routes->GET('/', 'Home::index');
    $routes->GET('ve-chung-toi', 'AboutUs::index');
    $routes->match(['GET','POST'], 'contact', 'Contact::index');
    $routes->GET('terms-of-service', 'LegalController::terms/en');
    $routes->GET('privacy-statement', 'LegalController::privacy/en');
    $routes->GET('tour-search', 'SearchController::tours');

    $routes->match(['GET', 'POST'], 'account/register', 'AuthController::register');
    $routes->POST('auth/login', 'AuthController::login');
    $routes->GET('auth/logout', 'AuthController::logout');
    $routes->GET('auth/google', 'AuthController::google');
    $routes->GET('auth/google/callback', 'AuthController::googleCallback');
    $routes->POST('booking/proceed', 'BookingController::proceed');
    $routes->match(['GET', 'POST'], 'booking/guest', 'BookingController::continueGuest');
    $routes->GET('booking/checkout', 'BookingController::checkout');
    $routes->GET('booking/success/(:segment)', 'BookingController::success/$1');
    $routes->POST('booking/paypal/create-order', 'BookingController::createPayPalOrder');
    $routes->POST('booking/vietqr/generate', 'BookingController::generateVietQr');
    $routes->POST('booking/vietqr/complete', 'BookingController::completeVietQr');
    $routes->GET('booking/paypal/return', 'BookingController::paypalReturn');
    $routes->GET('booking/paypal/cancel', 'BookingController::paypalCancel');

    
    $routes->GET('tour-nuoc-ngoai', 'Outbound::index');
    $routes->GET('tour-trong-nuoc', 'Domestic::index');
    $routes->GET('tour-preview', 'TourController::preview');
    $routes->POST('tour/reviews', 'TourController::submitReview');
    $routes->get('tour-nuoc-ngoai/(:segment)/(:segment)', 'TourController::detail/outbound/en/$1/$2');
    $routes->get('tour-trong-nuoc/(:segment)/tour/(:segment)', 'TourController::detail/inbound/en/$1/$2');
    $routes->get('tour-trong-nuoc/(:segment)', 'Domestic::region/en/$1');
    $routes->get('tour-trong-nuoc/(:segment)/(:segment)', 'Domestic::province/en/$1/$2');
    $routes->get('(:segment)', 'LocationController::continent/en/$1');
    $routes->get('(:segment)/(:segment)', 'LocationController::country/en/$1/$2');
    $routes->get('(:segment)/(:segment)/(:segment)', 'LocationController::province/en/$1/$2/$3');

});


$routes->get('(:segment)', 'LocationController::continent/vi/$1');
$routes->get('(:segment)/(:segment)', 'LocationController::country/vi/$1/$2');
$routes->get('(:segment)/(:segment)/(:segment)', 'LocationController::province/vi/$1/$2/$3');
