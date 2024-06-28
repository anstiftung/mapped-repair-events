<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ProvincesFixture extends TestFixture
{
    public $import = ['table' => 'provinces'];

    public array $records = [
        [
            'id' => 1,
            'name' => 'Bayern',
            'country_code' => 'DE',
        ]
    ];

}
?>