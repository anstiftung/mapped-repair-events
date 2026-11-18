<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\EventSeries;
use Cake\I18n\DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EventSeriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DateTime::setTestNow(new DateTime('2027-01-01'));
    }

    protected function tearDown(): void
    {
        DateTime::setTestNow();
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $settings
     * @return list<string>
     */
    private function dates(array $settings = []): array
    {
        return EventSeries::fromSettings($settings + [
            'start' => '2027-01-01',
            'end' => '2027-03-31',
            'frequency' => 'monthly',
            'weekdays' => ['SA'],
            'monthly_rules' => [['position' => '2', 'weekday' => 'SA']],
        ])->dates;
    }

    public function testDescribesBiweeklyRecurrenceWithMultipleWeekdays(): void
    {
        self::assertSame('jede 2. Woche (Mittwoch und Donnerstag), bis 31.03.2027', EventSeries::fromSettings([
            'rhythm' => 'weekly-2', 'weekdays' => ['TH', 'WE'], 'start' => '2027-01-01', 'end' => '2027-03-31',
        ])->description);
    }

    public function testDescribesWeeklyRecurrenceWithOneWeekday(): void
    {
        self::assertSame('jede Woche (Samstag), bis 31.03.2027', EventSeries::fromSettings([
            'frequency' => 'weekly', 'weekdays' => ['SA'], 'start' => '2027-01-01', 'end' => '2027-03-31',
        ])->description);
    }

    public function testDescribesMonthlyRecurrence(): void
    {
        self::assertSame('jeden Monat (2. Samstag), bis 31.03.2027', EventSeries::fromSettings([
            'frequency' => 'monthly', 'monthly_rules' => [['position' => '2', 'weekday' => 'SA']],
            'start' => '2027-01-01', 'end' => '2027-03-31',
        ])->description);
        self::assertSame('jeden 3. Monat (letzter Montag), bis 31.03.2027', EventSeries::fromSettings([
            'frequency' => 'monthly', 'interval' => 3, 'monthly_rules' => [['position' => '-1', 'weekday' => 'MO']],
            'start' => '2027-01-01', 'end' => '2027-03-31',
        ])->description);
    }

    public function testDescriptionIsVirtualAndReflectsFields(): void
    {
        $series = EventSeries::fromSettings([
            'rhythm' => 'weekly-1', 'weekdays' => ['SA'], 'start' => '2027-01-01', 'end' => '2027-03-31',
        ]);
        self::assertContains('description', $series->getVirtual());
        self::assertSame('jede Woche (Samstag), bis 31.03.2027', $series->toArray()['description']);
        $series->repeat_interval = 2;
        self::assertSame('jede 2. Woche (Samstag), bis 31.03.2027', $series->description);
        self::assertSame('', (new EventSeries())->toArray()['description']);
    }

    public function testDatesAreVirtualAndReflectFields(): void
    {
        $series = EventSeries::fromSettings([
            'rhythm' => 'monthly-1', 'monthly_rules' => [['position' => '2', 'weekday' => 'SA']],
            'start' => '2027-01-01', 'end' => '2027-03-31',
        ]);
        self::assertContains('dates', $series->getVirtual());
        self::assertSame(['2027-01-09', '2027-02-13', '2027-03-13'], $series->dates);
        self::assertSame($series->dates, $series->toArray()['dates']);
        $series->repeat_interval = 2;
        self::assertSame(['2027-01-09', '2027-03-13'], $series->dates);
        self::assertSame([], (new EventSeries())->toArray()['dates']);
    }

    public function testSecondSaturday(): void
    {
        self::assertSame(['2027-01-09', '2027-02-13', '2027-03-13'], $this->dates());
    }

    public function testCombinedMonthlySelection(): void
    {
        self::assertSame(['2027-01-09', '2027-03-13'], $this->dates(['rhythm' => 'monthly-2']));
    }

    public function testCombinedWeeklySelection(): void
    {
        self::assertSame(['2027-01-02', '2027-01-16', '2027-01-30'], $this->dates([
            'rhythm' => 'weekly-2', 'end' => '2027-01-31',
        ]));
    }

    public function testInvalidCombinedSelectionIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['rhythm' => 'weekly-0']);
    }

    public function testLastIsNotFourth(): void
    {
        self::assertSame(['2027-01-23', '2027-02-27', '2027-03-27'], $this->dates([
            'monthly_rules' => [['position' => '4', 'weekday' => 'SA']],
        ]));
        self::assertSame(['2027-01-30', '2027-02-27', '2027-03-27'], $this->dates([
            'monthly_rules' => [['position' => '-1', 'weekday' => 'SA']],
        ]));
    }

    public function testWeeklyCountAcrossDaylightSavingTime(): void
    {
        self::assertSame(['2027-03-20', '2027-04-03', '2027-04-17'], $this->dates([
            'start' => '2027-03-20', 'frequency' => 'weekly', 'interval' => 2,
            'end' => '2027-04-17',
        ]));
    }

    public function testMultipleWeeklyDays(): void
    {
        self::assertSame(['2027-01-04', '2027-01-07', '2027-01-11', '2027-01-14'], $this->dates([
            'frequency' => 'weekly', 'weekdays' => ['MO', 'TH'], 'end' => '2027-01-14',
        ]));
    }

    public function testSingleMonthlyRule(): void
    {
        self::assertSame(['2027-01-07', '2027-02-04', '2027-03-04'], $this->dates([
            'monthly_rules' => [['position' => '1', 'weekday' => 'TH']],
        ]));
    }

    public function testEndDateIsRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['end' => '']);
    }

    public function testTwoYearBoundaryIsInclusive(): void
    {
        self::assertNotEmpty($this->dates(['end' => '2029-01-01']));
    }

    public function testEndBeyondTwoYearsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['end' => '2029-01-02']);
    }

    public function testDailyWeekdaysFitWithinTwoYears(): void
    {
        self::assertCount(732, $this->dates([
            'frequency' => 'weekly', 'weekdays' => ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'], 'end' => '2029-01-01',
        ]));
    }

    public function testTooManyMonthlyRulesAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bitte lege genau eine Monatsregel fest.');
        $this->dates(['monthly_rules' => array_fill(0, 2, ['position' => '1', 'weekday' => 'MO'])]);
    }

    public function testEmptyWeekdaySelectionIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['frequency' => 'weekly', 'weekdays' => []]);
    }

    public function testInvalidCalendarDateIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['start' => '2027-02-30']);
    }

    public function testMissingEndDateCannotBeReplacedByEndMode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['end' => '', 'end_mode' => 'forever']);
    }

    public function testNoOccurrencesIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dates(['end' => '2027-01-01']);
    }
}