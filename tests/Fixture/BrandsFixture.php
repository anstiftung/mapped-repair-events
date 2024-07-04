<?php

namespace App\Test\Fixture;

class BrandsFixture extends AppFixture
{
    public $import = ['table' => 'brands'];

    public array $records = [
        [
            'id' => 1,
            'name' => 'Abacom',
            'status' => 1
        ]
    ];

}
?>