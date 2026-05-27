<?php
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setAutoRoute(false);

$routes->GET('sitemap.xml', 'Sitemap::index');

/*
|--------------------------------------------------------------------------
| Default language (VI)
|--------------------------------------------------------------------------
*/

$routes->GET('/', 'Home::index');
$routes->GET('api/destinations', 'Api\Destination::search');
$routes->POST('api/ai-chat', 'Api\ChatController::message');
$routes->GET('ve-chung-toi', 'AboutUs::index');
$routes->GET('cam-hung-du-lich', 'Blog::index');
$routes->GET('cam-hung-du-lich/(:segment)', 'Blog::show/$1');
$routes->GET('dich-vu-visa', 'Visa::index');
$routes->GET('dich-vu-mice', 'Mice::index');
$routes->GET('dich-vu-ve-may-bay', 'Services::airlineTickets');
$routes->GET('dich-vu-van-chuyen', 'Services::transport');
$routes->GET('dich-vu-dich-thuat', 'Services::translation');
$routes->GET('dich-vu-khach-san', 'Services::hotels');
$routes->GET('dieu-khoan-su-dung', 'LegalController::terms/vi');
$routes->GET('chinh-sach-bao-mat', 'LegalController::privacy/vi');
$routes->GET('tim-kiem-tour', 'SearchController::tours');

$routes->GET('admin', 'Admin\Dashboard::index');
$routes->GET('admin/bookings', 'Admin\Bookings::index');
$routes->GET('admin/bookings/export', 'Admin\Bookings::exportCsv');
$routes->GET('admin/bookings/(:num)', 'Admin\Bookings::show/$1');
$routes->POST('admin/bookings/(:num)/status', 'Admin\Bookings::updateStatus/$1');
$routes->GET('admin/tours', 'Admin\Tours::index');
$routes->GET('admin/tours/create', 'Admin\Tours::create');
$routes->POST('admin/tours', 'Admin\Tours::store');
$routes->GET('admin/tours/(:num)/edit', 'Admin\Tours::edit/$1');
$routes->POST('admin/tours/(:num)', 'Admin\Tours::update/$1');
$routes->POST('admin/tours/(:num)/delete', 'Admin\Tours::delete/$1');
$routes->GET('admin/blogs', 'Admin\Blogs::index');
$routes->GET('admin/blogs/create', 'Admin\Blogs::create');
$routes->POST('admin/blogs', 'Admin\Blogs::store');
$routes->GET('admin/blogs/(:num)/edit', 'Admin\Blogs::edit/$1');
$routes->POST('admin/blogs/(:num)', 'Admin\Blogs::update/$1');
$routes->POST('admin/blogs/(:num)/status', 'Admin\Blogs::updateStatus/$1');
$routes->POST('admin/blogs/(:num)/delete', 'Admin\Blogs::delete/$1');
$routes->POST('admin/blogs/upload-image', 'Admin\Blogs::uploadEditorImage');
$routes->GET('admin/reviews', 'Admin\Reviews::index');
$routes->GET('admin/reviews/(:num)', 'Admin\Reviews::show/$1');
$routes->POST('admin/reviews/(:num)/status', 'Admin\Reviews::updateStatus/$1');
$routes->POST('admin/reviews/(:num)/delete', 'Admin\Reviews::delete/$1');
$routes->GET('admin/media-audit', 'Admin\MediaAudit::index');
$routes->POST('admin/media-audit/delete-orphans', 'Admin\MediaAudit::deleteOrphans');
$routes->GET('admin/users', 'Admin\Users::index');
$routes->GET('admin/users/create', 'Admin\Users::create');
$routes->POST('admin/users', 'Admin\Users::store');
$routes->GET('admin/users/(:num)/edit', 'Admin\Users::edit/$1');
$routes->POST('admin/users/(:num)', 'Admin\Users::update/$1');

$routes->match(['GET', 'POST'], 'account/register', 'AuthController::register');
$routes->match(['GET', 'POST'], 'account/login', 'AuthController::login');
  $routes->match(['GET', 'POST'], 'account/forgot-password', 'AuthController::forgotPassword');
  $routes->match(['GET', 'POST'], 'account/reset-password/(:segment)', 'AuthController::resetPassword/$1');
  $routes->match(['GET', 'POST'], 'account/profile', 'AuthController::profile');
  $routes->POST('account/logout-all', 'AuthController::logoutAllDevices');
