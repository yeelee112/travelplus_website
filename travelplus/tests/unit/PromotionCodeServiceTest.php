<?php

use App\Services\PromotionCodeService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PromotionCodeServiceTest extends CIUnitTestCase
{
    public function testCalculatesFixedDiscountWithoutExceedingEligibleSubtotal(): void
    {
        $this->assertSame(150000.0, PromotionCodeService::calculateDiscount('fixed', 150000, 1000000));
        $this->assertSame(1000000.0, PromotionCodeService::calculateDiscount('fixed', 1500000, 1000000));
    }

    public function testCalculatesPercentageDiscountAndHonorsMaximum(): void
    {
        $this->assertSame(300000.0, PromotionCodeService::calculateDiscount('percent', 15, 2000000));
        $this->assertSame(200000.0, PromotionCodeService::calculateDiscount('percent', 15, 2000000, 200000));
    }

    public function testRejectsNonPositiveDiscountInputs(): void
    {
        $this->assertSame(0.0, PromotionCodeService::calculateDiscount('fixed', 0, 1000000));
        $this->assertSame(0.0, PromotionCodeService::calculateDiscount('percent', 10, 0));
    }
}
