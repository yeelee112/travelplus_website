<?php

use App\Services\TourPassportPricePresenter;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class TourPassportPricePresenterTest extends CIUnitTestCase
{
    public function testGuestSeesNeutralTierBenefitMessage(): void
    {
        $benefit = TourPassportPricePresenter::build(15500000, null, null, 'vi');

        $this->assertSame('guest', $benefit['state']);
        $this->assertSame('', $benefit['eyebrow']);
        $this->assertSame('Ưu đãi thành viên', $benefit['label']);
        $this->assertArrayNotHasKey('price', $benefit);
    }

    public function testMemberBelowSilverSeesRemainingMiles(): void
    {
        $benefit = TourPassportPricePresenter::build(
            15500000,
            ['id' => 8],
            ['points' => 3750, 'qualifying_points' => 3750],
            'vi'
        );

        $this->assertSame('locked', $benefit['state']);
        $this->assertSame('Còn 1.250 Dặm để mở giá Bạc', $benefit['label']);
    }

    public function testGoldMemberSeesPersonalPriceAndSaving(): void
    {
        $benefit = TourPassportPricePresenter::build(
            15500000,
            ['id' => 8],
            ['points' => 20855, 'qualifying_points' => 20855],
            'vi'
        );

        $this->assertSame('active', $benefit['state']);
        $this->assertSame('Vàng', $benefit['label']);
        $this->assertSame('gold', $benefit['tier_key']);
        $this->assertSame('15.267.500đ', $benefit['price']);
        $this->assertSame('Tiết kiệm 232.500đ', $benefit['saving']);
        $this->assertSame(232500.0, $benefit['discount_amount']);
    }

    public function testTierCapMatchesCheckoutRule(): void
    {
        $benefit = TourPassportPricePresenter::build(
            160000000,
            ['id' => 8],
            ['points' => 20855, 'qualifying_points' => 20855],
            'vi'
        );

        $this->assertSame('159.600.000đ', $benefit['price']);
        $this->assertSame('Tiết kiệm 400.000đ', $benefit['saving']);
        $this->assertSame(400000.0, $benefit['discount_amount']);
    }
}
