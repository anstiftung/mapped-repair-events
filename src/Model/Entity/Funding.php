<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

class Funding extends Entity
{

    const UPLOAD_PATH = ROOT . DS . 'files_private' . DS . 'fundings' . DS;

    const STATUS_PENDING = 10;
    const STATUS_VERIFIED = 20;
    const STATUS_REJECTED = 30;

    const STATUS_MAPPING = [
        self::STATUS_PENDING => 'Bestätigung von Admin ausstehend',
        self::STATUS_VERIFIED => 'von Admin bestätigt',
        self::STATUS_REJECTED => 'von Admin beanstandet',
    ];

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

    const FIELDS_SUPPORTER_BANK = [
        ['name' => 'bank_account_owner', 'options' => ['label' => 'Kontoinhaber']],
        ['name' => 'bank_institute', 'options' => ['label' => 'Kreditinstitut']],
        ['name' => 'iban', 'options' => ['label' => 'IBAN']],
    ];

    public static function getRenderedFields($fields, $entity, $form) {
        $renderedFields = '';
        foreach($fields as $field) {
            $renderedFields .= $form->control('Fundings.' . $entity . '.' . $field['name'], $field['options']);
        }
        return $renderedFields;
    }

    public static function getFieldsCount() {
        return count(self::FIELDS_WORKSHOP) + count(self::FIELDS_OWNER_USER) + count(self::FIELDS_SUPPORTER_ORGANIZATION) + count(self::FIELDS_SUPPORTER_USER) + count(self::FIELDS_SUPPORTER_BANK);
    }

    public function _getActivityProofStatusIsVerified() {
        return $this->activity_proof_status == self::STATUS_VERIFIED;
    }

    public function _getActivityProofStatusCssClass() {
        if ($this->activity_proof_status == self::STATUS_PENDING) {
            return 'is-pending';
        }
        if ($this->activity_proof_status == self::STATUS_VERIFIED) {
            return 'is-verified';
        }
        if ($this->activity_proof_status == self::STATUS_REJECTED) {
            return 'is-rejected';
        }
        return '';
    }

    public function _getActivityProofStatusHumanReadable() {
        return self::STATUS_MAPPING[$this->activity_proof_status];
    }

    public function _getAdminRowStatusClasses(): array {
        if ($this->all_fields_verified) {
            return ['is-verified'];
        }
        return [];
    }

    public function _getVerifiedFieldsCount(): int {
        return $this->verified_fields !== null ? count($this->verified_fields) : 0;
    }

    public function _getAllFieldsVerified(): bool {
        return $this->verified_fields_count == self::getFieldsCount();
    }

    public function _getFundinguploadsActivityProofs(): array {
        if ($this->fundinguploads === null) {
            return [];
        }
        return array_filter($this->fundinguploads, function($upload) {
            return $upload->type == Fundingupload::TYPE_ACTIVITY_PROOF;
        });
    }

    public function _getActivityProofsCount(): int {
        return count($this->fundinguploads_activity_proofs);
    }

}