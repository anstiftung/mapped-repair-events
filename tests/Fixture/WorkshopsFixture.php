<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class WorkshopsFixture extends TestFixture
{
    public $import = ['table' => 'workshops'];

    public array $records = [
        [
            'uid' => 2,
            'name' => 'Test Workshop',
            'url' => 'test-workshop',
            'email' => 'test-workshop@mailinator.com',
            'owner' => 1,
            'city' => 'Berlin',
            'zip' => '10115',
            'street' => 'Torstraße 123',
            'adresszusatz' => 'Stiege 2 &amp; Stiege 3',
            'lat' => 52.532,
            'lng' => 13.384,
            'status' => APP_ON,
        ]
    ];

}
?>