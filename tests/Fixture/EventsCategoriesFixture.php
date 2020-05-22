<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class EventsCategoriesFixture extends TestFixture
{
    public $import = ['table' => 'events_categories', 'connection' => 'default'];

    public function init(): void
    {
        $this->records = [
            [
                'event_uid' => 6,
                'category_id' => 87,
            ]
        ];
        parent::init();
    }

}
?>