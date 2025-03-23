<?php
declare(strict_types=1);
namespace App\Test\Mock;

class GeoServiceMock {

    public function getGeoDataByCoordinates(string $lat, string $lng): array
    {
        return [
            'provinceId' => 1,
        ];
    }

    public function getGeoDataByAddress(string $address): array
    {
        return [
            'lat' => 52.520008,
            'lng' => 13.404954,
            'provinceId' => 1,
        ];
    }

}

?>