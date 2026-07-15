<?php
$errorCode = 404;
$errorKey = 'not_found';
$technicalMessage = ENVIRONMENT !== 'production' ? (string) ($message ?? '') : '';
require __DIR__ . DIRECTORY_SEPARATOR . '_travelplus.php';
