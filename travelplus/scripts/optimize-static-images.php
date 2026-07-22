<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Services\ImageOptimizationService;

$root = dirname(__DIR__);
$images = [
    ['public/assets/images/mice-1.jpeg', 1600],
    ['public/assets/images/mice-2.jpg', 960],
    ['public/assets/images/mice-3.jpg', 960],
    ['public/assets/images/mice-corporate-travel.jpg', 1600],
    ['public/assets/images/visa-wrapper.jpg', 1600],
    ['public/assets/images/avt-tour-01.jpg', 1200],
    ['public/assets/images/avt-tour-02.jpg', 1200],
    ['public/assets/images/destination/anh.jpg', 1200],
    ['public/assets/images/destination/canada.jpg', 1200],
    ['public/assets/images/destination/da-lat.png', 1200],
    ['public/assets/images/destination/nhat-ban.jpg', 1200],
    ['public/assets/images/destination/nha-trang.png', 1200],
    ['public/assets/images/destination/sa-pa.jpg', 1200],
    ['public/assets/images/destination/uae.png', 1200],
];

$optimizer = new ImageOptimizationService();
$failed = false;

foreach ($images as [$relativePath, $maxDimension]) {
    $sourcePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $outputPath = preg_replace('/\.(?:jpe?g|png)$/i', '.webp', $sourcePath) ?: '';

    if (! is_file($sourcePath)) {
        $failed = true;
        fwrite(STDERR, "fail\t{$relativePath}\tSource image does not exist." . PHP_EOL);
        continue;
    }

    if ($outputPath !== '' && is_file($outputPath) && filemtime($outputPath) >= filemtime($sourcePath)) {
        fwrite(STDOUT, "skip\t{$relativePath}" . PHP_EOL);
        continue;
    }

    $result = $optimizer->optimizeToWebp($sourcePath, $maxDimension, $maxDimension, 82, false);
    if (! $result['success'] || ! is_file((string) $result['output_path'])) {
        $failed = true;
        fwrite(STDERR, "fail\t{$relativePath}\t{$result['error']}" . PHP_EOL);
        continue;
    }

    $savedBytes = max(0, $result['original_bytes'] - $result['output_bytes']);
    fwrite(STDOUT, "ok\t{$relativePath}\t-{$savedBytes} bytes" . PHP_EOL);
}

$responsiveImages = [
    'public/assets/images/home/banner01.webp',
    'public/assets/images/home/banner02.webp',
    'public/assets/images/home/banner03.webp',
];

foreach ($responsiveImages as $relativePath) {
    $sourcePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $variants = $optimizer->generateResponsiveVariants($sourcePath, [768, 1280, 1600], 78);

    if (count($variants) !== 3) {
        $failed = true;
        fwrite(STDERR, "fail\t{$relativePath}\tResponsive variants could not be generated." . PHP_EOL);
        continue;
    }

    fwrite(STDOUT, "responsive\t{$relativePath}\t" . count($variants) . " variants" . PHP_EOL);
}

exit($failed ? 1 : 0);
