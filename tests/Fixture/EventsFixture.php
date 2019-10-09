<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\I18n\FrozenDate;

class EventsFixture extends TestFixture
{
    public $import = ['table' => 'events', 'connection' => 'default'];
    
    public function init()
    {
        $this->records = [
            [
                'uid' => 6,
                'workshop_uid' => 2,
                'datumstart' => new FrozenDate('2040-01-01'),
                'status' => 1
            ]
        ];
        parent::init();
    }
    
}
?>