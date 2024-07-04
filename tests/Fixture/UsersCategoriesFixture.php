<?php

namespace App\Test\Fixture;

class UsersCategoriesFixture extends AppFixture
{
    public $import = ['table' => 'users_categories'];

    public array $records = [
        [
            'user_uid' => '2',
            'category_id' => '87',
        ],
        [
            'user_uid' => '1',
            'category_id' => '630',
        ],
    ];

}
?>