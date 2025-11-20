<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\I18n\DateTime;
use Cake\I18n\Date;

class EventsFixture extends AppFixture
{
    
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
                'owner' => 1,
                'province_id' => 1,
            ],
            [
                'uid' => 9,
                'workshop_uid' => 2,
                'eventbeschreibung' => 'description',
                'ort' => 'Berlin',
                'strasse' => 'Müllerstraße 123',
                'veranstaltungsort' => 'Haus Drei',
                'datumstart' => Date::now()->addDays(7),
                'uhrzeitstart' => new DateTime('09:00'),
                'uhrzeitend' => new DateTime('18:00'),
                'use_custom_coordinates' => 1,
                'lat' => 52.520008,
                'lng' => 13.404954,
                'status' => 1,
                'owner' => 1,
                'province_id' => 0.,
            ],
            [
                'uid' => 12,
                'workshop_uid' => 11,
                'eventbeschreibung' => 'description',
                'ort' => 'Berlin',
                'strasse' => 'Müllerstraße 123',
                'veranstaltungsort' => 'Haus Drei',
                'datumstart' => Date::now()->subDays(7),
                'uhrzeitstart' => new DateTime('09:00'),
                'uhrzeitend' => new DateTime('18:00'),
                'use_custom_coordinates' => 1,
                'lat' => 52.520008,
                'lng' => 13.404954,
                'status' => 1,
                'owner' => 1,
                'province_id' => 0,
            ],
        ];
        parent::init();
    }

}
?>