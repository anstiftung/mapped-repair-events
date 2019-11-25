<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

class EventsFixture extends TestFixture
{
    public $import = ['table' => 'events', 'connection' => 'default'];
    
    public function init()
    {
        $this->records = [
            [
                'uid' => 6,
                'workshop_uid' => 2,
                'eventbeschreibung' => 'description',
                'datumstart' => new FrozenDate('2040-01-01'),
                'uhrzeitstart' => new FrozenTime('09:00'),
                'uhrzeitend' => new FrozenTime('18:00'),
                'use_custom_coordinates' => 1,
                'lat' => 1.1,
                'lng' => 1.2,
                'status' => 1,
                'owner' => 1
            ]
        ];
        parent::init();
    }
    
}
?>