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

    public function testRewardRatesApplyToSmallAndLargeBookings(): void
    {
        foreach ([[5000, 0.5], [20000, 0.75], [60000, 1.0], [150000, 1.5]] as [$points, $rate]) {
            foreach ([50000, 15500000, 160000000] as $price) {
                $benefit = TourPassportPricePresenter::build($price, ['id' => 8], ['points' => $points, 'qualifying_points' => $points], 'vi');
                $this->assertSame('active', $benefit['state']);
                $this->assertEquals(round($price * $rate / 100), $benefit['discount_amount']);
                $this->assertArrayHasKey('price', $benefit);
            }
        }
    }
}
