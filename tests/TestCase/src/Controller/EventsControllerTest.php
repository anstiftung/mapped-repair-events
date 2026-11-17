<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Log\Log;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\I18n\DateTime;
use Cake\Core\Configure;
use App\Services\GeoService;
use Cake\Event\EventInterface;
use Cake\TestSuite\EmailTrait;
use Cake\Controller\Controller;
use App\Test\Mock\GeoServiceMock;
use App\Test\TestCase\AppTestCase;
use Cake\TestSuite\StringCompareTrait;
use App\Test\TestCase\Traits\LoginTrait;
use App\Test\TestCase\Traits\QueueTrait;
use Cake\TestSuite\IntegrationTestTrait;
use App\Test\TestCase\Traits\UserAssertionsTrait;
use App\Test\TestCase\Traits\LogFileAssertionsTrait;

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

    private bool $failSeriesSave = false;

    public function tearDown(): void
    {
        DateTime::setTestNow();
        parent::tearDown();
    }

    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
     */
    public function controllerSpy(EventInterface $event, ?Controller $controller = null): void
    {
		parent::controllerSpy($event, $controller);
		$this->_controller->geoService = new GeoServiceMock();

        if ($this->failSeriesSave) {
            $attempt = 0;
            $this->getTableLocator()->get('Events')->getEventManager()->on('Model.beforeSave', function (EventInterface $saveEvent) use (&$attempt): void {
                $attempt++;
                if ($attempt === 2) {
                    $saveEvent->stopPropagation();
                    $saveEvent->setResult(false);
                }
            });
        }
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

    /** @return array<string, mixed> */
    private function seriesSettings(): array
    {
        DateTime::setTestNow(new DateTime('2040-12-01'));
        return [
            'enabled' => '1', 'rhythm' => 'monthly-1',
            'monthly_rules' => [['weekday' => 'SA', 'position' => '2']],
            'end' => '31.03.2041',
        ];
    }

    /** @return array<string, mixed> */
    private function seriesEventData(): array
    {
        return [
            'eventbeschreibung' => 'Serientermin', 'workshop_uid' => 2,
            'datumstart' => '01.01.2041',
            'uhrzeitstart' => '14:00', 'uhrzeitend' => '17:00',
            'strasse' => 'Teststrasse 1', 'zip' => '10115', 'ort' => 'Berlin', 'land' => 'de',
            'use_custom_coordinates' => '1', 'lat' => '52.520008', 'lng' => '13.404954',
            'status' => APP_ON, 'categories' => ['_ids' => [87]],
        ];
    }

    private function createSeriesThroughForm(): void
    {
        $this->loginAsOrga();
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), 'series' => $this->seriesSettings(),
        ]);
        $this->assertResponseCode(302);
    }

    public function testCreateSeriesAndHideSeriesEditing(): void
    {
        $this->createSeriesThroughForm();
        $events = $this->getTableLocator()->get('Events')->find()->where(['series_uid IS NOT' => null])->all()->toList();
        $this->assertCount(3, $events);
        $this->assertSame($events[0]->series_uid, $events[2]->series_uid);
        $settings = $this->getTableLocator()->get('EventSeries')->get($events[0]->series_uid)->get('settings');
        $this->assertSame('monthly', $settings['frequency']);
        $this->assertSame(1, $settings['interval']);
        $this->assertSame('2041-03-31', $settings['end']);
        $this->assertArrayNotHasKey('rhythm', $settings);
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventEdit($events[0]->uid));
        $this->assertResponseOk();
        $this->assertResponseNotContains('class="event-series"');
        $this->assertResponseNotContains('name="series[scope]"');
        $this->assertResponseNotContains('name="series[rhythm]"');
        $this->assertResponseContains('Dieser Termin ist Teil einer Terminserie: jeden Monat (2. Samstag), bis 31.03.2041');
        $this->assertResponseNotContains('weiteren Terminen');
    }

    public function testWeeklySeriesDescriptionAppearsBelowStartDate(): void
    {
        $this->loginAsOrga();
        $settings = $this->seriesSettings();
        $settings['rhythm'] = 'weekly-2';
        $settings['weekdays'] = ['WE', 'TH'];
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), 'series' => $settings,
        ]);
        $this->assertResponseCode(302);
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventEdit($events[0]->uid));
        $this->assertResponseOk();
        $formDocument = new \DOMDocument();
        $formDocument->loadHTML((string)$this->_response->getBody(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $formXpath = new \DOMXPath($formDocument);
        $this->assertSame('Dieser Termin ist Teil einer Terminserie: jede 2. Woche (Mittwoch und Donnerstag), bis 31.03.2041', $formXpath->evaluate('string(//input[@id="0-datumstart"]/following-sibling::p[@class="series-membership-hint"])'));
        $this->assertResponseNotContains('weiteren Terminen');
        $this->get(Configure::read('AppConfig.htmlHelper')->urlMyEvents());
        $this->assertResponseOk();
        $this->assertResponseContains('title="Dieser Termin ist Teil einer Terminserie: jede 2. Woche (Mittwoch und Donnerstag), bis 31.03.2041"');
    }

    public function testCompactSeriesForm(): void
    {
        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventNew(2));
        $this->assertResponseOk();
        $this->assertResponseContains('name="series[rhythm]"');
        $this->assertResponseContains('<fieldset class="series-rule-fields" hidden>');
        $this->assertResponseContains('<div class="series-weekly-days" hidden>');
        $this->assertResponseContains('<div class="series-monthly-rules" hidden>');
        $this->assertResponseContains('<div class="series-preview-area" hidden>');
        $this->assertResponseContains('<option value="monthly-1" selected="selected">Jeden Monat</option>');
        $this->assertResponseContains('<option value="1" selected="selected">1.</option>');
        $this->assertResponseContains('<option value="SA" selected="selected">Samstag</option>');
        $this->assertResponseContains('Jeden Monat');
        $this->assertResponseContains('Jeden 2. Monat');
        $this->assertResponseContains('Jeden 3. Monat (quartalsweise)');
        $this->assertSame(3, substr_count((string)$this->_response->getBody(), '<option value="monthly-'));
        $this->assertResponseContains('Jede Woche');
        $this->assertResponseContains('Alle 2 Wochen');
        $this->assertSame(2, substr_count((string)$this->_response->getBody(), '<option value="weekly-'));
        $this->assertResponseContains('Diesen Termin wiederkehrend anlegen');
        $this->assertResponseContains('name="series[enabled]"');
        $this->assertResponseContains('name="series[monthly_rules][0][position]"');
        $this->assertResponseContains('name="series[monthly_rules][0][weekday]"');
        $this->assertResponseContains('Position im Monat');
        $this->assertResponseNotContains('<legend>Wiederholung</legend>');
        $this->assertResponseContains('class="series-weekday-options" role="group" aria-labelledby="series-weekdays-label"');
        $this->assertResponseRegExp('/<input(?=[^>]*name="series\[end\]")(?=[^>]*type="text")(?=[^>]*class="datepicker-input")[^>]*>/');
        $this->assertResponseNotContains('series-add-rule');
        $this->assertResponseNotContains('series-remove-rule');
        $this->assertSame(1, substr_count((string)$this->_response->getBody(), 'class="series-monthly-rule"'));
        $this->assertResponseContains('name="series[weekdays][]"');
        $this->assertResponseNotContains('name="series[end_mode]"');
        $this->assertResponseNotContains('name="series[interval]"');
        $this->assertLessThan(
            strpos((string)$this->_response->getBody(), 'name="0[eventbeschreibung]"'),
            strpos((string)$this->_response->getBody(), 'name="series[enabled]"'),
        );
        $this->assertResponseContains('<legend>Datum und Uhrzeit</legend>');
        $this->assertResponseContains('<legend>Beschreibung</legend>');
        $this->assertResponseContains('<legend>Veranstaltungsort</legend>');
        $this->assertResponseContains('<legend>Veröffentlichung</legend>');
        $this->assertResponseNotContains('event-date-mode-hint');
        $this->assertResponseContains('<span class="series-enabled-restriction" hidden> (nur mit einem einzigen Ausgangstermin möglich)</span>');
        $this->assertResponseRegExp('/<button(?=[^>]*type="button")(?=[^>]*class="add-date-button")[^>]*>/');
        $this->assertResponseContains('<i class="fa fa-plus-circle" aria-hidden="true"></i></button>');
        $this->assertResponseNotContains('<h2>Termin wiederholen?</h2>');
        $formDocument = new \DOMDocument();
        $formDocument->loadHTML((string)$this->_response->getBody(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $formXpath = new \DOMXPath($formDocument);
        $seriesCheckbox = $formXpath->query('//fieldset[legend="Datum und Uhrzeit"]//input[@type="checkbox" and @name="series[enabled]"]');
        $this->assertNotFalse($seriesCheckbox);
        $this->assertSame(1, $seriesCheckbox->length);
        $this->assertSame(3.0, $formXpath->evaluate('count(//section[contains(@class,"event-series")]//select)'));
        $this->assertSame(3.0, $formXpath->evaluate('count(//section[contains(@class,"event-series")]//select[@data-minimum-results-for-search="-1" and not(contains(concat(" ",normalize-space(@class)," ")," no-select2 "))])'));
        $this->assertSame(1.0, $formXpath->evaluate('count(//fieldset[legend="Veranstaltungsort"]/*[not(self::legend)][1][@class="event-location-row"]/*[1][@class="event-location-actions"]//a[contains(@class,"apply-workshop-data-button")])'));
        $this->assertSame(1.0, $formXpath->evaluate('count(//div[@class="event-location-actions"]/following-sibling::*[1]//input[@name="0[veranstaltungsort]"])'));
        $this->assertSame(0.0, $formXpath->evaluate('count(//fieldset[legend="Veranstaltungsort"]/br[not(preceding-sibling::div)])'));
        $this->assertSame('SA', $formXpath->evaluate('string(//input[@name="series[weekdays][]" and @checked]/@value)'));
        $this->assertSame(1.0, $formXpath->evaluate('count(//input[@name="series[weekdays][]" and @checked])'));
        $this->assertSame(
            \Cake\I18n\Date::now()->addYears(1)->format('d.m.Y'),
            $formXpath->evaluate('string(//input[@name="series[end]"]/@value)'),
        );
    }

    public function testMyEventsMarksOnlySeriesOccurrences(): void
    {
        $this->createSeriesThroughForm();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlMyEvents());
        $this->assertResponseOk();
        $this->assertResponseContains('Teil einer Terminserie');
        $this->assertSame(3, substr_count((string)$this->_response->getBody(), 'class="fa fa-repeat event-series-indicator"'));
        $this->assertResponseContains(' Uhr <i class="fa fa-repeat event-series-indicator"');
        $formDocument = new \DOMDocument();
        $formDocument->loadHTML((string)$this->_response->getBody(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $formXpath = new \DOMXPath($formDocument);
        $this->assertSame(3.0, $formXpath->evaluate('count(//td[contains(.," Uhr")]/i[@class="fa fa-repeat event-series-indicator" and @title="Dieser Termin ist Teil einer Terminserie: jeden Monat (2. Samstag), bis 31.03.2041" and @aria-label=@title])'));
    }

    public function testSeriesPreviewDoesNotSave(): void
    {
        $this->loginAsOrga();
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), 'series' => $this->seriesSettings() + ['preview' => '1'],
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('12.01.2041');
        $this->assertResponseContains('<h3>Terminvorschau</h3>');
        $this->assertResponseContains('<i class="fa fa-refresh" aria-hidden="true"></i> Terminvorschau aktualisieren');
        $this->assertResponseContains('Diese Terminserie enthält folgende <span class="series-preview-count">3</span> Termine. Du kannst vor dem Speichern einzelne Termine wegklicken.');
        $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
        $this->assertSame(0, $this->getTableLocator()->get('EventSeries')->find()->count());
    }

    public function testSeriesPreviewLeavesPastOccurrencesUnchecked(): void
    {
        $this->loginAsOrga();
        foreach ([[], ['2041-03-09']] as $excluded) {
            $settings = $this->seriesSettings() + ['preview' => '1', 'excluded' => $excluded];
            DateTime::setTestNow(new DateTime('2041-02-09 12:00:00'));
            $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
                'referer' => '/', $this->seriesEventData(), 'series' => $settings,
            ]);
            $this->assertResponseOk();
            $formDocument = new \DOMDocument();
            $formDocument->loadHTML((string)$this->_response->getBody(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $formXpath = new \DOMXPath($formDocument);
            $this->assertSame(3.0, $formXpath->evaluate('count(//input[@class="series-include-date"])'));
            $this->assertSame(0.0, $formXpath->evaluate('count(//input[@data-date="2041-01-12" and @checked])'));
            $this->assertSame(1.0, $formXpath->evaluate('count(//input[@data-date="2041-02-09" and @checked])'));
            $this->assertSame($excluded === [] ? 1.0 : 0.0, $formXpath->evaluate('count(//input[@data-date="2041-03-09" and @checked])'));
            $this->assertSame($excluded === [] ? '2' : '1', $formXpath->evaluate('string(//span[@class="series-preview-count"])'));
        }
    }

    public function testInvalidSeriesDoesNotPartiallySave(): void
    {
        $this->loginAsOrga();
        $data = $this->seriesEventData();
        $data['strasse'] = '';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $data, 'series' => $this->seriesSettings(),
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Bitte trage die Straße ein.');
        $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
        $this->assertSame(0, $this->getTableLocator()->get('EventSeries')->find()->count());
    }

    public function testOnlySelectedOccurrenceIsEdited(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $data = $this->seriesEventData();
        $data['datumstart'] = $events[1]->datumstart->format('d.m.Y');
        $data['uhrzeitstart'] = '15:00';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventEdit($events[1]->uid), [
            'referer' => '/', $data,
        ]);
        $this->assertResponseCode(302);
        foreach ($events as $index => $event) {
            $saved = $table->get($event->uid);
            $this->assertSame($index === 1 ? '15:00:00' : '14:00:00', $saved->uhrzeitstart->format('H:i:s'));
        }
    }

    public function testSubmittedSeriesScopeDoesNotChangeOtherOccurrences(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $table->updateAll(['datumstart' => '2020-01-01'], ['uid' => $events[0]->uid]);
        $data = $this->seriesEventData();
        $data['datumstart'] = $events[1]->datumstart->format('d.m.Y');
        $data['uhrzeitstart'] = '15:00';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventEdit($events[1]->uid), [
            'referer' => '/', $data, 'series' => ['scope' => 'following'],
        ]);
        $this->assertResponseCode(302);
        foreach ($events as $index => $event) {
            $saved = $table->get($event->uid);
            $this->assertSame($index === 1 ? '15:00:00' : '14:00:00', $saved->uhrzeitstart->format('H:i:s'));
            $this->assertSame($event->series_uid, $saved->series_uid);
        }
        $this->assertSame('2020-01-01', $table->get($events[0]->uid)->datumstart->format('Y-m-d'));
    }

    public function testSeriesSaveFailureRollsBackEverything(): void
    {
        $this->loginAsOrga();
        $this->failSeriesSave = true;
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), 'series' => $this->seriesSettings(),
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('Es wurde kein Termin gespeichert.');
        $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
        $this->assertSame(0, $this->getTableLocator()->get('EventSeries')->find()->count());
    }

    public function testExcludedOccurrenceIsNotPublished(): void
    {
        $this->loginAsOrga();
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(),
            'series' => $this->seriesSettings() + ['excluded' => ['2041-02-09']],
        ]);
        $this->assertResponseCode(302);
        $events = $this->getTableLocator()->get('Events');
        $this->assertSame(2, $events->find()->where(['series_uid IS NOT' => null, 'status' => APP_ON])->count());
        $this->assertSame(1, $events->find()->where(['series_uid IS NOT' => null, 'status' => APP_DELETED])->count());
    }

    public function testSubmittedRuleChangeDoesNotRegenerateSeries(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $series = $this->getTableLocator()->get('EventSeries');
        $originalSettings = $series->get($events[1]->series_uid)->get('settings');
        $data = $this->seriesEventData();
        $data['datumstart'] = '15.02.2041';
        $data['uhrzeitstart'] = '15:00';
        $data['series_uid'] = 'changed';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventEdit($events[1]->uid), [
            'referer' => '/', $data, 'series' => [
                'enabled' => '1', 'scope' => 'following', 'change_rule' => '1', 'reset_overrides' => '1',
                'rhythm' => 'weekly-1', 'end' => '31.12.2042', 'preview' => '1',
            ],
        ]);
        $this->assertResponseCode(302);
        foreach ($events as $index => $event) {
            $saved = $table->get($event->uid);
            $this->assertSame($index === 1 ? '15:00:00' : '14:00:00', $saved->uhrzeitstart->format('H:i:s'));
            $this->assertSame($index === 1 ? '2041-02-15' : $event->datumstart->format('Y-m-d'), $saved->datumstart->format('Y-m-d'));
            $this->assertSame($event->series_uid, $saved->series_uid);
        }
        $this->assertSame(1, $series->find()->count());
        $this->assertSame($originalSettings, $series->get($events[1]->series_uid)->get('settings'));
        $this->assertSame(3, $table->find()->where(['series_uid IS NOT' => null])->count());
    }

    public function testEditedOccurrenceSendsOnlySingleEventNotification(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        foreach ($events as $index => $event) {
            $table->updateAll(['datumstart' => Date::now()->addDays($index + 1)], ['uid' => $event->uid]);
        }
        $data = $this->seriesEventData();
        $data['datumstart'] = Date::now()->addDays(1)->format('d.m.Y');
        $data['uhrzeitstart'] = '15:00';
        $data['renotify'] = '1';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventEdit($events[0]->uid), [
            'referer' => '/', $data, 'series' => ['scope' => 'following'],
        ]);
        $this->assertResponseCode(302);
        $this->runAndAssertQueue();
        $this->assertMailCount(1);
        $this->assertMailSentToAt(0, 'worknews-test@mailinator.com');
        $this->assertMailContainsAt(0, 'hat folgende Veranstaltung geändert');
        $this->assertMailContainsAt(0, '15:00');
        $this->assertSame('14:00:00', $table->get($events[1]->uid)->uhrzeitstart->format('H:i:s'));
        $this->assertSame('14:00:00', $table->get($events[2]->uid)->uhrzeitstart->format('H:i:s'));
    }

    public function testUncheckedRecurrencePreservesManualMultiDateCreation(): void
    {
        $this->loginAsOrga();
        $settings = $this->seriesSettings();
        $settings['enabled'] = '0';
        $settings['end'] = '';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), ['datumstart' => '02.01.2041'], 'series' => $settings,
        ]);
        $this->assertResponseCode(302);
        $table = $this->getTableLocator()->get('Events');
        $this->assertSame(5, $table->find()->count());
        $this->assertSame(0, $table->find()->where(['series_uid IS NOT' => null])->count());
    }

    public function testAdditionalManualDatesAreKeptWhenRepeating(): void
    {
        $this->loginAsOrga();
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), ['datumstart' => '10.02.2041', 'uhrzeitstart' => '12:00'],
            'series' => $this->seriesSettings(),
        ]);
        $this->assertResponseCode(302);
        $table = $this->getTableLocator()->get('Events');
        $this->assertSame(3, $table->find()->where(['series_uid IS NOT' => null])->count());
        $manual = $table->find()->where(['datumstart' => '2041-02-10', 'series_uid IS' => null])->firstOrFail();
        $this->assertSame('12:00:00', $manual->uhrzeitstart->format('H:i:s'));
    }

    public function testSeriesEndIsRequired(): void
    {
        $this->loginAsOrga();
        $settings = $this->seriesSettings();
        $settings['end'] = '';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), 'series' => $settings,
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('gültiges Start- und Enddatum');
        $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
    }

    public function testInvalidSeriesPreservesAdditionalManualDates(): void
    {
        $this->loginAsOrga();
        $settings = $this->seriesSettings();
        $settings['end'] = '';
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
            'referer' => '/', $this->seriesEventData(), ['datumstart' => '10.02.2041', 'uhrzeitstart' => '12:00'], 'series' => $settings,
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('10.02.2041');
        $this->assertResponseContains('name="1[uhrzeitstart]"');
        $this->assertResponseRegExp('/<input(?=[^>]*type="checkbox")(?=[^>]*name="series\[enabled\]")(?=[^>]*disabled="disabled")[^>]*>/');
        $this->assertResponseContains('<span class="series-enabled-restriction"> (nur mit einem einzigen Ausgangstermin möglich)</span>');
        $this->assertResponseNotContains('event-date-mode-hint');
        $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
    }

    public function testSeriesDateCannotBeOmitted(): void
    {
        $this->loginAsOrga();
        $data = $this->seriesEventData();
        $data['datumstart'] = '';
        foreach ([[], ['preview' => '1']] as $preview) {
            $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
                'referer' => '/', $data, 'series' => $this->seriesSettings() + $preview,
            ]);
            $this->assertResponseOk();
            $formDocument = new \DOMDocument();
            $formDocument->loadHTML((string)$this->_response->getBody(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $formXpath = new \DOMXPath($formDocument);
            $this->assertSame(1.0, $formXpath->evaluate('count(//input[@id="0-datumstart"]/following-sibling::*[contains(@class,"error-message") and contains(.,"Bitte trage ein Datum ein.")])'));
            $this->assertSame(0.0, $formXpath->evaluate('count(//*[@id="event-series-preview"]//*[contains(@class,"error-message")])'));
            $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
        }
    }

    public function testSeriesWeekdaysErrorAppearsBelowCheckboxes(): void
    {
        $this->loginAsOrga();
        foreach ([[], ['preview' => '1']] as $preview) {
            $settings = array_replace($this->seriesSettings(), ['rhythm' => 'weekly-1', 'weekdays' => []], $preview);
            $this->post(Configure::read('AppConfig.htmlHelper')->urlEventNew(2), [
                'referer' => '/', $this->seriesEventData(), 'series' => $settings,
            ]);
            $this->assertResponseOk();
            $formDocument = new \DOMDocument();
            $formDocument->loadHTML((string)$this->_response->getBody(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $formXpath = new \DOMXPath($formDocument);
            $this->assertSame(1.0, $formXpath->evaluate('count(//*[@id="series-weekdays-error" and not(@hidden) and contains(.,"Bitte wähle mindestens einen gültigen Wochentag.")]/preceding-sibling::div[contains(@class,"series-weekday-options") and @aria-invalid="true"])'));
            $this->assertSame(0.0, $formXpath->evaluate('count(//*[@id="event-series-preview"]//*[contains(@class,"error-message")])'));
            $this->assertSame(3, $this->getTableLocator()->get('Events')->find()->count());
        }
    }

    public function testSeriesDeleteRequiresConfirmation(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $event = $table->find()->where(['series_uid IS NOT' => null])->firstOrFail();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventDelete($event->uid));
        $this->assertResponseOk();
        $this->assertResponseContains('Nur diesen Termin löschen');
        $this->assertResponseContains('Alle Termine dieser Serie löschen');
        $this->assertEquals(APP_ON, $table->get($event->uid)->status);
    }

    public function testDeleteOnlySelectedSeriesOccurrence(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventDelete($events[1]->uid), ['scope' => 'single']);
        $this->assertResponseCode(302);
        foreach ($events as $index => $event) {
            $this->assertEquals($index === 1 ? APP_DELETED : APP_ON, $table->get($event->uid)->status);
        }
    }

    public function testDeleteAllOccurrencesIncludesPastAndPreservesSeries(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $series = $this->getTableLocator()->get('EventSeries')->get($events[0]->series_uid)->toArray();
        $table->updateAll(['datumstart' => '2020-01-01'], ['uid' => $events[0]->uid]);
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventDelete($events[1]->uid), ['scope' => 'all']);
        $this->assertResponseCode(302);
        foreach ($events as $event) {
            $deletedEvent = $table->get($event->uid);
            $this->assertEquals(APP_DELETED, $deletedEvent->status);
            $this->assertSame($event->series_uid, $deletedEvent->series_uid);
        }
        $this->assertEquals($series, $this->getTableLocator()->get('EventSeries')->get($events[0]->series_uid)->toArray());
        $this->assertEquals(APP_ON, $table->get(6)->status);
    }

    public function testDeleteAllOccurrencesIncludesRescheduledEvents(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $table->updateAll(['datumstart' => '2041-03-16'], ['uid' => $events[0]->uid]);
        $table->updateAll(['datumstart' => '2041-02-01'], ['uid' => $events[2]->uid]);
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventDelete($events[1]->uid), ['scope' => 'all']);
        $this->assertResponseCode(302);
        foreach ($events as $event) {
            $this->assertEquals(APP_DELETED, $table->get($event->uid)->status);
        }
    }

    public function testSeriesDeleteFailureRollsBackEverything(): void
    {
        $this->createSeriesThroughForm();
        $table = $this->getTableLocator()->get('Events');
        $events = $table->find()->where(['series_uid IS NOT' => null])->orderBy(['datumstart' => 'ASC'])->all()->toList();
        $this->failSeriesSave = true;
        $this->post(Configure::read('AppConfig.htmlHelper')->urlEventDelete($events[0]->uid), ['scope' => 'all']);
        $this->assertResponseOk();
        foreach ($events as $event) {
            $this->assertEquals(APP_ON, $table->get($event->uid)->status);
        }
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
        $this->newEventData['datumstart'] = '01.02.2020';
        $this->newEventData['uhrzeitstart'] = '10:00:00';
        $this->newEventData['uhrzeitend'] = '20:00:00';

        $newEventData2 = [
            'datumstart' => '02.02.2020',
            'uhrzeitstart' => '12:00:00',
            'uhrzeitend' => '22:00:00',
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
        $expectedResult = (string)file_get_contents(TESTS . 'comparisons' . DS . 'events-for-map.json');
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
        $this->assertEquals($event->datumstart, new Date((string)$data['datumstart']));
        $this->assertEquals($event->uhrzeitstart, new Time((string)$data['uhrzeitstart']));
        $this->assertEquals($event->uhrzeitend, new Time((string)$data['uhrzeitend']));

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
        $this->assertResponseContains("UID:6");
        $this->assertResponseContains("UID:9");
        $this->assertResponseContains('SUMMARY:Test Workshop');
        $this->assertResponseContains('DESCRIPTION:description');
        $this->assertResponseContains('LOCATION:Müllerstraße 123 10115 Berlin Haus Drei');
    }

    public function testIcalAll(): void
    {
        $this->get(Configure::read('AppConfig.htmlHelper')->urlEventIcalAll());
        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'text/calendar');
        $this->assertHeaderContains('Content-Disposition', 'attachment; filename="events.ics"');
    }

    public function testMyEventsAsOrga(): void
    {
        // offline events must be shown
        $eventsTable = $this->getTableLocator()->get('Events');
        $event = $eventsTable->get(6);
        $event->status = APP_OFF;
        $eventsTable->save($event);

        $this->loginAsOrga();
        $this->get(Configure::read('AppConfig.htmlHelper')->urlMyEvents());
        $this->assertResponseOk();
        $this->assertResponseContains('Meine Termine');
        $this->assertResponseContains('Test Workshop (2 Termine, 8 Laufzettel, 1 Termin-Abonnement)');
        $this->assertResponseContains('Berlin, Müllerstraße 123');
        $this->assertResponseContains('01.01.2040');
        $this->assertResponseContains('09:00 - 18:00 Uhr');
        $this->assertResponseContains('<td class="eventUid">6</td>');
        $this->assertResponseContains('<td class="eventUid">9</td>');
        $this->assertResponseNotContains('event-series-indicator');
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
        $this->newEventData['uhrzeitstart'] = '10:00:00';
        $this->newEventData['uhrzeitend'] = '12:00:00';

        // Second event with same date/time but SAME location with trailing spaces (should fail - spaces will be trimmed)
        $newEventData2 = [
            'datumstart' => '01.01.2025',
            'uhrzeitstart' => '10:00:00',
            'uhrzeitend' => '12:00:00',
            'ort' => ' Berlin ',  // trailing space
            'strasse' => ' Demo Street 1 ',  // trailing space
            'zip' => ' 10999 ',  // trailing space
        ];

        // Third event with same date/time as fixture event 9 at same location (should fail against saved event)
        $fixtureDate = Date::now()->addDays(7)->format('d.m.Y');
        $newEventData3 = [
            'datumstart' => $fixtureDate,
            'uhrzeitstart' => '09:00:00',
            'uhrzeitend' => '18:00:00',
            'ort' => 'Berlin',
            'strasse' => 'Müllerstraße 123',
            'zip' => '10115',
        ];

        // Try to create events with same date/time/location
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            [
                'referer' => '/',
                $this->newEventData,
                $newEventData2,
                $newEventData3,
            ]
        );

        $foundCount = substr_count($this->_response->getBody()->__toString(), 'Du kannst keine Veranstaltungen zur gleichen Zeit, am gleichen Tag und am gleichen Ort anlegen.');
        $this->assertEquals(2, $foundCount);

        $foundCount = substr_count($this->_response->getBody()->__toString(), 'Es existiert bereits ein Termin für diese Initiative zur gleichen Zeit, am gleichen Tag und am gleichen Ort.');
        $this->assertEquals(1, $foundCount);
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
        $this->assertResponseNotContains('Es existiert bereits ein Termin für diese Initiative zur gleichen Zeit, am gleichen Tag und am gleichen Ort.');
        // Should redirect on success or show form without validation errors
        $this->assertResponseCode(302); // Redirect on success
    }

    public function testAddEventsWithSameDateTimeButDifferentLocation(): void
    {
        $this->loadNewEventData();
        $this->loginAsOrga();

        // First event at location A
        $eventData1 = [
            'workshop_uid' => 2,
            'ort' => 'Berlin',
            'strasse' => 'Demo Street 1',
            'zip' => '10999',
            'lat' => '52.520008',
            'lng' => '13.404954',
            'datumstart' => '15.03.2025',
            'uhrzeitstart' => '14:00:00',
            'uhrzeitend' => '16:00:00',
            'eventbeschreibung' => 'Event at location A',
            'use_custom_coordinates' => true,
            'province_id' => '1',
        ];

        // Second event at location B with same date/time
        $eventData2 = [
            'workshop_uid' => 2,
            'ort' => 'München',
            'strasse' => 'Marienplatz 1',
            'zip' => '80331',
            'lat' => '48.137154',
            'lng' => '11.576124',
            'datumstart' => '15.03.2025',
            'uhrzeitstart' => '14:00:00',
            'uhrzeitend' => '16:00:00',
            'eventbeschreibung' => 'Event at location B',
            'use_custom_coordinates' => true,
            'province_id' => '2',
        ];

        // Try to create two events with same date/time but DIFFERENT locations - should succeed
        $this->post(
            Configure::read('AppConfig.htmlHelper')->urlEventNew(2),
            [
                'referer' => '/',
                $eventData1,
                $eventData2,
            ]
        );

        // Should NOT show any duplicate validation errors
        $this->assertResponseNotContains('Du kannst keine Veranstaltungen zur gleichen Zeit, am gleichen Tag und am gleichen Ort anlegen.');
        $this->assertResponseNotContains('Es existiert bereits ein Termin für diese Initiative zur gleichen Zeit, am gleichen Tag und am gleichen Ort.');
        // Should redirect on success
        $this->assertResponseCode(302);
    }

}
?>