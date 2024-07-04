<?php

namespace App\Test\Fixture;

class BlogsFixture extends AppFixture
{
    public $import = ['table' => 'blogs'];

    public array $records = [
        [
            'id' => 1,
            'name' => 'Neuigkeiten',
            'url' => 'neuigkeiten'
        ]
    ];

}
?>