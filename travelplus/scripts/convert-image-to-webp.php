<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Services\ImageOptimizationService;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php scripts/convert-image-to-webp.php <source> <destination> [quality]\n");
    exit(1);
}

$source = (string) $argv[1];
$destination = (string) $argv[2];
$quality = max(1, min(100, (int) ($argv[3] ?? 82)));

if (! is_file($source)) {
    fwrite(STDERR, "Source image not found: {$source}\n");
    exit(1);
}

$destinationDirectory = dirname($destination);
if (! is_dir($destinationDirectory) && ! mkdir($destinationDirectory, 0775, true) && ! is_dir($destinationDirectory)) {
    fwrite(STDERR, "Unable to create destination directory: {$destinationDirectory}\n");
    exit(1);
}

$temporarySource = $source;
$expectedDestination = preg_replace('/\.(?:jpe?g|png|webp)$/i', '.webp', $source) ?: ($source . '.webp');
$result = (new ImageOptimizationService())->optimizeToWebp($source, 10000, 10000, $quality, false);

if (! $result['success']) {
    fwrite(STDERR, "Unable to optimize image: {$result['error']}\n");
    exit(1);
}

if ($expectedDestination !== $destination) {
    if (! @copy((string) $result['output_path'], $destination)) {
        fwrite(STDERR, "Unable to copy WebP image to: {$destination}\n");
        exit(1);
    }

    if ((string) $result['output_path'] !== $temporarySource) {
        @unlink((string) $result['output_path']);
    }
}

fwrite(STDOUT, sprintf(
    "Converted %s -> %s (%d bytes, quality %d)\n",
    $source,
    $destination,
    (int) (filesize($destination) ?: 0),
    $quality
));
