<?php

namespace App\Test\Fixture;

class PagesFixture extends AppFixture
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