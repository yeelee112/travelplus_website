<?php

use App\Services\LoyaltyPointService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LoyaltyPointServiceTest extends CIUnitTestCase
{
    public function testPointsAreCalculatedFromCompletedTenThousandVndUnits(): void
    {
        $service = new LoyaltyPointService();

        $this->assertSame(0, $service->calculatePoints(9999));
        $this->assertSame(1, $service->calculatePoints(10000));
        $this->assertSame(3255, $service->calculatePoints(32550000));
    }

    public function testNegativeAmountsNeverCreateNegativePoints(): void
    {
        $this->assertSame(0, (new LoyaltyPointService())->calculatePoints(-100000));
    }

    public function testPreviewUsesTheSameRuleAsAwardedPoints(): void
    {
        $service = new LoyaltyPointService();

        $this->assertSame(2599, LoyaltyPointService::previewPoints(25999000));
        $this->assertSame($service->calculatePoints(160000000), LoyaltyPointService::previewPoints(160000000));
    }
}
