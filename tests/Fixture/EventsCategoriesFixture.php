<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\I18n\FrozenDate;

class EventsCategoriesFixture extends TestFixture
{
    public $import = ['table' => 'events_categories', 'connection' => 'default'];
    
    public function init()
    {
        $this->records = [
            [
                'event_uid' => 7,
                'category_id' => 87,
            ]
        ];
        parent::init();
    }
    
}
?>