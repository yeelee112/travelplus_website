<?php
require 'vendor/autoload.php';
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Test/bootstrap.php';
$service = new App\Services\WebsiteKnowledgeService();
$ref = new ReflectionClass($service);
$method = $ref->getMethod('getDirectAnswer');
$method->setAccessible(true);
$result = $method->invoke($service, 'vi', 'có tour mỹ ko');
var_export($result);
