<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Funding extends Entity
{

    const UPLOAD_PATH = ROOT . DS . 'files_private' . DS . 'fundings' . DS;

    const FIELDS_WORKSHOP = [
        ['name' => 'name', 'options' => ['label' => 'Name der Initiative']],
        ['name' => 'street', 'options' => ['label' => 'Straße + Hausnummer']],
        ['name' => 'zip', 'options' => ['label' => 'PLZ']],
        ['name' => 'city', 'options' => ['label' => 'Stadt']],
        ['name' => 'adresszusatz', 'options' => ['label' => 'Adresszusatz']],
        ['name' => 'email', 'options' => ['label' => 'E-Mail']],
    ];

    const FIELDS_OWNER_USER = [
        ['name' => 'firstname', 'options' => ['label' => 'Vorname']],
        ['name' => 'lastname', 'options' => ['label' => 'Nachname']],
        ['name' => 'email', 'options' => ['label' => 'E-Mail']],
        ['name' => 'street', 'options' => ['label' => 'Straße + Hausnummer']],
        ['name' => 'zip', 'options' => ['label' => 'PLZ']],
        ['name' => 'city', 'options' => ['label' => 'Stadt']],
        ['name' => 'phone', 'options' => ['label' => 'Telefon']],
    ];

    const FIELDS_SUPPORTER_ORGANIZATION = [
        ['name' => 'name', 'options' => ['label' => 'Name']],
        ['name' => 'legal_form', 'options' => ['label' => 'Rechtsform']],
        ['name' => 'street', 'options' => ['label' => 'Straße + Hausnummer']],
        ['name' => 'zip', 'options' => ['label' => 'PLZ']],
        ['name' => 'city', 'options' => ['label' => 'Stadt']],
        ['name' => 'website', 'options' => ['label' => 'Website']],
    ];

    const FIELDS_SUPPORTER_USER = [
        ['name' => 'contact_firstname', 'options' => ['label' => 'Vorname']],
        ['name' => 'contact_lastname', 'options' => ['label' => 'Nachname']],
        ['name' => 'contact_function', 'options' => ['label' => 'Funktion']],
        ['name' => 'contact_phone', 'options' => ['label' => 'Telefon']],
        ['name' => 'contact_email', 'options' => ['label' => 'E-Mail']],
    ];

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


    public static function getRenderedFields($fields, $entity, $form) {
        $renderedFields = '';
        foreach($fields as $field) {
            $renderedFields .= $form->control('Fundings.' . $entity . '.' . $field['name'], $field['options']);
        }
        return $renderedFields;
}

    public static function getFieldsCount() {
        return count(self::FIELDS_WORKSHOP);
    }

}