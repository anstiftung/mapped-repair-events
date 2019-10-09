<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class RootsFixture extends TestFixture
{
    public $import = ['table' => 'roots', 'connection' => 'default'];

    public $records = [
        [
            'uid' => 1,
            'type' => 'users'
        ],
        [
            'uid' => 2,
            'type' => 'workshops'
        ],
        [
            'uid' => 3,
            'type' => 'users'
        ],
        [
            'uid' => 4,
            'type' => 'posts'
        ],
        [
            'uid' => 5,
            'type' => 'pages'
        ],
        [
            'uid' => 6,
            'type' => 'events'
        ],
    ];
}
?>