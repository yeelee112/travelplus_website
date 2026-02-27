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

});




