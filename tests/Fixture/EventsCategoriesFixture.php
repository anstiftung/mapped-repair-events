<?php

namespace App\Test\Fixture;

class EventsCategoriesFixture extends AppFixture
{
    public $import = ['table' => 'events_categories'];

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