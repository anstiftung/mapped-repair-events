<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Services\GeoService;
use App\Test\TestCase\AppTestCase;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\QueueTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Event\EventInterface;
use Cake\Controller\Controller;
use App\Test\Mock\GeoServiceMock;

class EventsControllerTest extends AppTestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use EmailTrait;
    use StringCompareTrait;
    use LogFileAssertionsTrait;
    use QueueTrait;

    /**
     * @var array<string, mixed>
     */
    private array $newEventData;

	public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
		parent::controllerSpy($event, $controller);
		$this->_controller->geoService = new GeoServiceMock();
	}

    public function loadNewEventData(): void
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
            'lng' => '',
            'province_id' => '1',
        ];
    }

    public function testAddEventValidations(): void
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
        $this->assertResponseContains('Bitte trage ein Datum ein.');
        $this->assertResponseContains('Bitte trage eine von-Uhrzeit ein.');
        $this->assertResponseContains('Bitte trage eine bis-Uhrzeit ein.');
        $this->assertResponseContains(GeoService::ERROR_OUT_OF_BOUNDING_BOX);

    }

    public function testAddEventsOk(): void
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

        $eventsTable = $this->getTableLocator()->get('Events');
        $events = $eventsTable->find('all', contain: [
            'Categories',
        ])->toArray();

        $eventIndexA = 3;
        $this->assertEquals(5, count($events));
        $this->assertEquals($events[$eventIndexA]->eventbeschreibung, 'description<img src="n" alt="n" />');
        $this->assertEquals($events[$eventIndexA]->strasse, $this->newEventData['strasse']);
        $this->assertEquals($events[$eventIndexA]->datumstart, new Date($this->newEventData['datumstart']));
        $this->assertEquals($events[$eventIndexA]->uhrzeitstart, new Time($this->newEventData['uhrzeitstart']));
        $this->assertEquals($events[$eventIndexA]->uhrzeitend, new Time($this->newEventData['uhrzeitend']));
        $this->assertEquals($events[$eventIndexA]->categories[0]->id, $this->newEventData['categories']['_ids'][0]);
        $this->assertEquals($events[$eventIndexA]->owner, 1);
        $this->assertEquals($events[$eventIndexA]->workshop_uid, 2);
        $this->assertEquals($events[$eventIndexA]->province_id, 1);

        $eventIndexB = 4;
        $this->assertEquals($events[$eventIndexB]->datumstart, new Date($newEventData2['datumstart']));
        $this->assertEquals($events[$eventIndexB]->uhrzeitstart, new Time($newEventData2['uhrzeitstart']));
        $this->assertEquals($events[$eventIndexB]->uhrzeitend, new Time($newEventData2['uhrzeitend']));
        $this->assertEquals($events[$eventIndexB]->province_id, 1);

        $this->assertMailCount(0);

    }

    public function testEditEventWithoutNotifications(): void
    {
        $data = [
            'renotify' => false,
            'eventbeschreibung' => 'new description',
            'strasse' => 'new street',
            'zip' => '46464',
            'ort' => 'testort',
            'land' => 'de',
            'datumstart' => '02.01.2030',
            'uhrzeitstart' => '10:00',
            'uhrzeitend' => '11:00',
            'use_custom_coordinates' => true,
            'lat' => '48.1291558',
            'lng' => '11.3626812',
            'status' => APP_ON,
        ];
        $this->doTestEditForm($data);
        $this->assertMailCount(0);
    }

    public function testEditEventWithNotifications(): void
    {
        $data = [
            'renotify' => true,
            'status' => APP_OFF,
            'eventbeschreibung' => 'new description',
            'strasse' => 'new street',
            'zip' => '46464',
            'ort' => 'testort',
            'datumstart' => '02.01.2030',
            'uhrzeitstart' => '10:00',
            'uhrzeitend' => '11:00',
            'use_custom_coordinates' => true,
            'lat' => '48.1291558',
            'lng' => '11.3626812',
            'is_online_event' => true,
        ];

        $this->doTestEditForm($data);
        $this->runAndAssertQueue();

        $this->assertMailCount(1);
        $this->assertMailSentToAt(0, 'worknews-test@mailinator.com');
        $this->assertMailContainsAt(0, '- Die Veranstaltung wurde deaktiviert.');
        $this->assertMailContainsAt(0, '- Das Datum der Veranstaltung wurde von Sonntag, 01.01.2040 auf <b>Mittwoch, 02.01.2030</b> geändert.');
        $this->assertMailContainsAt(0, '- Neue Uhrzeit: <b>10:00 - 11:00 Uhr</b>');
        $this->assertMailContainsAt(0, '- Neuer Veranstaltungsort: <b>testort, new street</b>');
        $this->assertMailContainsAt(0, '- Die Veranstaltung findet jetzt als <b>Online-Veranstaltung</b> statt.');
    }

    public function testAjaxGetAllEventsForMap(): void
    {
        $this->configRequest([
            'headers' => [
                'X_REQUESTED_WITH' => 'XMLHttpRequest'
            ]
        ]);
        $expectedResult = file_get_contents(TESTS . 'comparisons' . DS . 'events-for-map.json');
        $expectedNextEventDate = Date::now()->addDays(7)->format('Y-m-d');
        $expectedResult = $this->correctExpectedDate($expectedResult, $expectedNextEventDate);
        $this->get('/events/ajaxGetAllEventsForMap');
        $this->assertResponseContains($expectedResult);
        $this->assertResponseOk();
    }

    public function testDeleteEventWithInfoMail(): void
    {

        $eventsTable = $this->getTableLocator()->get('Events');
        $eventUid = 6;
        $event = $eventsTable->get($eventUid);
        $event->datumstart = Date::now()->addDays(5);
        $eventsTable->save($event);

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventDelete(6));
        $this->runAndAssertQueue();

        $event = $eventsTable->get($eventUid);
        $this->assertEquals($event->status, APP_DELETED);
        $this->assertMailCount(1);
        $this->assertMailSentToAt(0, 'worknews-test@mailinator.com');
        $this->assertMailContainsAt(0, 'Die von dir abonnierte Initiative <b>Test Workshop</b> hat folgende Veranstaltung gelöscht');
    }

    public function testDeleteEventWithoutInfoMail(): void
    {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventDelete(6));
        $this->runAndAssertQueue();

        $eventsTable = $this->getTableLocator()->get('Events');
        $event = $eventsTable->get(6);
        $this->assertEquals($event->status, APP_DELETED);
        $this->assertMailCount(0);
    }

    /**
     * @param array<string, string|int|bool> $data
     */
    private function doTestEditForm(array $data): void
    {
        $eventsTable = $this->getTableLocator()->get('Events');
        $event = $eventsTable->find('all', conditions: [
            'Events.uid' => 6,
        ])->first();

        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventEdit($event->uid),
            [
                'referer' => '/',
                $data,
            ]
        );

        $event = $eventsTable->find('all',
            conditions: [
                'Events.uid' => 6,
            ]
        )->first();

        $this->assertEquals($event->eventbeschreibung, $data['eventbeschreibung']);
        $this->assertEquals($event->strasse, $data['strasse']);
        $this->assertEquals($event->datumstart, new Date($data['datumstart']));
        $this->assertEquals($event->uhrzeitstart, new Time($data['uhrzeitstart']));
        $this->assertEquals($event->uhrzeitend, new Time($data['uhrzeitend']));

    }

    public function testIcalForWorkshop(): void
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventIcal(2));
        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'text/calendar');
        $this->assertHeaderContains('Content-Disposition', 'attachment; filename="2.ics"');
        $this->assertResponseContains('BEGIN:VCALENDAR');
        $this->assertResponseContains('PRODID:-//eluceo/ical//2.0/EN');
        $this->assertResponseContains('VERSION:2.0');
        $this->assertResponseContains('CALSCALE:GREGORIAN');
        $this->assertResponseContains('BEGIN:VEVENT');
        $this->assertResponseContains('SUMMARY:Test Workshop');
        $this->assertResponseContains('DESCRIPTION:description');
        $this->assertResponseContains('LOCATION:Müllerstraße 123  Berlin Haus Drei');
    }

    public function testIcalAll(): void
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventIcalAll());
        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'text/calendar');
        $this->assertHeaderContains('Content-Disposition', 'attachment; filename="events.ics"');
    }

    public function testAddDuplicateEventValidation(): void
    {
        $this->loadNewEventData();
        $this->loginAsOrga();
        
        // Prepare valid event data
        $this->newEventData['workshop_uid'] = 2;
        $this->newEventData['ort'] = 'Berlin';
        $this->newEventData['strasse'] = 'Demo Street 1';
        $this->newEventData['zip'] = '10999';
        $this->newEventData['lat'] = '48,1291558';
        $this->newEventData['lng'] = '11,3626812';
        $this->newEventData['datumstart'] = '01.01.2025';
        $this->newEventData['uhrzeitstart'] = '10:00';
        $this->newEventData['uhrzeitend'] = '12:00';

        // Create the first event successfully
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            [
                'referer' => '/',
                $this->newEventData
            ]
        );
        $this->assertResponseCode(302); // Redirect on success

        // Try to create a duplicate event with same workshop, date, and times
        $duplicateEventData = $this->newEventData;
        $duplicateEventData['strasse'] = 'Different Street 123'; // Different street to ensure it's not location based
        
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            [
                'referer' => '/',
                $duplicateEventData
            ]
        );
        
        // Should show validation error
        $this->assertResponseContains('Es existiert bereits ein Termin für diese Initiative zur gleichen Zeit an diesem Tag.');
    }

    public function testEditEventDoesNotTriggerDuplicateValidation(): void
    {
        $this->loadNewEventData();
        $this->loginAsOrga();
        
        // Use existing event from fixture (uid: 6)
        $editData = [
            'uid' => 6,
            'workshop_uid' => 2,
            'eventbeschreibung' => 'Updated description',
            'ort' => 'Berlin',
            'strasse' => 'Demo Street 1',
            'zip' => '10999',
            'lat' => '48,1291558',
            'lng' => '11,3626812',
            'datumstart' => '01.01.2040', // Same as fixture
            'uhrzeitstart' => '09:00',     // Same as fixture
            'uhrzeitend' => '18:00',       // Same as fixture
            'use_custom_coordinates' => true,
            'province_id' => '1',
        ];

        // Edit existing event - should work even with same date/times because it excludes itself
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventEdit(6),
            [
                'referer' => '/',
                $editData
            ]
        );
        
        // Should NOT show duplicate validation error
        $this->assertResponseNotContains('Es existiert bereits ein Termin für diese Initiative zur gleichen Zeit an diesem Tag.');
        // Should redirect on success or show form without validation errors
        $this->assertResponseCode(302); // Redirect on success
    }

}
?>