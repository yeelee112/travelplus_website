<?php
$_ENV['app.baseURL'] = $_SERVER['app.baseURL'] = 'http://127.0.0.1:8097/';
$_ENV['app.forceGlobalSecureRequests'] = $_SERVER['app.forceGlobalSecureRequests'] = 'false';
return require dirname(__DIR__) . '/vendor/codeigniter4/framework/system/rewrite.php';
