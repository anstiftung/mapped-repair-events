<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BrandsFixture extends TestFixture
{
    public $import = ['table' => 'brands', 'connection' => 'default'];

    public array $records = [
        [
            'id' => 1,
            'name' => 'Abacom',
            'status' => 1
        ]
    ];

}
?>