<?php
declare(strict_types=1);

namespace App\Services;

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Model\Table\EventSeriesTable;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\Utility\Text;
use InvalidArgumentException;

class EventSeriesService
{
    private const array FIELDS = [
        'datumstart', 'uhrzeitstart', 'uhrzeitend', 'eventbeschreibung', 'image', 'image_alt_text',
        'veranstaltungsort', 'strasse', 'zip', 'ort', 'land', 'lat', 'lng', 'province_id',
        'use_custom_coordinates', 'is_online_event', 'categories', 'status',
    ];

    public function __construct(
        private EventsTable $events,
        private EventSeriesTable $series,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $settings
    * @return array{events: list<Event>, series: EntityInterface}
     */
    public function prepare(Event $original, array $data, array $settings, string $timezone): array
    {
        if (!$original->isNew()) {
            throw new InvalidArgumentException('Eine bestehende Terminserie kann nicht geändert werden.');
        }
        $data = array_intersect_key($data, array_flip(self::FIELDS));
        foreach (['use_custom_coordinates', 'is_online_event'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (int)(bool)$data[$field];
            }
        }
        $data['workshop_uid'] = $original->workshop_uid;
        $rule = $this->rule($settings);
        $dates = (new EventRecurrenceService())->dates($rule, $timezone);
        $series = $this->series->newEntity([
            'uid' => Text::uuid(), 'workshop_uid' => $original->workshop_uid,
            'settings' => $rule, 'timezone' => $timezone,
        ]);
        $excluded = $this->excluded($settings, $dates);
        if (count($excluded) === count($dates)) {
            throw new InvalidArgumentException('Bitte wähle mindestens einen Termin aus.');
        }
        $result = [];
        foreach ($dates as $date) {
            $row = $data;
            $row['datumstart'] = new DateTime($date);
            if (in_array($date, $excluded, true)) {
                $row['status'] = APP_DELETED;
            }
            $event = $this->events->patchEntity(clone $original, $row);
            $event->series_uid = $series->get('uid');
            $result[] = $event;
        }
        return ['events' => $result, 'series' => $series];
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function rule(array $settings): array
    {
        $settings = (new EventRecurrenceService())->normalizeRhythm($settings);
        return array_intersect_key($settings, array_flip([
            'start', 'end', 'end_mode', 'frequency', 'interval', 'weekday', 'positions', 'weekdays', 'monthly_rules',
        ]));
    }

    /**
     * @param array<string, mixed> $settings
     * @param list<string> $dates
     * @return list<string>
     */
    private function excluded(array $settings, array $dates): array
    {
        $excluded = $settings['excluded'] ?? [];
        if (!is_array($excluded)) {
            throw new InvalidArgumentException('Die ausgeschlossenen Termine sind ungültig.');
        }
        return array_values(array_intersect($dates, $excluded));
    }

}