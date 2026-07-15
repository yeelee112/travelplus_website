<?php

use App\Services\VisitorCounterService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class VisitorCounterServiceTest extends CIUnitTestCase
{
    private string $testDirectory;
    private string $counterPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travelplus-counter-' . bin2hex(random_bytes(6));
        $this->counterPath = $this->testDirectory . DIRECTORY_SEPARATOR . 'site-views.json';
        mkdir($this->testDirectory, 0755, true);
        session()->remove('site_view_counted');
    }

    protected function tearDown(): void
    {
        session()->remove('site_view_counted');

        foreach ((array) glob($this->testDirectory . DIRECTORY_SEPARATOR . '*') as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        if (is_dir($this->testDirectory)) {
            rmdir($this->testDirectory);
        }

        parent::tearDown();
    }

    public function testStartsFromProtectedBaselineWhenCounterIsMissing(): void
    {
        $service = new VisitorCounterService($this->counterPath);

        $this->assertSame(5001, $service->getTotalViews());
        $this->assertSame(5001, $this->readTotal($this->counterPath));
        $this->assertSame(5000, $this->readTotal($this->counterPath . '.bak'));
    }

    public function testRecoversFromValidBackupWhenPrimaryCounterIsCorrupt(): void
    {
        file_put_contents($this->counterPath, '{invalid-json');
        file_put_contents($this->counterPath . '.bak', json_encode(['total' => 5320]));

        $service = new VisitorCounterService($this->counterPath);

        $this->assertSame(5321, $service->getTotalViews());
        $this->assertSame(5321, $this->readTotal($this->counterPath));
        $this->assertSame(5320, $this->readTotal($this->counterPath . '.bak'));
    }

    public function testNeverContinuesFromAValueBelowBaseline(): void
    {
        file_put_contents($this->counterPath, json_encode(['total' => 100]));

        $service = new VisitorCounterService($this->counterPath);

        $this->assertSame(5001, $service->getTotalViews());
        $this->assertSame(5001, $this->readTotal($this->counterPath));
    }

    public function testPreservesAnExistingTotalAboveBaseline(): void
    {
        file_put_contents($this->counterPath, json_encode(['total' => 5842]));
        file_put_contents($this->counterPath . '.bak', json_encode(['total' => 5841]));

        $service = new VisitorCounterService($this->counterPath);

        $this->assertSame(5843, $service->getTotalViews());
        $this->assertSame(5843, $this->readTotal($this->counterPath));
        $this->assertSame(5842, $this->readTotal($this->counterPath . '.bak'));
    }

    public function testOnlyIncrementsOncePerSession(): void
    {
        $service = new VisitorCounterService($this->counterPath);

        $this->assertSame(5001, $service->getTotalViews());
        $this->assertSame(5001, $service->getTotalViews());
        $this->assertSame(5001, $this->readTotal($this->counterPath));
    }

    private function readTotal(string $path): int
    {
        $data = json_decode((string) file_get_contents($path), true);

        return (int) ($data['total'] ?? 0);
    }
}
