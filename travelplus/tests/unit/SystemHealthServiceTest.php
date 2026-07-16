<?php

use App\Services\SystemHealthService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SystemHealthServiceTest extends CIUnitTestCase
{
    public function testSummaryPrioritizesErrorsOverWarnings(): void
    {
        $summary = SystemHealthService::summarize([
            ['status' => SystemHealthService::STATUS_OK],
            ['status' => SystemHealthService::STATUS_WARNING],
            ['status' => SystemHealthService::STATUS_ERROR],
        ]);

        $this->assertSame(1, $summary['ok']);
        $this->assertSame(1, $summary['warning']);
        $this->assertSame(1, $summary['error']);
        $this->assertSame(3, $summary['total']);
        $this->assertSame(SystemHealthService::STATUS_ERROR, $summary['status']);
    }

    public function testIndexCoverageIsHealthyWhenEveryIndexIsInstalled(): void
    {
        $coverage = SystemHealthService::evaluateIndexCoverage(SystemHealthService::expectedIndexNames());

        $this->assertSame(SystemHealthService::STATUS_OK, $coverage['status']);
        $this->assertSame(15, $coverage['installed']);
        $this->assertSame(15, $coverage['expected']);
        $this->assertSame([], $coverage['missing']);
    }

    public function testIndexCoverageDistinguishesPartialAndMissingInstallations(): void
    {
        $indexes = SystemHealthService::expectedIndexNames();

        $partial = SystemHealthService::evaluateIndexCoverage(array_slice($indexes, 0, 4));
        $missing = SystemHealthService::evaluateIndexCoverage([]);

        $this->assertSame(SystemHealthService::STATUS_WARNING, $partial['status']);
        $this->assertSame(4, $partial['installed']);
        $this->assertCount(11, $partial['missing']);
        $this->assertSame(SystemHealthService::STATUS_ERROR, $missing['status']);
        $this->assertSame(0, $missing['installed']);
    }

    public function testUploadLimitsReportTheEffectiveValueAndBottleneck(): void
    {
        $healthy = SystemHealthService::evaluateUploadLimits('8M', '256M');
        $unlimitedPost = SystemHealthService::evaluateUploadLimits('8M', '0');
        $uploadLimited = SystemHealthService::evaluateUploadLimits('2M', '256M');
        $postLimited = SystemHealthService::evaluateUploadLimits('256M', '2M');

        $this->assertSame(SystemHealthService::STATUS_OK, $healthy['status']);
        $this->assertSame(8 * 1024 * 1024, $healthy['effective_bytes']);
        $this->assertSame('upload_max_filesize', $healthy['bottleneck']);
        $this->assertSame(8 * 1024 * 1024, $unlimitedPost['effective_bytes']);
        $this->assertSame(SystemHealthService::STATUS_OK, $unlimitedPost['status']);

        $this->assertSame(SystemHealthService::STATUS_WARNING, $uploadLimited['status']);
        $this->assertSame('upload_max_filesize', $uploadLimited['bottleneck']);
        $this->assertSame(2 * 1024 * 1024, $uploadLimited['effective_bytes']);

        $this->assertSame(SystemHealthService::STATUS_WARNING, $postLimited['status']);
        $this->assertSame('post_max_size', $postLimited['bottleneck']);
        $this->assertSame(2 * 1024 * 1024, $postLimited['effective_bytes']);
    }
}
