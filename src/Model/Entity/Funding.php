<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Funding extends Entity
{

    const STATUS_DATA_MISSING = 10;
    const STATUS_PENDING = 20;
    const STATUS_APPROVED = 30;
    const STATUS_MONEY_TRANSFERRED = 40;

    const STATUS_MAPPING = [
        self::STATUS_DATA_MISSING => 'Daten fehlen',
        self::STATUS_PENDING => 'Antrag erfolgreich eingereicht und in Bearbeitung',
        self::STATUS_APPROVED => 'Antrag bestätigt',
        self::STATUS_MONEY_TRANSFERRED => 'Fördersumme überwiesen'
    ];

}