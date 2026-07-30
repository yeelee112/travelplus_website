<?php

use App\Services\VietnamAdministrativeUnitService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class VietnamAdministrativeUnitServiceTest extends CIUnitTestCase
{
    public function testLoadsPostMergerProvinceList(): void
    {
        $service = new VietnamAdministrativeUnitService();

        $this->assertCount(34, $service->provinces());
    }

    public function testResolvesWardOnlyInsideSelectedProvince(): void
    {
        $service = new VietnamAdministrativeUnitService();
        $unit = $service->resolve('79', '25747');

        $this->assertSame('Thành phố Hồ Chí Minh', $unit['province_name'] ?? null);
        $this->assertSame('Phường Thủ Dầu Một', $unit['ward_name'] ?? null);
        $this->assertNull($service->resolve('01', '25747'));
    }

    public function testFormatsTwoLevelAddress(): void
    {
        $service = new VietnamAdministrativeUnitService();

        $this->assertSame(
            '12 Nguyễn Huệ, Phường Sài Gòn, Thành phố Hồ Chí Minh',
            $service->formatAddress('12 Nguyễn Huệ', [
                'ward_name' => 'Phường Sài Gòn',
                'province_name' => 'Thành phố Hồ Chí Minh',
            ])
        );
    }
}
