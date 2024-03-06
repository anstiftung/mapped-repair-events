<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\I18n\DateTime;
use Cake\I18n\Date;

class EventsFixture extends TestFixture
{
    public $import = ['table' => 'events'];

    public function init(): void
    {
        $this->records = [
            [
                'uid' => 6,
                'workshop_uid' => 2,
                'eventbeschreibung' => 'description',
                'datumstart' => new Date('2040-01-01'),
                'uhrzeitstart' => new DateTime('09:00'),
                'uhrzeitend' => new DateTime('18:00'),
                'use_custom_coordinates' => 1,
                'lat' => 52.520008,
                'lng' => 13.404954,
                'status' => 1,
                'owner' => 1
            ]
        ];
        parent::init();
    }

}
?>