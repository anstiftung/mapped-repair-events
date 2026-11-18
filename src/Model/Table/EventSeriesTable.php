<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Event;
use App\Model\Entity\EventSeries;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
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
        $this->hasMany('Events', ['foreignKey' => 'series_uid']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->uuid('uid');
        foreach (['start_date', 'end_date'] as $field) {
            $validator->requirePresence($field, 'create')->notEmptyDate($field)->date($field, ['ymd']);
        }
        $validator->requirePresence('frequency', 'create')->notEmptyString('frequency')
            ->inList('frequency', EventSeries::FREQUENCIES);
        $validator->requirePresence('repeat_interval', 'create')->notEmptyString('repeat_interval')
            ->integer('repeat_interval')->range('repeat_interval', [1, 52]);
        $validator->allowEmptyString('monthly_position')->integer('monthly_position')
            ->inList('monthly_position', EventSeries::MONTHLY_POSITIONS);
        $validator->allowEmptyString('monthly_weekday')->inList('monthly_weekday', array_keys(EventSeries::WEEKDAY_FIELDS));
        foreach (EventSeries::WEEKDAY_FIELDS as $field) {
            $validator->notEmptyString($field)->boolean($field);
        }
        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->addCreate(
            $this->validateRecurrence(...),
            'validRecurrence',
            ['errorField' => 'frequency'],
        );
        $rules->addUpdate(
            static fn(EventSeries $series): bool => array_intersect($series->getDirty(), [
                'start_date', 'end_date', 'frequency', 'repeat_interval', 'monthly_position', 'monthly_weekday',
                ...array_values(EventSeries::WEEKDAY_FIELDS),
            ]) === [],
            'immutableRecurrence',
            ['errorField' => 'frequency', 'message' => 'Eine bestehende Terminserie kann nicht geändert werden.'],
        );
        return $rules;
    }

    /** @param \Cake\Event\EventInterface<\App\Model\Table\EventSeriesTable> $event */
    public function beforeSave(EventInterface $event, EventSeries $series): void
    {
        if ($series->isNew() && $series->uid === null) {
            $series->uid = Text::uuid();
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $settings
     */
    public function prepare(Event $original, array $data, array $settings): EventSeries
    {
        if (!$original->isNew()) {
            throw new InvalidArgumentException('Eine bestehende Terminserie kann nicht geändert werden.');
        }
        $series = EventSeries::fromSettings($settings);
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
            $result[] = $events->patchEntity(clone $original, $row);
        }
        $series->events = $result;
        return $series;
    }

    /**
     * @param list<string> $uids
     * @return array<string, string>
     */
    public function getDescriptions(array $uids): array
    {
        if ($uids === []) {
            return [];
        }
        $descriptions = [];
        foreach ($this->find()->where(['uid IN' => $uids]) as $series) {
            $descriptions[$series->uid] = $series->description;
        }
        return $descriptions;
    }

    private function validateRecurrence(EventSeries $series): bool|string
    {
        try {
            return $series->calculateDates() !== [] ? true : 'Bitte lege eine gültige Wiederholung fest.';
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        }
    }
}