<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class CitiesFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'geonameid' => 1,
                'name' => 'Potsdam',
                'latitude' => 52.390569,
                'longitude' => 13.064472,
            ]
        ];
        parent::init();
    }

}
?>