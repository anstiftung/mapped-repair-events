<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\View\Helper\MyTimeHelper;
use Cake\Core\Configure;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RRule\RRule;

/**
 * @property string|null $uid
 * @property list<\App\Model\Entity\Event> $events
 * @property \Cake\I18n\Date|null $start_date
 * @property \Cake\I18n\Date|null $end_date
 * @property string|null $frequency
 * @property int|null $repeat_interval
 * @property int|null $monthly_position
 * @property string|null $monthly_weekday
 * @property-read string $description
 * @property-read list<string> $dates
 */
class EventSeries extends Entity
{
    public const string ERROR_WEEKDAYS_REQUIRED = 'Bitte wähle mindestens einen Wochentag.';

    public const array FREQUENCIES = ['monthly', 'weekly'];

    public const array MONTHLY_POSITIONS = [1, 2, 3, 4, -1];

    public const array WEEKDAY_FIELDS = [
        'MO' => 'repeat_monday',
        'TU' => 'repeat_tuesday',
        'WE' => 'repeat_wednesday',
        'TH' => 'repeat_thursday',
        'FR' => 'repeat_friday',
        'SA' => 'repeat_saturday',
        'SU' => 'repeat_sunday',
    ];

    protected array $_virtual = ['description', 'dates'];

    /** @param array<string, mixed> $settings */
    public static function fromSettings(array $settings): self
    {
        if (array_key_exists('rhythm', $settings)) {
            if (!is_string($settings['rhythm']) || !preg_match('/\A(monthly|weekly)-([1-9][0-9]?)\z/', $settings['rhythm'], $matches)) {
                throw new InvalidArgumentException('Bitte wähle eine gültige Wiederholung.');
            }
            $settings['frequency'] = $matches[1];
            $settings['interval'] = $matches[2];
        }
        $frequency = $settings['frequency'] ?? 'monthly';
        $interval = filter_var($settings['interval'] ?? 1, FILTER_VALIDATE_INT);
        self::validateRhythm($frequency, $interval);

        $zone = new DateTimeZone('UTC');
        $fields = [
            'start_date' => new Date(self::parseDate($settings['start'] ?? null, $zone)->format('Y-m-d')),
            'end_date' => new Date(self::parseDate($settings['end'] ?? null, $zone)->format('Y-m-d')),
            'frequency' => $frequency,
            'repeat_interval' => $interval,
        ];
        if ($frequency === 'monthly') {
            $rules = $settings['monthly_rules'] ?? null;
            if (!is_array($rules) || count($rules) !== 1) {
                throw new InvalidArgumentException('Bitte lege genau eine Monatsregel fest.');
            }
            $rule = reset($rules);
            $position = is_array($rule) ? ($rule['position'] ?? null) : null;
            $weekday = is_array($rule) ? ($rule['weekday'] ?? null) : null;
            if (is_string($position) && in_array($position, ['1', '2', '3', '4', '-1'], true)) {
                $position = (int)$position;
            }
            self::validateMonthlyRule($position, $weekday);
            $fields['monthly_position'] = $position;
            $fields['monthly_weekday'] = $weekday;
        } else {
            $weekdays = $settings['weekdays'] ?? null;
            if (!is_array($weekdays) || $weekdays === []) {
                throw new InvalidArgumentException(self::ERROR_WEEKDAYS_REQUIRED);
            }
            foreach ($weekdays as $weekday) {
                if (!is_string($weekday) || !isset(self::WEEKDAY_FIELDS[$weekday])) {
                    throw new InvalidArgumentException(self::ERROR_WEEKDAYS_REQUIRED);
                }
                $fields[self::WEEKDAY_FIELDS[$weekday]] = true;
            }
        }

        return new self($fields);
    }

    protected function _getDescription(): string
    {
        if ($this->hasErrors() || $this->end_date === null) {
            return '';
        }
        $weekdays = MyTimeHelper::getWeekdays();
        $interval = $this->repeat_interval;
        if ($this->frequency === 'weekly') {
            $rhythm = $interval === 1 ? 'jede Woche' : 'jede ' . $interval . '. Woche';
            $days = array_values(array_intersect_key($weekdays, array_flip($this->getRepeatWeekdays())));
            $lastDay = array_pop($days);
            $detail = $days === [] ? $lastDay : implode(', ', $days) . ' und ' . $lastDay;
        } else {
            $rhythm = $interval === 1 ? 'jeden Monat' : 'jeden ' . $interval . '. Monat';
            $position = $this->monthly_position === -1 ? 'letzter' : $this->monthly_position . '.';
            $detail = $position . ' ' . ($weekdays[$this->monthly_weekday] ?? '');
        }
        return $rhythm . ' (' . $detail . '), bis ' . $this->end_date->format('d.m.Y');
    }

