<?php

namespace Tests\Unit;

use App\Services\TourCatalogService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class TourCatalogLocationSlugTest extends TestCase
{
    private TourCatalogService $service;
    private ReflectionMethod $method;

    protected function setUp(): void
    {
        $this->service = (new ReflectionClass(TourCatalogService::class))->newInstanceWithoutConstructor();
        $this->method = new ReflectionMethod(TourCatalogService::class, 'findCardForLocationSlug');
    }

    public function testDomesticTourMatchesRegionSlug(): void
    {
        $cards = [
            ['id' => 10, 'region_slug' => 'mien-bac', 'continent_slug' => 'asia'],
            ['id' => 11, 'region_slug' => 'mien-trung', 'continent_slug' => 'asia'],
        ];

        $result = $this->method->invoke($this->service, $cards, 'mien-trung', 'domestic');

        $this->assertSame(11, $result['id']);
    }

    public function testDomesticMultiRegionTourMatchesAnyDestinationRegion(): void
    {
        $cards = [[
            'id' => 12,
            'region_slug' => 'mien-bac',
            'region_slugs' => ['mien-bac', 'mien-trung', 'mien-nam', 'mien-tay', 'xuyen-viet'],
            'continent_slug' => 'asia',
        ]];

        $result = $this->method->invoke($this->service, $cards, 'mien-nam', 'domestic');

        $this->assertSame(12, $result['id']);
    }

    public function testOutboundTourMatchesContinentSlug(): void
    {
        $cards = [
            ['id' => 20, 'region_slug' => '', 'continent_slug' => 'asia'],
            ['id' => 21, 'region_slug' => '', 'continent_slug' => 'europe'],
        ];

        $result = $this->method->invoke($this->service, $cards, 'europe', 'outbound');

        $this->assertSame(21, $result['id']);
    }

    public function testMismatchedLocationDoesNotReturnAnotherTour(): void
    {
        $cards = [
            ['id' => 30, 'region_slug' => 'mien-nam', 'continent_slug' => 'asia'],
        ];

        $result = $this->method->invoke($this->service, $cards, 'mien-trung', 'domestic');

        $this->assertNull($result);
    }

    public function testInboundTourAlsoUsesVietnamRegionSlug(): void
    {
        $cards = [[
            'id' => 40,
            'region_slug' => 'mien-trung',
            'region_slugs' => ['mien-trung'],
            'continent_slug' => 'asia',
        ]];

        $result = $this->method->invoke($this->service, $cards, 'mien-trung', 'inbound');

        $this->assertSame(40, $result['id']);
    }

    public function testGenericVietnamSlugResolvesLocalTour(): void
    {
        $cards = [['id' => 50, 'region_slug' => 'mien-bac']];

        $result = $this->method->invoke($this->service, $cards, 'viet-nam', 'inbound');

        $this->assertSame(50, $result['id']);
    }

    public function testInboundTourMatchesNeighboringCountrySlug(): void
    {
        $cards = [[
            'id' => 60,
            'region_slugs' => ['mien-nam'],
            'destination_slugs' => ['viet-nam', 'campuchia', 'lao'],
        ]];

        $result = $this->method->invoke($this->service, $cards, 'campuchia', 'inbound');

        $this->assertSame(60, $result['id']);
    }
}
