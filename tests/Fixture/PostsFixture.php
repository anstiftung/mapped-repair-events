<?php

namespace App\Test\Fixture;

class PostsFixture extends AppFixture
{
    public $import = ['table' => 'posts'];

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