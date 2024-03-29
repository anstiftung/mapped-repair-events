<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PagesFixture extends TestFixture
{
    public $import = ['table' => 'pages'];

    public array $records = [
        [
            'uid' => 5,
            'name' => 'Test Page',
            'text' => '<b>some html</b>',
            'url' => 'test-page',
            'status' => APP_ON
        ]
    ];

}
?>