<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class WorkshopsFixture extends TestFixture
{
    public $import = ['table' => 'workshops', 'connection' => 'default'];

    public array $records = [
        [
            'uid' => 2,
            'name' => 'Test Workshop',
            'url' => 'test-workshop',
            'email' => 'test-workshop@mailinator.com',
            'owner' => 1,
            'status' => APP_ON,
        ]
    ];

}
?>