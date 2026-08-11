<?php

use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class ProfilePassportDashboardViewTest extends CIUnitTestCase
{
    public function testProfilePrioritizesTierBalancesProgressAndVoucherWallet(): void
    {
        service('request')->setLocale('vi');

        $html = view('auth/profile', [
            'user' => [
                'id' => 8,
                'full_name' => 'An Châu',
                'email' => 'member@example.test',
                'username' => 'anchau',
                'phone' => '0900000000',
                'status' => 'active',
                'last_login_at' => '2026-08-11 09:00:00',
            ],
            'bookings' => [],
            'membership' => [
                'program_active' => true,
                'points' => 1234,
                'qualifying_points' => 20855,
                'current_tier' => ['key' => 'gold', 'minimum_points' => 20000],
                'next_tier' => ['key' => 'diamond', 'minimum_points' => 60000],
                'remaining_points' => 39145,
                'progress' => 2,
                'tiers' => [
                    ['key' => 'member', 'minimum_points' => 0],
                    ['key' => 'silver', 'minimum_points' => 5000],
                    ['key' => 'gold', 'minimum_points' => 20000],
                    ['key' => 'diamond', 'minimum_points' => 60000],
                    ['key' => 'signature', 'minimum_points' => 150000],
                ],
                'booking_count' => 4,
                'paid_booking_count' => 3,
                'pending_booking_count' => 1,
            ],
            'loyaltyHistory' => [],
            'rewardCatalog' => [
                ['key' => 'passport_50', 'points' => 500, 'amount_vnd' => 50000, 'min_order_vnd' => 2000000, 'available' => true, 'points_needed' => 0],
            ],
            'rewardVouchers' => [
                [
                    'code' => 'TPP-GO-DASHBOARD',
                    'reward_key' => 'tier_welcome_gold',
                    'points_spent' => 0,
                    'voucher_amount_vnd' => 200000,
                    'min_order_vnd' => 6000000,
                    'status' => 'issued',
                    'expires_at' => '2099-12-31 23:59:59',
                    'used_count' => 0,
                    'is_active' => 1,
                ],
            ],
            'rewardsAvailable' => true,
            'authUser' => ['id' => 8, 'full_name' => 'An Châu', 'email' => 'member@example.test'],
            'administrativeProvinces' => [],
            'addressDataUrl' => '',
            'menu' => [],
            'domesticMenu' => [],
            'headerMembership' => null,
            'isAdminUser' => false,
            'currentLocale' => 'vi',
        ]);

        $this->assertStringContainsString('travelplus-profile-passport-card', $html);
        $this->assertStringContainsString('Passport của tôi', $html);
        $this->assertStringContainsString('Dặm khả dụng', $html);
        $this->assertStringContainsString('1.234', $html);
        $this->assertStringContainsString('Dặm xét hạng', $html);
        $this->assertStringContainsString('20.855', $html);
        $this->assertStringContainsString('Mục tiêu hạng tiếp theo', $html);
        $this->assertStringContainsString('Còn 39.145 Dặm', $html);
        $this->assertStringContainsString('travelplus-profile-passport__progress--diamond', $html);
        $this->assertStringContainsString('Đặt tour tích thêm Dặm', $html);
        $this->assertStringContainsString('Ví voucher của tôi', $html);
        $this->assertStringContainsString('TPP-GO-DASHBOARD', $html);
        $this->assertStringContainsString('Hạn dùng 31/12/2099', $html);
        $this->assertStringContainsString('Đổi thêm voucher', $html);
    }
}
