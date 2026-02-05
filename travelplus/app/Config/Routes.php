<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Sitemap for SEO
$routes->get('sitemap.xml', 'Sitemap::index');

// Localized routes - first segment MAY be locale (en/vi)
$routes->get('(:segment)', 'Home::index', ['filter' => 'setlocale']);
// localized sitemap
$routes->get('(:segment)/sitemap.xml', 'Sitemap::index', ['filter' => 'setlocale']);

$routes->group('(:segment)', ['filter' => 'setlocale'], function ($routes) {
    $routes->get('/', 'Home::index');
});

$routes->group('(:segment)', ['filter' => 'setlocale'], function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('test-db', 'Test::db');
});

$routes->get('api/destinations', 'Api\Destination::search');
