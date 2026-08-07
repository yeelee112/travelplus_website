<?php

use App\Services\LoyaltyRewardService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class LoyaltyRewardServiceTest extends CIUnitTestCase
{
    public function testCatalogUsesOnePercentRedemptionValue(): void
    {
        $catalog = (new LoyaltyRewardService())->catalog(1200);

        $this->assertSame([500, 1200, 2500], array_column($catalog, 'points'));
        $this->assertSame([50000, 120000, 250000], array_column($catalog, 'amount_vnd'));
        $this->assertSame([true, true, false], array_column($catalog, 'available'));
        $this->assertSame(1300, $catalog[2]['points_needed']);
    }

    public function testCatalogNeverReturnsNegativePointsNeeded(): void
    {
        $catalog = (new LoyaltyRewardService())->catalog(9999);

        $this->assertSame([0, 0, 0], array_column($catalog, 'points_needed'));
    }

    public function testNextRewardReturnsTheFirstUnreachedVoucher(): void
    {
        $service = new LoyaltyRewardService();

        $this->assertSame('passport_50', $service->nextReward(0)['key']);
        $this->assertSame('passport_120', $service->nextReward(500)['key']);
        $this->assertSame('passport_250', $service->nextReward(2499)['key']);
        $this->assertNull($service->nextReward(2500));
    }
}
