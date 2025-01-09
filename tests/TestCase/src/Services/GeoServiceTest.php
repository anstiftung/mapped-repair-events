<?php
declare(strict_types=1);

namespace App\Test\TestCase\Services;

use App\Services\GeoService;
use Cake\TestSuite\TestCase;

class GeoServiceTest extends TestCase
{
    public function testIsPointInBoundingBox()
    {
        $GeoService = new GeoService();

        $this->assertFalse($GeoService->isPointInBoundingBox(0, 0));

        // OK
        $this->assertTrue($GeoService->isPointInBoundingBox(52.520008, 13.404954)); // berlin
        $this->assertTrue($GeoService->isPointInBoundingBox(48.135125, 11.581981)); // munich
        $this->assertTrue($GeoService->isPointInBoundingBox(40.416775, -3.703790)); // madrid

        // NOT OK
        $this->assertFalse($GeoService->isPointInBoundingBox(40.712776, -74.005974)); // new york
        $this->assertFalse($GeoService->isPointInBoundingBox(34.052235, -118.243683)); // los angeles
        $this->assertFalse($GeoService->isPointInBoundingBox(35.689487, 139.691711)); // tokio

    }

}