<?php
declare(strict_types=1);

namespace App\Test\TestCase\Services;

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Services\EventSeriesService;
use App\Test\TestCase\AppTestCase;
use Cake\I18n\DateTime;

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

    public function testBulkEditPreservesIdsAndIndividualExceptions(): void
    {
        $created = $this->createSeries();
        /** @var EventsTable $events */
        $events = $this->getTableLocator()->get('Events');
        $service = new EventSeriesService($events, $this->getTableLocator()->get('EventSeries'));
        $exception = $events->get($created[1]->uid, contain: ['Categories']);
        $exceptionData = $this->data();
        $exceptionData['datumstart'] = $exception->datumstart;
        $exceptionData['uhrzeitstart'] = new DateTime('15:00');
        $single = $service->prepare($exception, $exceptionData, ['scope' => 'single'], 'Europe/Berlin');
        self::assertContains('uhrzeitstart', $single['events'][0]->series_overrides);
        $events->saveOrFail($single['events'][0]);
        $original = $events->get($created[0]->uid, contain: ['Categories']);
        $data = $this->data();
        $data['datumstart'] = $original->datumstart;
        $data['uhrzeitstart'] = new DateTime('16:00');
        $plan = $service->prepare($original, $data, ['scope' => 'following'], 'Europe/Berlin');
        self::assertCount(3, $plan['events']);
        foreach ($plan['events'] as $index => $event) {
            self::assertFalse($event->hasErrors(), json_encode($event->getErrors(), JSON_THROW_ON_ERROR));
            self::assertSame($created[$index]->uid, $event->uid);
            self::assertSame($index === 1 ? '15:00:00' : '16:00:00', $event->uhrzeitstart->format('H:i:s'));
        }
    }

    public function testRuleChangePreservesIdsAndExcludedSlots(): void
    {
        $created = $this->createSeries();
        /** @var EventsTable $events */
        $events = $this->getTableLocator()->get('Events');
        $events->updateAll(['status' => APP_DELETED], ['uid' => $created[1]->uid]);
        $original = $events->get($created[0]->uid, contain: ['Categories']);
        $settings = $this->settings();
        $settings['positions'] = ['-1'];
        $settings['scope'] = 'following';
        $settings['change_rule'] = true;
        $data = $this->data();
        $data['datumstart'] = $original->datumstart;
        $plan = (new EventSeriesService($events, $this->getTableLocator()->get('EventSeries')))
            ->prepare($original, $data, $settings, 'Europe/Berlin');
        self::assertNotSame($original->series_uid, $plan['series']->get('uid'));
        foreach ($plan['events'] as $index => $event) {
            self::assertFalse($event->hasErrors(), json_encode($event->getErrors(), JSON_THROW_ON_ERROR));
            self::assertSame($created[$index]->uid, $event->uid);
        }
        self::assertEquals(APP_DELETED, $plan['events'][1]->status);
        self::assertSame('2041-01-26', $plan['events'][0]->datumstart->format('Y-m-d'));
    }

    public function testShorteningAndExtendingDoesNotRestoreRemovedOccurrence(): void
    {
        $created = $this->createSeries();
        /** @var EventsTable $events */
        $events = $this->getTableLocator()->get('Events');
        $series = $this->getTableLocator()->get('EventSeries');
        $service = new EventSeriesService($events, $series);
        $data = $this->data();
        foreach (['2041-02-28', '2041-03-31'] as $end) {
            $original = $events->get($created[0]->uid, contain: ['Categories']);
            $data['datumstart'] = $original->datumstart;
            $settings = $this->settings();
            $settings['end'] = $end;
            $settings['scope'] = 'following';
            $settings['change_rule'] = true;
            $plan = $service->prepare($original, $data, $settings, 'Europe/Berlin');
            $series->saveOrFail($plan['series']);
            foreach ($plan['events'] as $event) {
                $events->saveOrFail($event);
            }
            self::assertCount(3, $plan['events']);
            self::assertEquals(APP_DELETED, $events->get($created[2]->uid)->status);
            self::assertSame($plan['series']->get('uid'), $events->get($created[2]->uid)->series_uid);
        }
    }
}