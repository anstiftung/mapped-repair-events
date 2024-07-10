<?php
namespace App\Test\Mock;

class GeoServiceMock {

    public function getGeoDataByCoordinates($lat, $lng): array
    {
        return ['provinceId' => 1];
    }

}

?>