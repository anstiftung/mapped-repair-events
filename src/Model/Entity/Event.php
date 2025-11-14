<?php
declare(strict_types=1);
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Event extends Entity
{
    public const FALLBACK_RADIUS_KM = 50;
}
