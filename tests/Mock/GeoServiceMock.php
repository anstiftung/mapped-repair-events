<?php
declare(strict_types=1);
namespace App\Test\Mock;

class GeoServiceMock {

    public function getGeoDataByCoordinates($lat, $lng): array
    {
        return [
            'provinceId' => 1,
        ];
    }

    public function getGeoDataByAddress($address): array
    {
        return [
            'lat' => 52.520008,
            'lng' => 13.404954,
            'provinceId' => 1,
        ];
    }

}

?>