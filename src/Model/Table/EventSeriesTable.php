<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Event;
use App\Model\Entity\EventSeries;
use Cake\I18n\DateTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * @extends \Cake\ORM\Table<array<string, \Cake\ORM\Behavior>, \App\Model\Entity\EventSeries>
 */
class EventSeriesTable extends Table
{
    private const array EVENT_FIELDS = [
        'uhrzeitstart', 'uhrzeitend', 'eventbeschreibung', 'image', 'image_alt_text',
        'veranstaltungsort', 'strasse', 'zip', 'ort', 'land', 'lat', 'lng', 'province_id',
        'use_custom_coordinates', 'is_online_event', 'categories', 'status',
    ];

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('event_series');
        $this->setPrimaryKey('uid');
        $this->setEntityClass(EventSeries::class);
        $this->getSchema()->setColumnType('settings', 'json');
        $this->hasMany('Events', ['foreignKey' => 'series_uid']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->uuid('uid');
        $validator->requirePresence('settings', 'create')
            ->notEmptyArray('settings', 'Bitte lege eine gültige Wiederholung fest.')
            ->add('settings', 'validRecurrence', [
                'rule' => fn(mixed $value): bool|string => $this->validateRecurrence($value),
            ]);
        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->addCreate(
            fn(EventSeries $series): bool|string => $this->validateRecurrence($series->settings),
            'validRecurrence',
            ['errorField' => 'settings'],
        );
        $rules->addUpdate(
            static fn(EventSeries $series): bool => !$series->isDirty('settings'),
            'immutableRecurrence',
            ['errorField' => 'settings', 'message' => 'Eine bestehende Terminserie kann nicht geändert werden.'],
        );
        return $rules;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $settings
     * @return array{events: list<Event>, series: EventSeries}
     */
    public function prepare(Event $original, array $data, array $settings): array
    {
        if (!$original->isNew()) {
            throw new InvalidArgumentException('Eine bestehende Terminserie kann nicht geändert werden.');
        }
        $series = $this->newEntity([
            'uid' => Text::uuid(), 'settings' => $settings,
        ]);
        if ($series->hasErrors()) {
            $errors = Hash::flatten($series->getErrors());
            throw new InvalidArgumentException((string)reset($errors));
        }
        $dates = $series->dates;
        $excluded = $settings['excluded'] ?? [];
        if (!is_array($excluded)) {
            throw new InvalidArgumentException('Die ausgeschlossenen Termine sind ungültig.');
        }
        $excluded = array_values(array_intersect($dates, $excluded));
        if (count($excluded) === count($dates)) {
            throw new InvalidArgumentException('Bitte wähle mindestens einen Termin aus.');
        }
        $data = array_intersect_key($data, array_flip(self::EVENT_FIELDS));
        foreach (['use_custom_coordinates', 'is_online_event'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = (int)(bool)$data[$field];
            }
        }
        $data['workshop_uid'] = $original->workshop_uid;
        /** @var EventsTable $events */
        $events = $this->getAssociation('Events')->getTarget();
        $result = [];
        foreach ($dates as $date) {
            $row = $data;
            $row['datumstart'] = new DateTime($date);
            if (in_array($date, $excluded, true)) {
                $row['status'] = APP_DELETED;
            }
            $event = $events->patchEntity(clone $original, $row);
            $event->series_uid = $series->uid;
            $result[] = $event;
        }
        return ['events' => $result, 'series' => $series];
    }

    private function validateRecurrence(mixed $settings): bool|string
    {
        if (!is_array($settings) || $settings === []) {
            return 'Bitte lege eine gültige Wiederholung fest.';
        }
        try {
            return (new EventSeries(['settings' => $settings]))->dates !== [];
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        } catch (\TypeError|\ValueError) {
            return 'Bitte lege eine gültige Wiederholung fest.';
        }
    }
}