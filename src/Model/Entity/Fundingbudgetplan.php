<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Fundingbudgetplan extends Entity
{

    const TYPE_A = 1;
    const TYPE_B = 2;
    const TYPE_C = 3;

    const TYPE_MAP = [
        self::TYPE_A => 'Investive Maßnahme',
        self::TYPE_B => 'Raum/Veranstaltung',
        self::TYPE_C => 'Fortbildung',
    ];

    protected function _getIsValid() {
        return $this->type > 0 && $this->description != '' && $this->amount > 0;
    }

    protected function _getIsNotEmpty() {
        return $this->type > 0 || $this->description != '' || $this->amount > 0;
    }

}
