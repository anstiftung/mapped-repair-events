<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BlogsFixture extends TestFixture
{
    public $import = ['table' => 'blogs', 'connection' => 'default'];

    public array $records = [
        [
            'id' => 1,
            'name' => 'Neuigkeiten',
            'url' => 'neuigkeiten'
        ]
    ];

}
?>