<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Funding extends Entity
{

    const UPLOAD_PATH = ROOT . DS . 'files_private' . DS . 'fundings' . DS;

    const STATUS_DATA_MISSING = 10;
    const STATUS_PENDING = 20;
    const STATUS_APPROVED = 30;
    const STATUS_MONEY_TRANSFERRED = 40;


    /*
    in Bearbeitung
    eingereicht
    beanstandet und wieder in bearbeitung
    eingereicht
    von admin bestätigt
    Fördersumme überwiesen
    tom möchte wissen, ob bereits beanstandet wurde
    */

    const STATUS_MAPPING = [
        self::STATUS_DATA_MISSING => 'Daten fehlen',
        self::STATUS_PENDING => 'Antrag erfolgreich eingereicht und in Bearbeitung',
        self::STATUS_APPROVED => 'Antrag bestätigt',
        self::STATUS_MONEY_TRANSFERRED => 'Fördersumme überwiesen'
    ];

}