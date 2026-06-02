<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root . DIRECTORY_SEPARATOR . 'public/assets/css/style.css';
$target = $root . DIRECTORY_SEPARATOR . 'public/assets/css/style.min.css';

if (! is_file($source)) {
    fwrite(STDERR, "Missing source CSS: {$source}" . PHP_EOL);
    exit(1);
}

$css = file_get_contents($source);
if ($css === false) {
    fwrite(STDERR, "Unable to read source CSS: {$source}" . PHP_EOL);
    exit(1);
}

$originalBytes = strlen($css);

$css = preg_replace('~/\*[^*]*\*+(?:[^/*][^*]*\*+)*/~', '', $css) ?? $css;
$css = preg_replace('/\s+/', ' ', $css) ?? $css;
$css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css) ?? $css;
$css = preg_replace('/;}/', '}', $css) ?? $css;
$css = trim($css);

if (file_put_contents($target, $css) === false) {
    fwrite(STDERR, "Unable to write minified CSS: {$target}" . PHP_EOL);
    exit(1);
}

$targetBytes = strlen($css);
$saved = $originalBytes > 0 ? round((1 - ($targetBytes / $originalBytes)) * 100, 1) : 0;

echo sprintf(
    "Minified %s -> %s (%s -> %s bytes, saved %s%%)%s",
    $source,
    $target,
    number_format($originalBytes),
    number_format($targetBytes),
    $saved,
    PHP_EOL
);
