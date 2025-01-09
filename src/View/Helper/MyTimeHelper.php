<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\TimeHelper;
use DateTime;

class MyTimeHelper extends TimeHelper {


    public function getAllYearsUntilThisYear($thisYear, $firstYear)
    {
        $years = [];
        while($thisYear >= $firstYear) {
            $years[$thisYear] = $thisYear;
            $thisYear--;
        }
        return $years;
    }

    public function getMonths()
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

    public function getLastDayOfGivenMonth($date)
    {
        return date('t', strtotime($date));
    }


    function validateDate($date, $format = 'd.m.Y')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

}
