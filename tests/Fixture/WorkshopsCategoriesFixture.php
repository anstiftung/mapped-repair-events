<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class WorkshopsCategoriesFixture extends TestFixture
{
    public $import = ['table' => 'workshops_categories', 'connection' => 'default'];

    public $records = [
        [
            'workshop_uid' => '2',
            'category_id' => '630',
        ],
    ];

}
?>