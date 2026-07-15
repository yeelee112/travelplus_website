<?php

use App\Services\ImageOptimizationService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ImageOptimizationServiceTest extends CIUnitTestCase
{
    public function testConvertsAndResizesPngToWebp(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('PHP GD WebP support is unavailable.');
        }

        $sourcePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travelplus-image-' . bin2hex(random_bytes(5)) . '.png';
        $outputPath = preg_replace('/\.png$/', '.webp', $sourcePath) ?: ($sourcePath . '.webp');
        $image = imagecreatetruecolor(1600, 900);
        $background = imagecolorallocate($image, 22, 132, 190);
        imagefill($image, 0, 0, $background);

        for ($index = 0; $index < 120; $index++) {
            $color = imagecolorallocate($image, ($index * 19) % 255, ($index * 41) % 255, ($index * 73) % 255);
            imageline($image, 0, $index * 7, 1599, 899 - ($index * 5 % 899), $color);
        }

        imagepng($image, $sourcePath, 0);
        imagedestroy($image);

        try {
            $result = (new ImageOptimizationService())->optimizeToWebp($sourcePath, 800, 600, 82, true);

            $this->assertTrue($result['success'], $result['error']);
            $this->assertFileExists($outputPath);
            $this->assertFileDoesNotExist($sourcePath);
            $this->assertLessThan($result['original_bytes'], $result['output_bytes']);

            $size = getimagesize($outputPath);
            $this->assertSame(800, (int) ($size[0] ?? 0));
            $this->assertSame(450, (int) ($size[1] ?? 0));
            $this->assertSame('image/webp', (string) ($size['mime'] ?? ''));
        } finally {
            @unlink($sourcePath);
            @unlink($outputPath);
        }
    }

    public function testRejectsNonImageFileWithoutDeletingIt(): void
    {
        $sourcePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travelplus-image-' . bin2hex(random_bytes(5)) . '.png';
        file_put_contents($sourcePath, 'not an image');

        try {
            $result = (new ImageOptimizationService())->optimizeToWebp($sourcePath, 800, 600, 82, true);

            $this->assertFalse($result['success']);
            $this->assertFileExists($sourcePath);
        } finally {
            @unlink($sourcePath);
        }
    }
}