    /** @return list<string> */
    protected function _getDates(): array
    {
        return $this->calculateDates();
    }

    /**
     * @return list<string>
     * @throws \InvalidArgumentException
     */
    public function calculateDates(): array
    {
        if ($this->hasErrors() || $this->frequency === null) {
            return [];
        }
        $timezone = (string)Configure::read('App.defaultTimezone');
        $zone = new DateTimeZone($timezone);
        $start = self::parseDate($this->start_date?->format('Y-m-d'), $zone);
        $end = self::parseDate($this->end_date?->format('Y-m-d'), $zone);
        $frequency = $this->frequency;
        $interval = $this->repeat_interval;
        self::validateRhythm($frequency, $interval);
        $days = $this->getRepeatWeekdays();
        if ($frequency === 'monthly') {
            self::validateMonthlyRule($this->monthly_position, $this->monthly_weekday);
            if ($days !== []) {
                throw new InvalidArgumentException('Bitte lege eine gültige Wiederholung fest.');
            }
            $days = [$this->monthly_position . $this->monthly_weekday];
        } else {
            if ($days === []) {
                throw new InvalidArgumentException(self::ERROR_WEEKDAYS_REQUIRED);
            }
            if ($this->monthly_position !== null || $this->monthly_weekday !== null) {
                throw new InvalidArgumentException('Bitte lege eine gültige Wiederholung fest.');
            }
        }
        $maximumEnd = DateTime::now($timezone)->startOfDay()->addYears(2);
        if ($end < $start || $end > $maximumEnd) {
            throw new InvalidArgumentException('Das Enddatum muss am oder nach dem Beginn und höchstens zwei Jahre in der Zukunft liegen.');
        }
        $options = [
            'FREQ' => strtoupper($frequency),
            'DTSTART' => $start,
            'INTERVAL' => $interval,
            'BYDAY' => implode(',', $days),
            'UNTIL' => $end->setTime(23, 59, 59),
        ];
        $dates = [];
        foreach (new RRule($options) as $occurrence) {
            $dates[] = $occurrence->format('Y-m-d');
        }
        if ($dates === []) {
            throw new InvalidArgumentException('Für diese Wiederholung gibt es im gewählten Zeitraum keine Termine.');
        }
        return array_values(array_unique($dates));
    }

    /** @return list<string> */
    private function getRepeatWeekdays(): array
    {
        $days = [];
        foreach (self::WEEKDAY_FIELDS as $weekday => $field) {
            $enabled = $this->get($field);
            if ($enabled === true) {
                $days[] = $weekday;
            } elseif ($enabled !== false && $enabled !== null) {
                throw new InvalidArgumentException(self::ERROR_WEEKDAYS_REQUIRED);
            }
        }
        return $days;
    }

    private static function validateRhythm(mixed $frequency, mixed $interval): void
    {
        if (!in_array($frequency, self::FREQUENCIES, true) || !is_int($interval) || $interval < 1 || $interval > 52) {
            throw new InvalidArgumentException('Bitte wähle eine gültige Wiederholung und ein Intervall zwischen 1 und 52.');
        }
    }

    private static function validateMonthlyRule(mixed $position, mixed $weekday): void
    {
        if (!in_array($position, self::MONTHLY_POSITIONS, true) || !is_string($weekday) || !isset(self::WEEKDAY_FIELDS[$weekday])) {
            throw new InvalidArgumentException('Bitte wähle für jede Monatsregel eine gültige Position und einen Wochentag.');
        }
    }

    private static function parseDate(mixed $value, DateTimeZone $zone): DateTimeImmutable
    {
        if (!is_string($value) || !preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $value)) {
            throw new InvalidArgumentException('Bitte gib ein gültiges Start- und Enddatum ein.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $zone);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Bitte gib ein gültiges Start- und Enddatum ein.');
        }
        return $date;
    }
}