<?php

use App\Services\LoyaltyMembershipService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LoyaltyMembershipServiceTest extends CIUnitTestCase
{
    public function testInactiveProgramKeepsRealBookingMetricsWithoutInventingPoints(): void
    {
        $snapshot = (new LoyaltyMembershipService())->buildSnapshot([
            ['payment_status' => 'paid'],
            ['payment_status' => 'pending_transfer'],
            ['payment_status' => 'cancelled'],
        ]);

        $this->assertFalse($snapshot['program_active']);
        $this->assertSame(0, $snapshot['points']);
        $this->assertSame('member', $snapshot['current_tier']['key']);
        $this->assertSame(3, $snapshot['booking_count']);
        $this->assertSame(1, $snapshot['paid_booking_count']);
        $this->assertSame(1, $snapshot['pending_booking_count']);
    }

    public function testActiveProgramResolvesTierAndProgress(): void
    {
        $snapshot = (new LoyaltyMembershipService())->buildSnapshot([], 30000);

        $this->assertTrue($snapshot['program_active']);
        $this->assertSame('gold', $snapshot['current_tier']['key']);
        $this->assertSame('diamond', $snapshot['next_tier']['key']);
        $this->assertSame(25, $snapshot['progress']);
        $this->assertSame(30000, $snapshot['remaining_points']);
    }

    public function testSignatureTierHasNoNextTier(): void
    {
        $snapshot = (new LoyaltyMembershipService())->buildSnapshot([], 180000);

        $this->assertSame('signature', $snapshot['current_tier']['key']);
        $this->assertNull($snapshot['next_tier']);
        $this->assertSame(100, $snapshot['progress']);
        $this->assertSame(0, $snapshot['remaining_points']);
    }

    public function testBuildsSnapshotFromAggregateBookingCounts(): void
    {
        $snapshot = (new LoyaltyMembershipService())->buildSnapshotFromCounts(128, 35, 7, 4855);

        $this->assertSame(128, $snapshot['booking_count']);
        $this->assertSame(35, $snapshot['paid_booking_count']);
        $this->assertSame(7, $snapshot['pending_booking_count']);
        $this->assertSame('member', $snapshot['current_tier']['key']);
        $this->assertSame(4855, $snapshot['points']);
    }

    public function testPremiumTierBoundaries(): void
    {
        $service = new LoyaltyMembershipService();
        $boundaries = [
            4999 => 'member',
            5000 => 'silver',
            19999 => 'silver',
            20000 => 'gold',
            59999 => 'gold',
            60000 => 'diamond',
            149999 => 'diamond',
            150000 => 'signature',
        ];

        foreach ($boundaries as $points => $expectedTier) {
            $snapshot = $service->buildSnapshot([], $points);
            $this->assertSame($expectedTier, $snapshot['current_tier']['key'], 'Unexpected tier at ' . $points . ' points.');
        }
    }

    public function testEachTierUsesTheRewardDiscountRate(): void
    {
        $service = new LoyaltyMembershipService();
        $rates = [
            0 => 0.0,
            5000 => 0.5,
            20000 => 0.75,
            60000 => 1.0,
            150000 => 1.5,
        ];

        foreach ($rates as $points => $expectedRate) {
            $snapshot = $service->buildSnapshot([], $points);
            $this->assertSame($expectedRate, $snapshot['current_tier']['discount_rate']);
            $this->assertSame(0, $snapshot['current_tier']['discount_cap_vnd']);
            $this->assertEquals(round(1000000000 * $expectedRate / 100), LoyaltyMembershipService::calculateTierDiscount(1000000000, $expectedRate, $snapshot['current_tier']['discount_cap_vnd']));
        }
    }

    public function testTierDiscountUsesRateAndTierAmountCap(): void
    {
        $this->assertSame(150000.0, LoyaltyMembershipService::calculateTierDiscount(10000000, 1.5, 400000));
        $this->assertSame(400000.0, LoyaltyMembershipService::calculateTierDiscount(50000000, 1.5, 400000));
        $this->assertSame(300000.0, LoyaltyMembershipService::calculateTierDiscount(10000000, 9, 1000000));
        $this->assertSame(0.0, LoyaltyMembershipService::calculateTierDiscount(-1000, 1));
    }
}
