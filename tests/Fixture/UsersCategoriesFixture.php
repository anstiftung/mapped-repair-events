<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersCategoriesFixture extends TestFixture
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