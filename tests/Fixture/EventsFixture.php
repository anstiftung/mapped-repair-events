<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class EventsFixture extends TestFixture
{
    public $import = ['table' => 'events', 'connection' => 'default'];

    public function init(): void
    {
        $this->records = [
            [
                'uid' => 6,
                'workshop_uid' => 2,
                'eventbeschreibung' => 'description',
                'datumstart' => new \Cake\I18n\Date('2040-01-01'),
                'uhrzeitstart' => new \Cake\I18n\DateTime('09:00'),
                'uhrzeitend' => new \Cake\I18n\DateTime('18:00'),
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