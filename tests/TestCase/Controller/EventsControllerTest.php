<?php

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\Traits\LoadAllFixturesTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

class EventsControllerTest extends TestCase
{
    use LoginTrait;
    use IntegrationTestTrait;
    use UserAssertionsTrait;
    use EmailTrait;
    use StringCompareTrait;
    use LogFileAssertionsTrait;
    use LoadAllFixturesTrait;
    
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
                'Events' => $this->newEventData
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
    
    public function testAddEventOk()
    {
        $this->loadNewEventData();
        $this->loginAsOrga();
        $this->newEventData['eventbeschreibung'] = 'description</title></script><img src=n onerror=alert("x")>';
        $this->newEventData['datumstart'] = '01.01.2020';
        $this->newEventData['ort'] = 'Berlin';
        $this->newEventData['strasse'] = 'Demo Street 1';
        $this->newEventData['zip'] = '10999';
        $this->newEventData['lat'] = '48,1291558';
        $this->newEventData['lng'] = '11,3626812';
        $this->newEventData['uhrzeitstart'] = '10:00';
        $this->newEventData['uhrzeitend'] = '20:00';
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            [
                'referer' => '/',
                'Events' => $this->newEventData
            ]
        );
        $this->assertResponseNotContains('error');
        
        $this->Event = TableRegistry::getTableLocator()->get('Events');
        $events = $this->Event->find('all', [
            'contain' => [
                'Categories',
            ]
        ])->toArray();
        $this->assertEquals(2, count($events));
        $this->assertEquals($events[1]->eventbeschreibung, 'description');
        $this->assertEquals($events[1]->strasse, $this->newEventData['strasse']);
        $this->assertEquals($events[1]->datumstart, new FrozenDate($this->newEventData['datumstart']));
        $this->assertEquals($events[1]->uhrzeitstart, new FrozenTime($this->newEventData['uhrzeitstart']));
        $this->assertEquals($events[1]->uhrzeitend, new FrozenTime($this->newEventData['uhrzeitend']));
        $this->assertEquals($events[1]->categories[0]->id, $this->newEventData['categories']['_ids'][0]);
        $this->assertEquals($events[1]->owner, 1);
        
        $this->assertMailCount(0);
        
    }
    
    public function testEditEventWithoutNotifications()
    {
        $this->doTestEditForm(false);
        $this->assertMailCount(0);
    }
    
    public function testEditEventWithNotifications()
    {
        $this->doTestEditForm(true);
        $this->assertMailCount(1);
        $this->assertMailSentTo('worknews-test@example.com');
    }
    
    private function doTestEditForm($renotify)
    {
        $this->Event = TableRegistry::getTableLocator()->get('Events');
        $event = $this->Event->find('all', [
            'conditions' => [
                'Events.uid' => 6
            ]
        ])->first();
        $event->datumstart = $event->datumstart->i18nFormat(Configure::read('DateFormat.de.DateLong2'));
        
        $eventForPost = [
            'eventbeschreibung' => 'new description',
            'strasse' => 'new street',
            'datumstart' => '02.01.2030',
            'uhrzeitstart' => '10:00',
            'uhrzeitend' => '11:00',
            'use_custom_coordinates' => $event->use_custom_coordinates,
            'lat' => $event->lat,
            'lng' => $event->lng,
            'renotify' => $renotify
        ];
        
        $this->loginAsOrga();
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventEdit($event->uid),
            [
                'referer' => '/',
                'Events' => $eventForPost
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