<?php

use App\Services\LoyaltyTierBenefitService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class LoyaltyTierBenefitServiceTest extends CIUnitTestCase
{
    public function testCatalogUsesSustainableTierThresholdsAndBookingMinimums(): void
    {
        $catalog = (new LoyaltyTierBenefitService())->catalog();

        $this->assertSame(['silver', 'gold', 'diamond', 'signature'], array_column($catalog, 'key'));
        $this->assertSame([5000, 20000, 60000, 150000], array_column($catalog, 'minimum_points'));
        $this->assertSame([100000, 200000, 300000, 500000], array_column($catalog, 'amount_vnd'));
        $this->assertSame([3000000, 6000000, 10000000, 15000000], array_column($catalog, 'min_order_vnd'));
    }

    public function testEligibleBenefitsAreCumulativeAtEachThreshold(): void
    {
        $service = new LoyaltyTierBenefitService();

        $this->assertSame([], $service->eligibleBenefits(4999));
        $this->assertSame(['silver'], array_column($service->eligibleBenefits(5000), 'key'));
        $this->assertSame(['silver', 'gold'], array_column($service->eligibleBenefits(20000), 'key'));
        $this->assertSame(
            ['silver', 'gold', 'diamond', 'signature'],
            array_column($service->eligibleBenefits(150000), 'key')
        );
    }
}
