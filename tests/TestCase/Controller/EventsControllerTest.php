<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

class EventsControllerTest extends AppTestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use EmailTrait;
    use StringCompareTrait;
    use LogFileAssertionsTrait;

    private $newEventData;

    public function loadNewEventData()
    {
        $this->newEventData = [
            'eventbeschreibung' => 'description',
            'datumstart' => '',
            'uhrzeitstart' => '',
            'uhrzeitend' => '',
            'veranstaltungsort' => 'Room 1',
            'strasse' => '',
            'zip' => '',
            'ort' => '',
            'author' => 'John Doe',
            'categories' => [
                '_ids' => [
                    '0' => 87
                ]
            ],
            'use_custom_coordinates' => true,
            'lat' => '',
            'lng' => ''
        ];
    }

    public function testAddEventValidations()
    {
        $this->loadNewEventData();
        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            [
                'referer' => '/',
                $this->newEventData
            ]
        );
        $this->assertResponseContains('Bitte trage die Stadt ein.');
        $this->assertResponseContains('Bitte trage die Straße ein.');
        $this->assertResponseContains('Bitte trage die PLZ ein.');
        $this->assertResponseContains('Die Eingabe muss eine Zahl zwischen -90 und 90 sein.');
        $this->assertResponseContains('Die Eingabe muss eine Zahl zwischen -180 und 180 sein.');
        $this->assertResponseContains('Bitte trage ein Datum ein.');
        $this->assertResponseContains('Bitte trage eine von-Uhrzeit ein.');
        $this->assertResponseContains('Bitte trage eine bis-Uhrzeit ein.');
    }

    public function testAddEventsOk()
    {
        $this->loadNewEventData();
        $this->loginAsOrga();
        $this->newEventData['eventbeschreibung'] = 'description</title></script><img src=n onerror=alert("x")>';
        $this->newEventData['workshop_uid'] = 2;
        $this->newEventData['ort'] = 'Berlin';
        $this->newEventData['strasse'] = 'Demo Street 1';
        $this->newEventData['zip'] = '10999';
        $this->newEventData['lat'] = '48,1291558';
        $this->newEventData['lng'] = '11,3626812';
        $this->newEventData['datumstart'] = '01.01.2020';
        $this->newEventData['uhrzeitstart'] = '10:00';
        $this->newEventData['uhrzeitend'] = '20:00';

        $newEventData2 = [
            'datumstart' => '01.02.2020',
            'uhrzeitstart' => '12:00',
            'uhrzeitend' => '22:00',
        ];

        $data = [
            'referer' => '/',
            $this->newEventData,
            $newEventData2,
        ];
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            $data,
        );
        $this->assertResponseNotContains('error');

        $this->Event = $this->getTableLocator()->get('Events');
        $events = $this->Event->find('all', [
            'contain' => [
                'Categories',
            ]
        ])->toArray();

        $this->assertEquals(3, count($events));
        $this->assertEquals($events[1]->eventbeschreibung, 'description<img src="n" alt="n" />');
        $this->assertEquals($events[1]->strasse, $this->newEventData['strasse']);
        $this->assertEquals($events[1]->datumstart, new FrozenDate($this->newEventData['datumstart']));
        $this->assertEquals($events[1]->uhrzeitstart, new FrozenTime($this->newEventData['uhrzeitstart']));
        $this->assertEquals($events[1]->uhrzeitend, new FrozenTime($this->newEventData['uhrzeitend']));
        $this->assertEquals($events[1]->categories[0]->id, $this->newEventData['categories']['_ids'][0]);
        $this->assertEquals($events[1]->owner, 1);
        $this->assertEquals($events[1]->workshop_uid, 2);

        $this->assertEquals($events[2]->datumstart, new FrozenDate($newEventData2['datumstart']));
        $this->assertEquals($events[2]->uhrzeitstart, new FrozenTime($newEventData2['uhrzeitstart']));
        $this->assertEquals($events[2]->uhrzeitend, new FrozenTime($newEventData2['uhrzeitend']));

        $this->assertMailCount(0);

    }

    public function testEditEventWithoutNotifications()
    {
        $this->doTestEditForm(false);
        $this->assertMailCount(0);
    }

    public function testEditEventWithNotifications()
    {
        $this->Event = $this->getTableLocator()->get('Events');
        $patchedEntity = $this->Event->patchEntity(
            $this->Event->get(6),
            ['status' => APP_OFF]
        );
        $this->Event->save($patchedEntity);
        $this->doTestEditForm(true);
        $this->assertMailCount(1);
        $this->assertMailSentTo('worknews-test@mailinator.com');
    }

    public function testAjaxGetAllEventsForMap()
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest'
            ]
        ]);
        $this->_compareBasePath = ROOT . DS . 'tests' . DS . 'comparisons' . DS;
        $this->get('/events/ajaxGetAllEventsForMap');
        $this->assertSameAsFile('events-for-map.json', $this->_response);
    }

    public function testDeleteEvent()
    {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventDelete(6));
        $this->Event = $this->getTableLocator()->get('Events');
        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => 6
            ]
        ])->first();
        $this->assertEquals($event->status, APP_DELETED);
        $this->assertMailCount(1);
        $this->assertMailSentTo('worknews-test@mailinator.com');
    }

    private function doTestEditForm($renotify)
    {
        $this->Event = $this->getTableLocator()->get('Events');
        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => 6
            ]
        ])->first();

        $eventForPost = [
            'eventbeschreibung' => 'new description',
            'strasse' => 'new street',
            'datumstart' => '02.01.2030',
            'uhrzeitstart' => '10:00',
            'uhrzeitend' => '11:00',
            'use_custom_coordinates' => $event->use_custom_coordinates,
            'lat' => $event->lat,
            'lng' => $event->lng,
            'status' => APP_ON,
            'renotify' => $renotify
        ];

        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventEdit($event->uid),
            [
                'referer' => '/',
                $eventForPost
            ]
        );

        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => 6
            ]
        ])->first();

        $this->assertEquals($event->eventbeschreibung, $eventForPost['eventbeschreibung']);
        $this->assertEquals($event->strasse, $eventForPost['strasse']);
        $this->assertEquals($event->datumstart, new FrozenDate($eventForPost['datumstart']));
        $this->assertEquals($event->uhrzeitstart, new FrozenTime($eventForPost['uhrzeitstart']));
        $this->assertEquals($event->uhrzeitend, new FrozenTime($eventForPost['uhrzeitend']));

    }

}
?>