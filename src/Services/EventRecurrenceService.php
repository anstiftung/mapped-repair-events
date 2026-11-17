<?php
declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use Cake\I18n\DateTime;
use InvalidArgumentException;
use RRule\RRule;

class EventRecurrenceService
{
    public const int MAX_OCCURRENCES = 750;
    public const int MAX_MONTHLY_RULES = 1;

    /**
     * @param array<string, mixed> $settings
     * @return list<string>
     */
    public function dates(array $settings, string $timezone): array
    {
        $settings = $this->normalizeRhythm($settings);
        $zone = new DateTimeZone($timezone);
        $start = $this->date((string)($settings['start'] ?? ''), $zone);
        $frequency = (string)($settings['frequency'] ?? 'monthly');
        $interval = filter_var($settings['interval'] ?? 1, FILTER_VALIDATE_INT);
        if (!in_array($frequency, ['monthly', 'weekly'], true) || $interval === false || $interval < 1 || $interval > 52) {
            throw new InvalidArgumentException('Bitte wähle eine gültige Wiederholung und ein Intervall zwischen 1 und 52.');
        }
        $weekdays = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
        $selectedWeekdays = $settings['weekdays'] ?? [$settings['weekday'] ?? 'SA'];
        $days = [];
        if ($frequency === 'monthly') {
            $rules = $settings['monthly_rules'] ?? null;
            if ($rules === null) {
                $positions = $settings['positions'] ?? [];
                $rules = [];
                if (is_array($positions)) {
                    foreach ($positions as $position) {
                        $rules[] = ['position' => $position, 'weekday' => $settings['weekday'] ?? 'SA'];
                    }
                }
            }
            if (!is_array($rules) || $rules === [] || count($rules) > self::MAX_MONTHLY_RULES) {
                throw new InvalidArgumentException('Bitte lege genau eine Monatsregel fest.');
            }
            foreach ($rules as $rule) {
                if (!is_array($rule) || !in_array((string)($rule['position'] ?? ''), ['1', '2', '3', '4', '-1'], true)
                    || !in_array($rule['weekday'] ?? '', $weekdays, true)) {
                    throw new InvalidArgumentException('Bitte wähle für jede Monatsregel eine gültige Position und einen Wochentag.');
                }
                $days[] = (string)$rule['position'] . $rule['weekday'];
            }
        } else {
            if (!is_array($selectedWeekdays) || $selectedWeekdays === []) {
                throw new InvalidArgumentException('Bitte wähle mindestens einen gültigen Wochentag.');
            }
            foreach ($selectedWeekdays as $weekday) {
                if (!is_string($weekday) || !in_array($weekday, $weekdays, true)) {
                    throw new InvalidArgumentException('Bitte wähle mindestens einen gültigen Wochentag.');
                }
            }
            $days = $selectedWeekdays;
        }
        $end = $this->date((string)($settings['end'] ?? ''), $zone);
        $maximumEnd = DateTime::now($timezone)->startOfDay()->addYears(2);
        if ($end < $start || $end > $maximumEnd) {
            throw new InvalidArgumentException('Das Enddatum muss am oder nach dem Beginn und höchstens zwei Jahre in der Zukunft liegen.');
        }
        if (isset($settings['end_mode']) && $settings['end_mode'] !== 'date') {
            throw new InvalidArgumentException('Für eine Terminserie ist ein Enddatum erforderlich.');
        }
        $options = [
            'FREQ' => strtoupper($frequency),
            'DTSTART' => $start,
            'INTERVAL' => $interval,
            'BYDAY' => implode(',', array_unique($days)),
            'UNTIL' => $end->setTime(23, 59, 59),
        ];
        $dates = [];
        foreach (new RRule($options) as $occurrence) {
            if (count($dates) >= self::MAX_OCCURRENCES || $occurrence > $maximumEnd) {
                throw new InvalidArgumentException('Eine Serie darf höchstens 750 Termine innerhalb von zwei Jahren enthalten.');
            }
            $dates[] = $occurrence->format('Y-m-d');
        }
        if ($dates === []) {
            throw new InvalidArgumentException('Für diese Wiederholung gibt es im gewählten Zeitraum keine Termine.');
        }
        return array_values(array_unique($dates));
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function normalizeRhythm(array $settings): array
    {
        if (array_key_exists('rhythm', $settings)) {
            if (!is_string($settings['rhythm']) || !preg_match('/\A(monthly|weekly)-([1-9][0-9]?)\z/', $settings['rhythm'], $matches)) {
                throw new InvalidArgumentException('Bitte wähle eine gültige Wiederholung.');
            }
            $settings['frequency'] = $matches[1];
            $settings['interval'] = (int)$matches[2];
            unset($settings['rhythm']);
        }
        return $settings;
    }

    private function date(string $value, DateTimeZone $zone): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, $zone);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException('Bitte gib ein gültiges Start- und Enddatum ein.');
        }
        return $date;
    }
}