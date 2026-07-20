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
        $snapshot = (new LoyaltyMembershipService())->buildSnapshot([], 7000);

        $this->assertTrue($snapshot['program_active']);
        $this->assertSame('gold', $snapshot['current_tier']['key']);
        $this->assertSame('diamond', $snapshot['next_tier']['key']);
        $this->assertSame(20, $snapshot['progress']);
        $this->assertSame(8000, $snapshot['remaining_points']);
    }

    public function testSignatureTierHasNoNextTier(): void
    {
        $snapshot = (new LoyaltyMembershipService())->buildSnapshot([], 45000);

        $this->assertSame('signature', $snapshot['current_tier']['key']);
        $this->assertNull($snapshot['next_tier']);
        $this->assertSame(100, $snapshot['progress']);
        $this->assertSame(0, $snapshot['remaining_points']);
    }
}
