<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PostsFixture extends TestFixture
{
    public $import = ['table' => 'posts', 'connection' => 'default'];

    public array $records = [
        [
            'uid' => 4,
            'name' => 'Test Post',
            'text' => '<b>some html</b>',
            'url' => 'test-post',
            'blog_id' => 1,
            'publish' => '2019-10-09 08:23:23',
            'status' => APP_ON
        ]
    ];

}
?>