$routes->POST('auth/logout', 'AuthController::logout');
$routes->GET('auth/google', 'AuthController::google');
$routes->GET('auth/google/callback', 'AuthController::googleCallback');
$routes->POST('booking/proceed', 'BookingController::proceed');
$routes->match(['GET', 'POST'], 'booking/guest', 'BookingController::continueGuest');
$routes->GET('booking/checkout', 'BookingController::checkout');
$routes->GET('booking/success/(:segment)', 'BookingController::success/$1');
$routes->POST('booking/paypal/create-order', 'BookingController::createPayPalOrder');
$routes->POST('booking/vnpay/create-payment', 'BookingController::createVnpayPayment');
$routes->POST('booking/vietqr/generate', 'BookingController::generateVietQr');
$routes->POST('booking/vietqr/complete', 'BookingController::completeVietQr');
$routes->GET('booking/paypal/return', 'BookingController::paypalReturn');
$routes->GET('booking/paypal/cancel', 'BookingController::paypalCancel');
$routes->GET('booking/vnpay/return', 'BookingController::vnpayReturn');
$routes->GET('booking/vnpay/ipn', 'BookingController::vnpayIpn');

$routes->GET('tour-nuoc-ngoai', 'Outbound::index');
$routes->GET('tour-trong-nuoc', 'Domestic::index');
$routes->GET('tour-preview', 'TourController::preview');
$routes->POST('tour/reviews', 'TourController::submitReview');
$routes->POST('tour/enquiry', 'TourController::submitEnquiry');
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
    $routes->GET('travel-inspiration', 'Blog::index');
    $routes->GET('travel-inspiration/(:segment)', 'Blog::show/$1');
    $routes->GET('dich-vu-visa', 'Visa::index');
    $routes->GET('dich-vu-mice', 'Mice::index');
    $routes->GET('airline-ticket-service', 'Services::airlineTickets');
    $routes->GET('transport-service', 'Services::transport');
    $routes->GET('translation-service', 'Services::translation');
    $routes->GET('hotel-service', 'Services::hotels');
    $routes->match(['GET','POST'], 'contact', 'Contact::index');
    $routes->GET('terms-of-service', 'LegalController::terms/en');
    $routes->GET('privacy-statement', 'LegalController::privacy/en');
    $routes->GET('tour-search', 'SearchController::tours');

    $routes->match(['GET', 'POST'], 'account/register', 'AuthController::register');
    $routes->match(['GET', 'POST'], 'account/login', 'AuthController::login');
    $routes->match(['GET', 'POST'], 'account/forgot-password', 'AuthController::forgotPassword');
    $routes->match(['GET', 'POST'], 'account/reset-password/(:segment)', 'AuthController::resetPassword/$1');
    $routes->match(['GET', 'POST'], 'account/profile', 'AuthController::profile');
    $routes->POST('account/logout-all', 'AuthController::logoutAllDevices');
    $routes->POST('auth/logout', 'AuthController::logout');
    $routes->GET('auth/google', 'AuthController::google');
    $routes->GET('auth/google/callback', 'AuthController::googleCallback');
    $routes->POST('booking/proceed', 'BookingController::proceed');
    $routes->match(['GET', 'POST'], 'booking/guest', 'BookingController::continueGuest');
    $routes->GET('booking/checkout', 'BookingController::checkout');
    $routes->GET('booking/success/(:segment)', 'BookingController::success/$1');
    $routes->POST('booking/paypal/create-order', 'BookingController::createPayPalOrder');
    $routes->POST('booking/vnpay/create-payment', 'BookingController::createVnpayPayment');
    $routes->POST('booking/vietqr/generate', 'BookingController::generateVietQr');
    $routes->POST('booking/vietqr/complete', 'BookingController::completeVietQr');
    $routes->GET('booking/paypal/return', 'BookingController::paypalReturn');
    $routes->GET('booking/paypal/cancel', 'BookingController::paypalCancel');
    $routes->GET('booking/vnpay/return', 'BookingController::vnpayReturn');
    $routes->GET('booking/vnpay/ipn', 'BookingController::vnpayIpn');

    
    $routes->GET('tour-nuoc-ngoai', 'Outbound::index');
    $routes->GET('tour-trong-nuoc', 'Domestic::index');
    $routes->GET('tour-preview', 'TourController::preview');
    $routes->POST('tour/reviews', 'TourController::submitReview');
    $routes->POST('tour/enquiry', 'TourController::submitEnquiry');
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
