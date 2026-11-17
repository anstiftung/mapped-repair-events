<?php
declare(strict_types=1);

namespace App\Test\TestCase\Services;

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Services\EventSeriesService;
use App\Test\TestCase\AppTestCase;
use Cake\I18n\DateTime;
use InvalidArgumentException;

class EventSeriesServiceTest extends AppTestCase
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
            'interval' => 1, 'weekday' => 'SA', 'positions' => ['2'],
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
        $plan = (new EventSeriesService($events, $series))->prepare($original, $this->data(), $this->settings(), 'Europe/Berlin');
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
        /** @var EventsTable $events */
        $events = $this->getTableLocator()->get('Events');
        $service = new EventSeriesService($events, $this->getTableLocator()->get('EventSeries'));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Eine bestehende Terminserie kann nicht geändert werden.');
        $service->prepare($created[0], $this->data(), $this->settings(), 'Europe/Berlin');
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
    }
}