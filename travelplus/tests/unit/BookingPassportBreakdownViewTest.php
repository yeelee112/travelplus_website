<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class BookingPassportBreakdownViewTest extends CIUnitTestCase
{
    public function testPaidBookingHighlightsMilesTierUpAndAppliedBenefits(): void
    {
        service('request')->setLocale('vi');

        $html = view('booking/_passport-breakdown', [
            'locale' => 'vi',
            'passportSummary' => $this->summary([
                'state' => 'earned',
                'earned_points' => 2000,
                'available_points' => 21000,
                'qualifying_points' => 21000,
                'current_tier' => ['key' => 'gold', 'minimum_points' => 20000],
                'next_tier' => ['key' => 'diamond', 'minimum_points' => 60000],
                'progress' => 2,
                'remaining_points' => 39000,
                'tier_up' => true,
                'booking_tier_after_key' => 'gold',
                'membership_tier_key' => 'silver',
                'membership_discount_rate' => 1.0,
                'membership_discount_amount_vnd' => 200000,
                'coupon_discount_amount_vnd' => 100000,
                'total_benefit_vnd' => 300000,
                'voucher' => [
                    'code' => 'TPP-SI-TEST0001',
                    'status' => 'used',
                    'benefit_type' => 'tier',
                ],
            ]),
        ]);

        $this->assertStringContainsString('booking-passport-breakdown--earned', $html);
        $this->assertStringContainsString('Dặm Hành Trình đã được cộng', $html);
        $this->assertStringContainsString('+2.000', $html);
        $this->assertStringContainsString('Số dư khả dụng hiện tại:', $html);
        $this->assertStringContainsString('Chúc mừng! Bạn đã đạt hạng Vàng', $html);
        $this->assertStringContainsString('Còn 39.000 Dặm để đạt Kim Cương', $html);
        $this->assertStringContainsString('Tổng tiết kiệm 300.000đ', $html);
        $this->assertStringContainsString('TPP-SI-TEST0001', $html);
        $this->assertStringContainsString('Đã áp dụng', $html);
    }

    public function testPendingBookingExplainsThatMilesAreNotAvailableYet(): void
    {
        service('request')->setLocale('vi');

        $html = view('booking/_passport-breakdown', [
            'locale' => 'vi',
            'passportSummary' => $this->summary([
                'state' => 'pending',
                'preview_points' => 500,
            ]),
        ]);

        $this->assertStringContainsString('booking-passport-breakdown--pending', $html);
        $this->assertStringContainsString('Dặm đang chờ ghi nhận', $html);
        $this->assertStringContainsString('Dự kiến sau khi xác nhận', $html);
        $this->assertStringContainsString('+500', $html);
    }

    public function testReversedBookingShowsMilesAdjustmentAndVoucherReturn(): void
    {
        service('request')->setLocale('vi');

        $html = view('booking/_passport-breakdown', [
            'locale' => 'vi',
            'passportSummary' => $this->summary([
                'state' => 'reversed',
                'earned_points' => 500,
                'reversed_points' => 500,
                'coupon_discount_amount_vnd' => 50000,
                'total_benefit_vnd' => 50000,
                'voucher' => [
                    'code' => 'TPP-RETURNED',
                    'status' => 'issued',
                    'benefit_type' => 'miles',
                ],
            ]),
        ]);

        $this->assertStringContainsString('booking-passport-breakdown--reversed', $html);
        $this->assertStringContainsString('Dặm của booking đã được điều chỉnh', $html);
        $this->assertStringContainsString('-500', $html);
        $this->assertStringContainsString('Đã hoàn lại ví', $html);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function summary(array $overrides = []): array
    {
        return array_replace([
            'visible' => true,
            'state' => 'earned',
            'earned_points' => 0,
            'reversed_points' => 0,
            'net_points' => 0,
            'preview_points' => 0,
            'available_points' => 0,
            'qualifying_points' => 0,
            'current_tier' => ['key' => 'member', 'minimum_points' => 0],
            'next_tier' => ['key' => 'silver', 'minimum_points' => 5000],
            'progress' => 0,
            'remaining_points' => 5000,
            'tier_up' => false,
            'previous_tier_key' => 'member',
            'booking_tier_after_key' => 'member',
            'membership_tier_key' => 'member',
            'membership_discount_rate' => 0,
            'membership_discount_amount_vnd' => 0,
            'coupon_discount_amount_vnd' => 0,
            'total_benefit_vnd' => 0,
            'voucher' => null,
        ], $overrides);
    }
}
