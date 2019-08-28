<?php

namespace App\View\Helper;

use Cake\View\Helper\TimeHelper;
use DateTime;

class MyTimeHelper extends TimeHelper {
    

    
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
