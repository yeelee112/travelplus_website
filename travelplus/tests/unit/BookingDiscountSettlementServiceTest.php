<?php

use App\Services\BookingDiscountSettlementService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class BookingDiscountSettlementServiceTest extends CIUnitTestCase
{
    public function testPaidTransitionConsumesCoupon(): void
    {
        $this->assertSame('consume', BookingDiscountSettlementService::transitionAction('pending_payment', 'paid'));
        $this->assertSame('consume', BookingDiscountSettlementService::transitionAction('pending_transfer', 'paid'));
    }

    public function testFailedOrCancelledPaymentReleasesReservation(): void
    {
        $this->assertSame('release', BookingDiscountSettlementService::transitionAction('pending_payment', 'failed'));
        $this->assertSame('release', BookingDiscountSettlementService::transitionAction('pending_payment', 'cancelled'));
    }

    public function testRefundOrCancellationAfterPaymentRestoresCoupon(): void
    {
        $this->assertSame('restore', BookingDiscountSettlementService::transitionAction('paid', 'cancelled'));
        $this->assertSame('restore', BookingDiscountSettlementService::transitionAction('paid', 'failed'));
    }

    public function testUnchangedStatusesAreIdempotent(): void
    {
        $this->assertSame('none', BookingDiscountSettlementService::transitionAction('paid', 'paid'));
        $this->assertSame('none', BookingDiscountSettlementService::transitionAction('pending_payment', 'pending_transfer'));
    }
}
