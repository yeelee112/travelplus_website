<?php
$errorCode = isset($code) ? (int) $code : 500;
$errorKey = match ($errorCode) {
    400 => 'bad_request',
    401, 403 => 'forbidden',
    404 => 'not_found',
    429 => 'rate_limit',
    502, 503, 504 => 'maintenance',
    default => 'server',
};
$technicalMessage = '';
require __DIR__ . DIRECTORY_SEPARATOR . '_travelplus.php';
