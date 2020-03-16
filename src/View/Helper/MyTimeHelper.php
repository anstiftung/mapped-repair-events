<?php

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
    
    /**
    * returns the difference of two dates in seconds
    * http://stackoverflow.com/questions/676824/how-to-calculate-the-difference-between-two-dates-using-php
    * läuft auch auf php4
    * @param $date1 eg. date from db
    * @param $date2 eg. date from db
    * @return int $seconds
    */
    public function datediff($date1, $date2) {
        $diff = abs(strtotime($date2) - strtotime($date1));
        return $diff;
    }
    
}
