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
        self::TYPE_B => 'Type B',
        self::TYPE_C => 'Type C',
    ];

}
