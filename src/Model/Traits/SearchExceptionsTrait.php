<?php

namespace App\Model\Traits;

trait SearchExceptionsTrait {

    private $searchExceptions = [
        'berlin',
        'münchen',
    ];

    public function getChangeableOrConditions($keyword, $changeableOrConditions) {

        if (!in_array(strtolower($keyword), $this->searchExceptions)) {
            return $changeableOrConditions;
        }

        // search only from the beginning of the string for the defined exceptions
        foreach($changeableOrConditions as $key => &$value) {
            $value = preg_replace('/%/', '', $value, 1);
        }

        return $changeableOrConditions;

    }

}