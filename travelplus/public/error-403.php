<?php

declare(strict_types=1);

http_response_code(403);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex, nofollow');
header('Content-Type: text/html; charset=UTF-8');

// Keep asset and navigation URLs correct whether public/ is the document root
// or the application is deployed with /public visible in the URL.
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/error-403.php'));
$_SERVER['SCRIPT_NAME'] = preg_replace('#/error-403\.php$#', '/index.php', $scriptName) ?? '/index.php';

$errorCode = 403;
$errorKey = 'forbidden';
$technicalMessage = '';

require dirname(__DIR__) . '/app/Views/errors/html/_travelplus.php';
