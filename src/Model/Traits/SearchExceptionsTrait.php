<?php
declare(strict_types=1);

namespace App\Model\Traits;

trait SearchExceptionsTrait {

    private array $searchExceptions = [
        'berlin',
        'münchen',
    ];

    public function getChangeableOrConditions(string $keyword, array $changeableOrConditions): array
    {

        if (!in_array(strtolower($keyword), $this->searchExceptions)) {
            return $changeableOrConditions;
        }

        // search only from the beginning of the string for the defined exceptions
        foreach($changeableOrConditions as $key => &$value) {
            $value = preg_replace('/%/', '', (string) $value, 1);
        }

        return $changeableOrConditions;

    }

}