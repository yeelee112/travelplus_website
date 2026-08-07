<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class BookingCheckoutPassportViewTest extends CIUnitTestCase
{
    public function testCheckoutRendersSelectableAndLockedPassportVouchers(): void
    {
        service('request')->setLocale('vi');

        $html = view('booking/checkout', [
            'pendingBooking' => [
                'tour_id' => 10,
                'tour_title' => 'Tour test',
                'tour_link' => '/',
                'tour_image' => '/assets/images/home/banner00.png',
                'departure_label' => '01/01/2027',
                'duration_label' => '4 Ngày / 3 Đêm',
                'adult_quantity' => 1,
                'child_quantity' => 0,
                'infant_quantity' => 0,
                'adult_price' => 5000000,
                'subtotal_vnd' => 5000000,
                'coupon_eligible_subtotal_vnd' => 5000000,
                'membership_tier_key' => 'gold',
                'membership_discount_rate' => 1.5,
                'membership_discount_amount_vnd' => 75000,
                'grand_total' => 4925000,
                'currency' => 'VND',
            ],
            'authUser' => ['id' => 2, 'full_name' => 'Passport Member'],
            'checkoutMode' => 'member',
            'passportVouchers' => [
                [
                    'code' => 'TPP-SI-TEST0001',
                    'reward_key' => 'tier_welcome_silver',
                    'voucher_amount_vnd' => 100000,
                    'min_order_vnd' => 3000000,
                    'expires_at' => '2027-08-06 12:00:00',
                    'eligible' => true,
                    'amount_needed_vnd' => 0,
                    'benefit_type' => 'tier',
                ],
                [
                    'code' => 'TPP-GO-TEST0002',
                    'reward_key' => 'tier_welcome_gold',
                    'voucher_amount_vnd' => 200000,
                    'min_order_vnd' => 6000000,
                    'expires_at' => '2027-08-06 12:00:00',
                    'eligible' => false,
                    'amount_needed_vnd' => 1000000,
                    'benefit_type' => 'tier',
                ],
            ],
            'administrativeProvinces' => [],
            'addressDataUrl' => '',
            'menu' => [],
            'domesticMenu' => [],
            'headerMembership' => null,
            'isAdminUser' => false,
            'currentLocale' => 'vi',
        ]);

        $this->assertStringContainsString('Voucher Passport của bạn', $html);
        $this->assertStringContainsString('data-passport-voucher-code="TPP-SI-TEST0001"', $html);
        $this->assertStringContainsString('data-passport-voucher-eligible="1"', $html);
        $this->assertStringContainsString('data-passport-voucher-code="TPP-GO-TEST0002"', $html);
        $this->assertStringContainsString('data-passport-voucher-eligible="0"', $html);
        $this->assertStringContainsString('Cần thêm 1.000.000 VND để sử dụng', $html);
        $this->assertStringContainsString('Ưu đãi Passport hạng Vàng (1,5%)', $html);
        $this->assertStringContainsString('-75.000 VND', $html);
    }
}
