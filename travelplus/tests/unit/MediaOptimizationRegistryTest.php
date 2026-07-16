<?php

use App\Services\MediaOptimizationRegistry;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MediaOptimizationRegistryTest extends CIUnitTestCase
{
    public function testRecognizesOnlyTheRecordedFileVersion(): void
    {
        $token = bin2hex(random_bytes(5));
        $manifestPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "travelplus-media-registry-{$token}.json";
        $imagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "travelplus-media-image-{$token}.webp";
        file_put_contents($imagePath, str_repeat('a', 4096));

        try {
            $registry = new MediaOptimizationRegistry($manifestPath);
            $this->assertFalse($registry->isCurrent('uploads/test.webp', $imagePath));

            $registry->markCurrent('uploads/test.webp', $imagePath);
            $this->assertTrue($registry->persist());

            $reloaded = new MediaOptimizationRegistry($manifestPath);
            $this->assertTrue($reloaded->isCurrent('uploads/test.webp', $imagePath));

            file_put_contents($imagePath, 'changed', FILE_APPEND);
            $this->assertFalse($reloaded->isCurrent('uploads/test.webp', $imagePath));
        } finally {
            @unlink($manifestPath);
            @unlink($imagePath);
        }
    }
}
