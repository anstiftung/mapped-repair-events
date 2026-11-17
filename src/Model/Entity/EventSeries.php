<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\View\Helper\MyTimeHelper;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use RRule\RRule;

/**
 * @property string $uid
 * @property array<string, mixed> $settings
 * @property-read string $description
 * @property-read list<string> $dates
 */
class EventSeries extends Entity
{
    public const string ERROR_WEEKDAYS_REQUIRED = 'Bitte wähle mindestens einen Wochentag.';

    protected array $_virtual = ['description', 'dates'];

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    protected function _setSettings(array $settings): array
    {
        return self::normalizeSettings($settings);
    }

    protected function _getDescription(): string
    {
        if ($this->hasErrors() || empty($this->get('settings'))) {
            return '';
        }
        $settings = self::normalizeSettings($this->get('settings'));
        $weekdays = MyTimeHelper::getWeekdays();
        $interval = (int)($settings['interval'] ?? 1);
        if (($settings['frequency'] ?? 'monthly') === 'weekly') {
            $rhythm = $interval === 1 ? 'jede Woche' : 'jede ' . $interval . '. Woche';
            $selectedWeekdays = $settings['weekdays'];
            $days = array_values(array_intersect_key($weekdays, array_flip($selectedWeekdays)));
            $lastDay = array_pop($days);
            $detail = $days === [] ? $lastDay : implode(', ', $days) . ' und ' . $lastDay;
        } else {
            $rhythm = $interval === 1 ? 'jeden Monat' : 'jeden ' . $interval . '. Monat';
            $rule = $settings['monthly_rules'][0];
            $position = (int)$rule['position'] === -1 ? 'letzter' : $rule['position'] . '.';
            $detail = $position . ' ' . $weekdays[$rule['weekday']];
        }
        $end = $this->date((string)$settings['end'], new DateTimeZone('UTC'));
        return $rhythm . ' (' . $detail . '), bis ' . $end->format('d.m.Y');
    }

    /** @return list<string> */
    protected function _getDates(): array
    {
        if ($this->hasErrors() || empty($this->get('settings'))) {
            return [];
        }
        $settings = self::normalizeSettings($this->get('settings'));
        $timezone = (string)Configure::read('App.defaultTimezone');
        $zone = new DateTimeZone($timezone);
        $start = $this->date((string)($settings['start'] ?? ''), $zone);
        $frequency = (string)($settings['frequency'] ?? 'monthly');
        $interval = filter_var($settings['interval'] ?? 1, FILTER_VALIDATE_INT);
        if (!in_array($frequency, ['monthly', 'weekly'], true) || $interval === false || $interval < 1 || $interval > 52) {
            throw new InvalidArgumentException('Bitte wähle eine gültige Wiederholung und ein Intervall zwischen 1 und 52.');
        }
        $weekdays = array_keys(MyTimeHelper::getWeekdays());
        $days = [];
        if ($frequency === 'monthly') {
            $rules = $settings['monthly_rules'] ?? null;
            if (!is_array($rules) || count($rules) !== 1) {
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
            $selectedWeekdays = $settings['weekdays'] ?? [];
            if (!is_array($selectedWeekdays) || $selectedWeekdays === []) {
                throw new InvalidArgumentException(self::ERROR_WEEKDAYS_REQUIRED);
            }
            foreach ($selectedWeekdays as $weekday) {
                if (!is_string($weekday) || !in_array($weekday, $weekdays, true)) {
                    throw new InvalidArgumentException(self::ERROR_WEEKDAYS_REQUIRED);
                }
            }
            $days = $selectedWeekdays;
        }
        $end = $this->date((string)($settings['end'] ?? ''), $zone);
        $maximumEnd = DateTime::now($timezone)->startOfDay()->addYears(2);
        if ($end < $start || $end > $maximumEnd) {
            throw new InvalidArgumentException('Das Enddatum muss am oder nach dem Beginn und höchstens zwei Jahre in der Zukunft liegen.');
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
    private static function normalizeSettings(array $settings): array
    {
        if (array_key_exists('rhythm', $settings)) {
            if (!is_string($settings['rhythm']) || !preg_match('/\A(monthly|weekly)-([1-9][0-9]?)\z/', $settings['rhythm'], $matches)) {
                throw new InvalidArgumentException('Bitte wähle eine gültige Wiederholung.');
            }
            $settings['frequency'] = $matches[1];
            $settings['interval'] = (int)$matches[2];
            unset($settings['rhythm']);
        }
        return array_intersect_key($settings, array_flip([
            'start', 'end', 'frequency', 'interval', 'weekdays', 'monthly_rules',
        ]));
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