<?php
declare(strict_types=1);

namespace App\Services;

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Model\Table\EventSeriesTable;
use Cake\Datasource\EntityInterface;
use Cake\I18n\Date;
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
     * @return array{events: list<Event>, series: EntityInterface|null, changes: array<int, array{fields: list<string>, original: array<string, mixed>}>}
     */
    public function prepare(Event $original, array $data, array $settings, string $timezone): array
    {
        $data = array_intersect_key($data, array_flip(self::FIELDS));
        foreach (['use_custom_coordinates', 'is_online_event'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (int)(bool)$data[$field];
            }
        }
        $data['workshop_uid'] = $original->workshop_uid;
        $scope = (string)($settings['scope'] ?? 'single');
        if (!in_array($scope, ['single', 'following'], true)) {
            throw new InvalidArgumentException('Der Änderungsumfang ist ungültig.');
        }
        $changes = [];
        if ($original->isNew()) {
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
            foreach ($dates as $position => $date) {
                $row = $data;
                $row['datumstart'] = new DateTime($date);
                if (in_array($date, $excluded, true)) {
                    $row['status'] = APP_DELETED;
                }
                $event = $this->events->patchEntity(clone $original, $row);
                $event->patch(['series_uid' => $series->get('uid'), 'series_date' => $date, 'series_position' => $position, 'series_overrides' => []]);
                $result[] = $event;
            }
            return ['events' => $result, 'series' => $series, 'changes' => []];
        }
        $series = $this->series->get((string)$original->series_uid);
        if ((int)$series->get('workshop_uid') !== (int)$original->workshop_uid) {
            throw new InvalidArgumentException('Die Serie gehört nicht zu dieser Initiative.');
        }
        if ($scope === 'single') {
            $data['uid'] = $original->uid;
            $event = $this->events->patchEntity(clone $original, $data);
            $fields = $this->changedFields($original, $event);
            $event->series_overrides = array_values(array_unique(array_merge($original->series_overrides ?? [], $fields)));
            $changes[(int)$event->uid] = ['fields' => $fields, 'original' => $original->toArray()];
            return ['events' => [$event], 'series' => null, 'changes' => $changes];
        }
        $today = Date::now()->format('Y-m-d');
        $conditions = [
            'Events.series_uid' => $original->series_uid,
            'Events.workshop_uid' => $original->workshop_uid,
        ];
        $conditions['Events.datumstart >='] = $today;
        $conditions['Events.series_position >='] = $original->series_position;
        /** @var list<Event> $targets */
        $targets = $this->events->find()
            ->where($conditions)->contain(['Categories'])->orderBy(['Events.series_position' => 'ASC'])->all()->toList();
        if ($targets === []) {
            throw new InvalidArgumentException('Es gibt keine Termine in diesem Änderungsumfang.');
        }
        $patched = $this->events->patchEntity(clone $original, $data, ['validate' => false]);
        $fields = $this->changedFields($original, $patched);
        $regenerate = !empty($settings['change_rule']);
        if (!$regenerate && in_array('datumstart', $fields, true)) {
            throw new InvalidArgumentException('Bitte ändere gemeinsame Termine über die Wiederholungsregel.');
        }
        $dates = [];
        $excluded = [];
        $newSeries = null;
        if ($regenerate) {
            $rule = $this->rule($settings);
            $cutoff = max($today, $original->series_date->format('Y-m-d'));
            $dates = array_values(array_filter(
                (new EventRecurrenceService())->dates($rule, (string)$series->get('timezone')),
                static fn(string $date): bool => $date >= $cutoff,
            ));
            if ($dates === []) {
                throw new InvalidArgumentException('Die neue Regel enthält keine zukünftigen Termine in diesem Änderungsumfang.');
            }
            $excluded = $this->excluded($settings, $dates);
            $newSeries = $this->series->newEntity([
                'uid' => Text::uuid(), 'parent_uid' => $original->series_uid,
                'workshop_uid' => $original->workshop_uid, 'settings' => $rule,
                'timezone' => $series->get('timezone'),
            ]);
        }
        $this->events->seriesValidationUids = array_map(static fn(Event $event): int => (int)$event->uid, $targets);
        $result = [];
        try {
            $length = $regenerate ? max(count($targets), count($dates)) : count($targets);
            for ($index = 0; $index < $length; $index++) {
                $target = $targets[$index] ?? null;
                $isNew = $target === null;
                /** @var Event $event */
                $event = $isNew ? $this->events->newEmptyEntity() : clone $target;
                $row = $isNew ? $data : [];
                /** @var list<string> $overrides */
                $overrides = $target === null ? [] : ($target->series_overrides ?? []);
                foreach ($fields as $field) {
                    if ($field !== 'datumstart' && (!in_array($field, $overrides, true) || !empty($settings['reset_overrides']))) {
                        $row[$field] = $data[$field] ?? null;
                        $overrides = array_values(array_diff($overrides, [$field]));
                    }
                }
                if ($regenerate && isset($dates[$index])) {
                    $date = $dates[$index];
                    $event->series_date = $date;
                    if ($isNew || !in_array('datumstart', $overrides, true) || !empty($settings['reset_overrides'])) {
                        $row['datumstart'] = new DateTime($date);
                        $overrides = array_values(array_diff($overrides, ['datumstart']));
                    }
                    if (in_array($date, $excluded, true)) {
                        $row['status'] = APP_DELETED;
                    }
                } elseif ($regenerate) {
                    $row['status'] = APP_DELETED;
                }
                if ($regenerate) {
                    $event->series_uid = $newSeries->get('uid');
                    $event->series_position = $index;
                }
                if ($target !== null && (int)$target->status === APP_DELETED) {
                    $row['status'] = APP_DELETED;
                }
                $row['workshop_uid'] = $original->workshop_uid;
                if (!$isNew) {
                    $existing = array_intersect_key($event->toArray(), array_flip(self::FIELDS));
                    $existing['uid'] = $event->uid;
                    $existing['categories'] = ['_ids' => array_map(
                        static fn(EntityInterface $category): int => (int)$category->get('id'),
                        $event->categories ?? [],
                    )];
                    $row = array_replace($existing, $row);
                }
                $event = $this->events->patchEntity($event, $row);
                $event->series_overrides = $overrides;
                if ($target !== null) {
                    $changed = $this->changedFields($target, $event);
                    $changes[(int)$target->uid] = ['fields' => $changed, 'original' => $target->toArray()];
                }
                $result[] = $event;
            }
        } finally {
            $this->events->seriesValidationUids = [];
        }
        $keys = [];
        foreach ($result as $event) {
            if ((int)$event->status === APP_DELETED || $event->hasErrors()) {
                continue;
            }
            $key = implode('|', [(string)$event->datumstart, (string)$event->uhrzeitstart, (string)$event->uhrzeitend, $event->ort, $event->zip, $event->strasse]);
            if (isset($keys[$key])) {
                $event->setError('datumstart', 'Die Änderungen würden doppelte Termine erzeugen.');
            }
            $keys[$key] = true;
        }
        return ['events' => $result, 'series' => $newSeries, 'changes' => $changes];
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

    /** @return list<string> */
    private function changedFields(Event $original, Event $patched): array
    {
        $fields = [];
        foreach (self::FIELDS as $field) {
            if ($field === 'categories') {
                $before = array_map(static fn(EntityInterface $category): int => (int)$category->get('id'), $original->categories ?? []);
                $after = array_map(static fn(EntityInterface $category): int => (int)$category->get('id'), $patched->categories ?? []);
                sort($before);
                sort($after);
                if ($before !== $after) {
                    $fields[] = $field;
                }
            } elseif ((string)$original->get($field) !== (string)$patched->get($field)) {
                $fields[] = $field;
            }
        }
        return $fields;
    }
}