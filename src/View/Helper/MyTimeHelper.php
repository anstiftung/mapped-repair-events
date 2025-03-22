<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\TimeHelper;
use Cake\Core\Configure;
use DateTime;

class MyTimeHelper extends TimeHelper {


    public function isFundingFinished(?int $fundingUid): bool
    {
        if (!is_null($fundingUid) && in_array($fundingUid, [97445])) {
            return false;
        }
        return strtotime(Configure::read('AppConfig.fundingsEndDateNTime')) < time();
    }

    public function getAllYearsUntilThisYear(int $thisYear, int $firstYear): array
    {
        $years = [];
        while($thisYear >= $firstYear) {
            $years[$thisYear] = $thisYear;
            $thisYear--;
        }
        return $years;
    }

    public function getMonths(): array
    {
        $months = [
            '01' => 'Januar',
            '02' => 'Februar',
            '03' => 'März',
            '04' => 'April',
            '05' => 'Mai',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'August',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Dezember',
        ];
        return $months;
    }

    public function getLastDayOfGivenMonth(string $date): string
    {
        return date('t', strtotime($date));
    }


    function validateDate(string $date, string $format = 'd.m.Y'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

}
