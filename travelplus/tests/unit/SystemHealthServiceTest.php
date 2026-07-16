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
}
