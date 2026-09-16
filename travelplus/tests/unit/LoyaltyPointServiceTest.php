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

    public function testBookingPointsUseOriginalTravelerFareBeforeDiscountsAndDeposit(): void
    {
        $booking = [
            'adult_quantity' => 2,
            'adult_price' => 10000000,
            'child_quantity' => 1,
            'child_price' => 8500000,
            'infant_quantity' => 1,
            'infant_price' => 2500000,
            'single_room_supplement_vnd' => 3000000,
            'subtotal_vnd' => 34000000,
            'membership_discount_amount_vnd' => 155000,
            'discount_amount_vnd' => 250000,
            'grand_total' => 33595000,
            'payment_plan' => 'deposit',
            'amount_paid_vnd' => 3359500,
        ];

        $earningBase = LoyaltyPointService::earningBaseForBooking($booking);

        $this->assertSame(31000000.0, $earningBase);
        $this->assertSame(3100, LoyaltyPointService::previewPoints($earningBase));
    }

    public function testLegacyBookingEarningBaseExcludesSingleRoomSupplement(): void
    {
        $this->assertSame(160000000.0, LoyaltyPointService::earningBaseForBooking([
            'subtotal_vnd' => 162800000,
            'single_room_supplement_vnd' => 2800000,
            'amount_paid_vnd' => 16280000,
        ]));
    }
}
