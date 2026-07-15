<?php

namespace App\Services;

use Throwable;

final class ImageOptimizationService
{
    private const MAX_SOURCE_PIXELS = 40_000_000;

    /**
     * @return array{success: bool, optimized: bool, source_path: string, output_path: string, original_bytes: int, output_bytes: int, error: string}
     */
    public function optimizeToWebp(
        string $sourcePath,
        int $maxWidth = 2400,
        int $maxHeight = 2400,
        int $quality = 82,
        bool $removeSource = false
    ): array {
        $result = [
            'success' => false,
            'optimized' => false,
            'source_path' => $sourcePath,
            'output_path' => $sourcePath,
            'original_bytes' => is_file($sourcePath) ? (int) (filesize($sourcePath) ?: 0) : 0,
            'output_bytes' => 0,
            'error' => '',
        ];

        if (! is_file($sourcePath)) {
            $result['error'] = 'Source image does not exist.';

            return $result;
        }

        if (! function_exists('imagewebp')) {
            $result['error'] = 'PHP GD WebP support is unavailable.';

            return $result;
        }

        $imageInfo = @getimagesize($sourcePath);
        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        $mimeType = strtolower((string) ($imageInfo['mime'] ?? ''));

        if ($width < 1 || $height < 1 || ($width * $height) > self::MAX_SOURCE_PIXELS) {
            $result['error'] = 'Image dimensions are invalid or too large to optimize safely.';

            return $result;
        }

        try {
            $sourceImage = match ($mimeType) {
                'image/jpeg' => @imagecreatefromjpeg($sourcePath),
                'image/png' => @imagecreatefrompng($sourcePath),
                'image/webp' => @imagecreatefromwebp($sourcePath),
                default => false,
            };

            if ($sourceImage === false) {
                $result['error'] = 'Unsupported or unreadable image format.';

                return $result;
            }

            if ($mimeType === 'image/jpeg') {
                $sourceImage = $this->applyExifOrientation($sourceImage, $sourcePath);
                $width = imagesx($sourceImage);
                $height = imagesy($sourceImage);
            }

            $maxWidth = max(320, $maxWidth);
            $maxHeight = max(320, $maxHeight);
            $scale = min(1, $maxWidth / $width, $maxHeight / $height);
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
            $outputImage = $sourceImage;

            if ($targetWidth !== $width || $targetHeight !== $height) {
                $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
                if ($resizedImage === false) {
                    imagedestroy($sourceImage);
                    $result['error'] = 'Unable to allocate the resized image.';

                    return $result;
                }

                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefill($resizedImage, 0, 0, $transparent);
                imagecopyresampled(
                    $resizedImage,
                    $sourceImage,
                    0,
                    0,
                    0,
                    0,
                    $targetWidth,
                    $targetHeight,
                    $width,
                    $height
                );
                $outputImage = $resizedImage;
            }

            $destinationPath = preg_replace('/\.(?:jpe?g|png|webp)$/i', '.webp', $sourcePath) ?: ($sourcePath . '.webp');
            $temporaryPath = $destinationPath . '.tmp-' . bin2hex(random_bytes(4));
            $quality = max(45, min(92, $quality));
            $written = @imagewebp($outputImage, $temporaryPath, $quality);

            if ($outputImage !== $sourceImage) {
                imagedestroy($outputImage);
            }
            imagedestroy($sourceImage);

            if (! $written || ! is_file($temporaryPath)) {
                @unlink($temporaryPath);
                $result['error'] = 'Unable to write the optimized WebP image.';

                return $result;
            }

            clearstatcache(true, $temporaryPath);
            $candidateBytes = (int) (filesize($temporaryPath) ?: 0);
            $wasResized = $targetWidth !== $width || $targetHeight !== $height;

            if ($candidateBytes < 1) {
                @unlink($temporaryPath);
                $result['error'] = 'The optimized WebP image is empty.';

                return $result;
            }

            if (! $wasResized && $candidateBytes >= $result['original_bytes']) {
                @unlink($temporaryPath);
                $result['success'] = true;
                $result['output_bytes'] = $result['original_bytes'];

                return $result;
            }

            if (! @copy($temporaryPath, $destinationPath)) {
                @unlink($temporaryPath);
                $result['error'] = 'Unable to publish the optimized WebP image.';

                return $result;
            }

            @unlink($temporaryPath);
            clearstatcache(true, $destinationPath);
            $outputBytes = (int) (filesize($destinationPath) ?: 0);

            if ($outputBytes < 1) {
                $result['error'] = 'The optimized WebP image is empty.';

                return $result;
            }

            if ($removeSource && $destinationPath !== $sourcePath) {
                @unlink($sourcePath);
            }

            $result['success'] = true;
            $result['optimized'] = true;
            $result['output_path'] = $destinationPath;
            $result['output_bytes'] = $outputBytes;

            return $result;
        } catch (Throwable $exception) {
            $result['error'] = $exception->getMessage();

            return $result;
        }
    }

    private function applyExifOrientation(\GdImage $image, string $sourcePath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($sourcePath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        if (in_array($orientation, [2, 4, 5, 7], true) && function_exists('imageflip')) {
            imageflip($image, in_array($orientation, [2, 5], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
