<?php
$errorCode = 403;
$errorKey = 'forbidden';
$technicalMessage = ENVIRONMENT !== 'production' ? (string) ($message ?? '') : '';
require __DIR__ . DIRECTORY_SEPARATOR . '_travelplus.php';
