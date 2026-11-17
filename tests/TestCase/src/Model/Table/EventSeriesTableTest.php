<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Event;
use App\Model\Entity\EventSeries;
use App\Model\Table\EventsTable;
use App\Test\TestCase\AppTestCase;
use Cake\I18n\DateTime;
use InvalidArgumentException;

class EventSeriesTableTest extends AppTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        DateTime::setTestNow(new DateTime('2040-12-01'));
    }

    public function tearDown(): void
    {
        DateTime::setTestNow();
        parent::tearDown();
    }

    /** @return array<string, mixed> */
    private function settings(): array
    {
        return [
            'start' => '2041-01-01', 'end' => '2041-03-31', 'frequency' => 'monthly',
            'interval' => 1, 'monthly_rules' => [['position' => '2', 'weekday' => 'SA']],
        ];
    }

    /** @return array<string, mixed> */
    private function data(): array
    {
        return [
            'eventbeschreibung' => 'Repaircafe', 'datumstart' => new DateTime('2041-01-12'),
            'uhrzeitstart' => new DateTime('14:00'), 'uhrzeitend' => new DateTime('17:00'),
            'strasse' => 'Teststrasse 1', 'zip' => '10115', 'ort' => 'Berlin', 'land' => 'de',
            'use_custom_coordinates' => true, 'lat' => '52.520008', 'lng' => '13.404954',
            'status' => APP_ON, 'province_id' => 1, 'categories' => ['_ids' => [87]],
        ];
    }

    /** @return list<Event> */
    private function createSeries(): array
    {
        /** @var EventsTable $events */
        $events = $this->getTableLocator()->get('Events');
        $series = $this->getTableLocator()->get('EventSeries');
        $original = $events->newEntity(['workshop_uid' => 2], ['validate' => false]);
        $plan = $series->prepare($original, $this->data(), $this->settings());
        $series->saveOrFail($plan['series']);
        foreach ($plan['events'] as $event) {
            self::assertFalse($event->hasErrors(), json_encode($event->getErrors(), JSON_THROW_ON_ERROR));
            $events->saveOrFail($event);
        }
        return $plan['events'];
    }

    public function testCreatesSeparateLinkedEvents(): void
    {
        $events = $this->createSeries();
        self::assertCount(3, $events);
        self::assertCount(3, array_unique(array_map(static fn(Event $event): int => (int)$event->uid, $events)));
        self::assertCount(1, array_unique(array_map(static fn(Event $event): string => $event->series_uid, $events)));
    }

    public function testRejectsEditingExistingSeries(): void
    {
        $created = $this->createSeries();
        $series = $this->getTableLocator()->get('EventSeries');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Eine bestehende Terminserie kann nicht geändert werden.');
        $series->prepare($created[0], $this->data(), $this->settings());
    }

    public function testMarshalsSeriesEntityAndNormalizesSettings(): void
    {
        $series = $this->getTableLocator()->get('EventSeries')->newEntity([
            'settings' => $this->settings() + ['rhythm' => 'weekly-2', 'weekdays' => ['WE'], 'preview' => '1', 'excluded' => []],
        ]);
        self::assertInstanceOf(EventSeries::class, $series);
        self::assertFalse($series->hasErrors());
        self::assertSame('weekly', $series->settings['frequency']);
        self::assertSame(2, $series->settings['interval']);
        self::assertArrayNotHasKey('rhythm', $series->settings);
        self::assertArrayNotHasKey('preview', $series->settings);
        self::assertArrayNotHasKey('excluded', $series->settings);
    }

    public function testInvalidSettingsBecomeValidationErrors(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        foreach ([['end' => ''], ['rhythm' => 'weekly-0'], ['frequency' => 'daily'], ['monthly_rules' => []]] as $invalid) {
            $series = $table->newEntity([
                'settings' => array_replace($this->settings(), $invalid),
            ]);
            self::assertNotEmpty($series->getError('settings'));
            self::assertSame('', $series->toArray()['description']);
        }
    }

    public function testRequiresSettings(): void
    {
        $series = $this->getTableLocator()->get('EventSeries')->newEntity([]);
        self::assertNotEmpty($series->getError('settings'));
    }

    public function testRulesRejectInvalidSeriesWhenValidationIsSkipped(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $series = $table->newEntity([
            'settings' => array_replace($this->settings(), ['end' => '']),
        ], ['validate' => false]);
        self::assertFalse($table->checkRules($series));
        self::assertNotEmpty($series->getError('settings'));
    }

    public function testRulesPreventChangingPersistedRecurrence(): void
    {
        $events = $this->createSeries();
        $table = $this->getTableLocator()->get('EventSeries');
        $series = $table->get($events[0]->series_uid);
        $series->settings = array_replace($this->settings(), ['end' => '2041-04-30']);
        self::assertFalse($table->save($series));
        self::assertNotEmpty($series->getError('settings'));
    }

    public function testRejectsExcludingEveryOccurrence(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $original = new Event(['workshop_uid' => 2]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bitte wähle mindestens einen Termin aus.');
        $table->prepare($original, $this->data(), $this->settings() + ['excluded' => ['2041-01-12', '2041-02-09', '2041-03-09']]);
    }

    public function testRejectsInvalidExclusions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Die ausgeschlossenen Termine sind ungültig.');
        $this->getTableLocator()->get('EventSeries')->prepare(
            new Event(['workshop_uid' => 2]), $this->data(), $this->settings() + ['excluded' => 'invalid'],
        );
    }

    public function testCopiesOnlyAllowedEventFields(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $data = array_replace($this->data(), [
            'uid' => 999, 'series_uid' => 'forged', 'workshop_uid' => 1, 'owner' => 999,
            'datumstart' => new DateTime('2030-01-01'), 'is_online_event' => true,
        ]);
        $plan = $table->prepare(new Event(['workshop_uid' => 2]), $data, $this->settings());
        self::assertSame('2041-01-12', $plan['events'][0]->datumstart->format('Y-m-d'));
        foreach ($plan['events'] as $event) {
            self::assertTrue($event->isNew());
            self::assertNull($event->uid);
            self::assertNull($event->owner);
            self::assertSame(2, $event->workshop_uid);
            self::assertSame($plan['series']->uid, $event->series_uid);
            self::assertSame(1, $event->is_online_event);
            self::assertSame(1, $event->use_custom_coordinates);
        }
    }

    public function testCreationDoesNotNeedPositionOrEditingFields(): void
    {
        $this->createSeries();
        /** @var EventsTable $events */
        $events = $this->getTableLocator()->get('Events');
        $series = $this->getTableLocator()->get('EventSeries');
        self::assertFalse($events->getSchema()->hasColumn('series_date'));
        self::assertFalse($events->getSchema()->hasColumn('series_overrides'));
        self::assertFalse($events->getSchema()->hasColumn('series_position'));
        self::assertFalse($series->getSchema()->hasColumn('parent_uid'));
        self::assertFalse($series->getSchema()->hasColumn('workshop_uid'));
        self::assertFalse($series->getSchema()->hasColumn('timezone'));
    }
}