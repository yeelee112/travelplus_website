<?php
header('Content-Type: text/plain; charset=utf-8');
echo 'php_ini_loaded_file=' . (php_ini_loaded_file() ?: '(none)') . PHP_EOL;
echo 'curl.cainfo=' . ini_get('curl.cainfo') . PHP_EOL;
echo 'openssl.cafile=' . ini_get('openssl.cafile') . PHP_EOL;
echo 'curl_loaded=' . (extension_loaded('curl') ? 'yes' : 'no') . PHP_EOL;
echo 'openssl_loaded=' . (extension_loaded('openssl') ? 'yes' : 'no') . PHP_EOL;
