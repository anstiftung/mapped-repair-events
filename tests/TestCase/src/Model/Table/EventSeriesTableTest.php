<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Event;
use App\Model\Entity\EventSeries;
use App\Model\Table\EventsTable;
use App\Test\TestCase\AppTestCase;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\Utility\Text;
use Cake\Validation\Validation;
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
        $table = $this->getTableLocator()->get('EventSeries');
        $original = $events->newEntity(['workshop_uid' => 2], ['validate' => false]);
        $series = $table->prepare($original, $this->data(), $this->settings());
        self::assertNull($series->uid);
        foreach ($series->events as $event) {
            self::assertFalse($event->hasErrors(), json_encode($event->getErrors(), JSON_THROW_ON_ERROR));
        }
        $table->saveOrFail($series, ['associated' => ['Events.Categories']]);
        self::assertTrue(Validation::uuid($series->uid));
        foreach ($series->events as $event) {
            self::assertSame($series->uid, $event->series_uid);
        }
        return $series->events;
    }

    public function testCreatesSeparateLinkedEvents(): void
    {
        $events = $this->createSeries();
        self::assertCount(3, $events);
        self::assertCount(3, array_unique(array_map(static fn(Event $event): int => (int)$event->uid, $events)));
        self::assertCount(1, array_unique(array_map(static fn(Event $event): string => $event->series_uid, $events)));
        $series = $this->getTableLocator()->get('EventSeries')->get($events[0]->series_uid);
        self::assertSame(2, $series->monthly_position);
        self::assertSame('SA', $series->monthly_weekday);
        self::assertFalse($series->get('repeat_saturday'));
        self::assertSame(['2041-01-12', '2041-02-09', '2041-03-09'], $series->dates);
    }

    public function testRejectsEditingExistingSeries(): void
    {
        $created = $this->createSeries();
        $series = $this->getTableLocator()->get('EventSeries');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Eine bestehende Terminserie kann nicht geändert werden.');
        $series->prepare($created[0], $this->data(), $this->settings());
    }

    public function testSavePreservesExplicitUid(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $series = EventSeries::fromSettings($this->settings());
        $uid = Text::uuid();
        $series->uid = $uid;
        $table->saveOrFail($series);
        self::assertSame($uid, $series->uid);
    }

    public function testPersistsWeeklyFormSettingsAsColumns(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $prepared = $table->prepare(new Event(['workshop_uid' => 2]), $this->data(), $this->settings() + [
            'rhythm' => 'weekly-2', 'weekdays' => ['WE', 'TH'], 'preview' => '1', 'excluded' => ['2041-01-02'],
        ]);
        $table->saveOrFail($prepared, ['associated' => ['Events.Categories']]);
        $series = $table->get($prepared->uid);
        self::assertSame('weekly', $series->frequency);
        self::assertSame(2, $series->repeat_interval);
        self::assertSame('2041-01-01', $series->start_date?->format('Y-m-d'));
        self::assertSame('2041-03-31', $series->end_date?->format('Y-m-d'));
        self::assertNull($series->monthly_position);
        self::assertNull($series->monthly_weekday);
        self::assertTrue($series->get('repeat_wednesday'));
        self::assertTrue($series->get('repeat_thursday'));
        self::assertFalse($series->get('repeat_saturday'));
        self::assertSame('jede 2. Woche (Mittwoch und Donnerstag), bis 31.03.2041', $series->description);
        self::assertSame($prepared->dates, $series->dates);
    }

    public function testInvalidColumnsBecomeValidationErrors(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $series = $table->newEntity([
            'start_date' => '2041-01-01', 'end_date' => '2041-02-31', 'frequency' => 'daily', 'repeat_interval' => 53,
            'monthly_position' => 5, 'monthly_weekday' => 'XX', 'repeat_monday' => 'invalid',
        ]);
        self::assertEmpty($series->getError('start_date'));
        self::assertSame('2041-01-01', $series->start_date?->format('Y-m-d'));
        foreach (['end_date', 'frequency', 'repeat_interval', 'monthly_position', 'monthly_weekday', 'repeat_monday'] as $field) {
            self::assertNotEmpty($series->getError($field));
        }
        self::assertSame('', $series->toArray()['description']);
    }

    public function testRequiresRecurrenceColumns(): void
    {
        $series = $this->getTableLocator()->get('EventSeries')->newEntity([]);
        self::assertSame(['start_date', 'end_date', 'frequency', 'repeat_interval'], array_keys($series->getErrors()));
    }

    public function testRulesRejectInvalidSeriesWhenValidationIsSkipped(): void
    {
        $table = $this->getTableLocator()->get('EventSeries');
        $series = $table->newEntity([
            'uid' => Text::uuid(), 'start_date' => '2041-01-01', 'end_date' => '2041-03-31',
            'frequency' => 'weekly', 'repeat_interval' => 1,
        ], ['validate' => false]);
        self::assertFalse($table->checkRules($series));
        self::assertContains(EventSeries::ERROR_WEEKDAYS_REQUIRED, $series->getError('frequency'));
    }

    public function testRulesPreventChangingPersistedRecurrence(): void
    {
        $events = $this->createSeries();
        $table = $this->getTableLocator()->get('EventSeries');
        $series = $table->get($events[0]->series_uid);
        $series->end_date = new Date('2041-04-30');
        self::assertFalse($table->save($series));
        self::assertNotEmpty($series->getError('frequency'));
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
        $series = $table->prepare(new Event(['workshop_uid' => 2]), $data, $this->settings());
        self::assertSame('2041-01-12', $series->events[0]->datumstart->format('Y-m-d'));
        foreach ($series->events as $event) {
            self::assertTrue($event->isNew());
            self::assertNull($event->uid);
            self::assertNull($event->owner);
            self::assertSame(2, $event->workshop_uid);
            self::assertNull($event->series_uid);
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
        self::assertFalse($series->getSchema()->hasColumn('settings'));
    }
}