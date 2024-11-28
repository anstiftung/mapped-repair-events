<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Table\FundingbudgetplansTable;
use App\Model\Table\FundingdatasTable;

class Funding extends Entity
{

    const STATUS_PENDING = 10;
    const STATUS_VERIFIED_BY_ADMIN = 20;
    const STATUS_REJECTED_BY_ADMIN = 30;
    const STATUS_BUDGETPLAN_DATA_MISSING = 40;
    const STATUS_DATA_OK = 50;
    const STATUS_DESCRIPTION_MISSING = 60;
    const STATUS_CHECKBOXES_MISSING = 70;
    const STATUS_CHECKBOXES_OK = 80;

    const MAX_FUNDING_SUM = 3000;

    const STATUS_MAPPING_UPLOADS = [
        self::STATUS_PENDING => 'Bestätigung von Admin ausstehend',
        self::STATUS_VERIFIED_BY_ADMIN => 'von Admin bestätigt',
        self::STATUS_REJECTED_BY_ADMIN => 'von Admin beanstandet',
    ];

    const STATUS_MAPPING = [
        self::STATUS_BUDGETPLAN_DATA_MISSING => 'Du musst mindestens eine investive Maßnahme hinzufügen',
        self::STATUS_DATA_OK => 'Die eingegebenen Daten sind ok',
        self::STATUS_DESCRIPTION_MISSING => 'Die Beschreibung ist nicht vollständig',
        self::STATUS_CHECKBOXES_MISSING => 'Bitte bestätige alle Checkboxen',
        self::STATUS_CHECKBOXES_OK => 'Alle Checkboxen bestätigt',
    ];

    const FIELDS_WORKSHOP = [
        ['name' => 'name', 'options' => ['label' => 'Name der Initiative']],
        ['name' => 'street', 'options' => ['label' => 'Straße + Hausnummer']],
        ['name' => 'zip', 'options' => ['label' => 'PLZ']],
        ['name' => 'city', 'options' => ['label' => 'Stadt']],
        ['name' => 'adresszusatz', 'options' => ['label' => 'Adresszusatz']],
        ['name' => 'email', 'options' => ['label' => 'E-Mail']],
        ['name' => 'website', 'options' => ['label' => 'Website']],
    ];

    const FIELDS_OWNER_USER = [
        ['name' => 'firstname', 'options' => ['label' => 'Vorname']],
        ['name' => 'lastname', 'options' => ['label' => 'Nachname']],
        ['name' => 'email', 'options' => ['label' => 'E-Mail']],
        ['name' => 'street', 'options' => ['label' => 'Straße + Hausnummer', 'required' => true]],
        ['name' => 'zip', 'options' => ['label' => 'PLZ']],
        ['name' => 'city', 'options' => ['label' => 'Stadt', 'required' => true]],
        ['name' => 'phone', 'options' => ['label' => 'Telefon', 'required' => true]],
    ];

    const FIELDS_FUNDINGSUPPORTER_ORGANIZATION = [
        ['name' => 'name', 'options' => ['label' => 'Name']],
        ['name' => 'legal_form', 'options' => ['label' => 'Rechtsform']],
        ['name' => 'street', 'options' => ['label' => 'Straße + Hausnummer']],
        ['name' => 'zip', 'options' => ['label' => 'PLZ']],
        ['name' => 'city', 'options' => ['label' => 'Stadt']],
        ['name' => 'website', 'options' => ['label' => 'Website']],
    ];

    const FIELDS_FUNDINGSUPPORTER_USER = [
        ['name' => 'contact_firstname', 'options' => ['label' => 'Vorname']],
        ['name' => 'contact_lastname', 'options' => ['label' => 'Nachname']],
        ['name' => 'contact_function', 'options' => ['label' => 'Funktion']],
        ['name' => 'contact_phone', 'options' => ['label' => 'Telefon']],
        ['name' => 'contact_email', 'options' => ['label' => 'E-Mail']],
    ];

    const FIELDS_FUNDINGSUPPORTER_BANK = [
        ['name' => 'bank_account_owner', 'options' => ['label' => 'Kontoinhaber']],
        ['name' => 'bank_institute', 'options' => ['label' => 'Kreditinstitut']],
        ['name' => 'iban', 'options' => ['label' => 'IBAN']],
    ];

    const FIELDS_FUNDINGDATA_DESCRIPTION = [
        ['name' => 'description', 'options' => ['label' =>  FundingdatasTable::DESCRIPTION_ERROR_MESSAGE, 'type' => 'textarea', 'rows' => 15, 'maxlength' => FundingdatasTable::DESCRIPTION_MAX_LENGTH, 'minlength' => FundingdatasTable::DESCRIPTION_MIN_LENGTH, 'class' => 'no-verify']],
    ];

    const FIELDS_FUNDINGBUDGETPLAN = [
        ['name' => 'id', 'options' => ['type' => 'hidden']],
        ['name' => 'type', 'options' => ['type' => 'select', 'options' => Fundingbudgetplan::TYPE_MAP, 'empty' => 'Förderbereich wählen...', 'label' => false, 'class' => 'no-select2']],
        ['name' => 'description', 'options' => ['label' => false, 'placeholder' => 'Maßnahme/Gegenstand (' . FundingbudgetplansTable::DESCRIPTION_ERROR_MESSAGE . ')', 'class' => 'no-verify', 'maxlength' => FundingbudgetplansTable::DESCRIPTION_MAX_LENGTH, 'minlength' => FundingbudgetplansTable::DESCRIPTION_MIN_LENGTH]],
        ['name' => 'amount', 'options' => ['label' => false, 'placeholder' => 'Kosten in € ', 'type' => 'number', 'step' => '0.01']],
    ];

    const FIELDS_FUNDING_DATA_CHECKBOXES = [
        ['name' => 'checkbox_a', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Mit der zu bewilligenden Maßnahme wurde noch nicht begonnen und wird auch nicht vor Erhalt des Bewilligungsbescheides begonnen.']],
        ['name' => 'checkbox_b', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Die in diesem Antrag gemachten Angaben (einschließlich Anlagen) sind vollständig und richtig.']],
        ['name' => 'checkbox_c', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Mit der Einreichung bestätige ich die zugrundeliegende Förderrichtlinie zur Kenntnis genommen, verstanden und vollumfänglich akzeptiert zu haben.']],
        ['name' => 'checkbox_d', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Mit der Einreichung stimme ich der Speicherung und Verarbeitung personenbezogener Daten, die zur Förderabwicklung und -Auswertung zu.']],
    ];

    public static function getRenderedFields($fields, $entityString, $form, $disabled, $entity = null) {
        $renderedFields = '';
        $fieldsToBeFormattedWithToDigits = ['amount'];
        foreach($fields as $field) {
            if ($entity !== null && in_array($field['name'], $fieldsToBeFormattedWithToDigits)) {
                $value = $entity[$field['name']];
                if ($value !== null) {
                    $field['options']['value'] = number_format($value, 2, '.', '');
                }
            }
            $field['options']['disabled'] = $disabled;
            $preparedEntityString = 'Fundings.' . $entityString . '.' . $field['name'];
            $renderedFields .= $form->control($preparedEntityString, $field['options']);
        }
        return $renderedFields;
    }

    public function _getBudgetplanTotal() {
        $total = 0;
        foreach($this->fundingbudgetplans as $fundingbudgetplan) {
            if ($fundingbudgetplan->is_valid) {
                $total += $fundingbudgetplan->amount;
            }
        }
        return $total;
    }

    public function _getGroupedValidBudgetplans() {
        $result = [];
        foreach($this->fundingbudgetplans as $fundingbudgetplan) {
            if ($fundingbudgetplan->is_valid) {
                $result[$fundingbudgetplan->type][] = $fundingbudgetplan;
            }
        }
        return $result;
    }

    public function _getGroupedValidBudgetplansTotals() {
        $result = [];
        foreach($this->grouped_valid_budgetplans as $typeId => $fundingbudgetplans) {
            $total = 0;
            foreach($fundingbudgetplans as $fundingbudgetplan) {
                $total += $fundingbudgetplan->amount;
            }
            $result[$typeId] = $total;
        }
        return $result;
    }

    public function _getBudgetplanStatus() {
        foreach($this->fundingbudgetplans as $fundingbudgetplan) {
            if ($fundingbudgetplan->is_valid && $fundingbudgetplan->type == Fundingbudgetplan::TYPE_A) {
                return self::STATUS_DATA_OK;
            }
        }
        return self::STATUS_BUDGETPLAN_DATA_MISSING;
    }

    public function _getCheckboxesStatus() {
        $checkboxes = array_map(function($checkbox) {
            return $checkbox['name'];
        }, self::FIELDS_FUNDING_DATA_CHECKBOXES);

        foreach($checkboxes as $checkbox) {
            if (!$this->fundingdata->$checkbox) {
                return self::STATUS_CHECKBOXES_MISSING;
            }
        }
        return self::STATUS_CHECKBOXES_OK;

    }

    public function _getCheckboxesStatusCssClass() {
        if ($this->checkboxes_status == self::STATUS_CHECKBOXES_MISSING) {
            return 'is-pending';
        }
        if ($this->checkboxes_status == self::STATUS_CHECKBOXES_OK) {
            return 'is-verified';
        }
        return '';
    }

    public function _getCheckboxesStatusHumanReadable() {
        return self::STATUS_MAPPING[$this->checkboxes_status];
    }

    public function _getBudgetplanStatusCssClass() {
        if ($this->budgetplan_status == self::STATUS_BUDGETPLAN_DATA_MISSING) {
            return 'is-pending';
        }
        if ($this->budgetplan_status == self::STATUS_DATA_OK) {
            return 'is-verified';
        }
        return '';
    }

    public function _getBudgetplanStatusHumanReadable() {
        return self::STATUS_MAPPING[$this->budgetplan_status];
    }

    public function _getActivityProofStatusCssClass() {

        if (!empty($this->workshop) && !$this->workshop->funding_activity_proof_required) {
            return '';
        }

        if ($this->activity_proof_status == self::STATUS_PENDING) {
            return 'is-pending';
        }
        if ($this->activity_proof_status == self::STATUS_VERIFIED_BY_ADMIN) {
            return 'is-verified';
        }
        if ($this->activity_proof_status == self::STATUS_REJECTED_BY_ADMIN) {
            return 'is-rejected';
        }
        return '';
    }

    public function _getFreistellungsbescheidStatusCssClass() {

        if ($this->freistellungsbescheid_status == self::STATUS_PENDING) {
            return 'is-pending';
        }
        if ($this->freistellungsbescheid_status == self::STATUS_VERIFIED_BY_ADMIN) {
            return 'is-verified';
        }
        if ($this->freistellungsbescheid_status == self::STATUS_REJECTED_BY_ADMIN) {
            return 'is-rejected';
        }
        return '';
    }

    public function _getDescriptionStatus() {
        $length = mb_strlen($this->fundingdata->description);
        $isValid = isset($this->fundingdata->description)
            && $length >= FundingdatasTable::DESCRIPTION_MIN_LENGTH
            && $length <= FundingdatasTable::DESCRIPTION_MAX_LENGTH;
        if ($isValid) {
            return self::STATUS_DATA_OK;
        };
        return self::STATUS_DESCRIPTION_MISSING;
    }

    public function _getDescriptionStatusCssClass() {
        if ($this->description_status == self::STATUS_DATA_OK) {
            return 'is-verified';
        }
        return 'is-pending';
    }

    public function _getDescriptionStatusHumanReadable() {
        return self::STATUS_MAPPING[$this->description_status];
    }

    public function _getActivityProofStatusHumanReadable() {
        return self::STATUS_MAPPING_UPLOADS[$this->activity_proof_status];
    }

    public function _getFreistellungsbescheidStatusHumanReadable() {
        return self::STATUS_MAPPING_UPLOADS[$this->freistellungsbescheid_status];
    }

    public static function getFieldsCount() {
        return count(self::FIELDS_WORKSHOP)
              + count(self::FIELDS_OWNER_USER)
              + count(self::FIELDS_FUNDINGSUPPORTER_ORGANIZATION)
              + count(self::FIELDS_FUNDINGSUPPORTER_USER)
              + count(self::FIELDS_FUNDINGSUPPORTER_BANK)
              + count(self::FIELDS_FUNDINGDATA_DESCRIPTION)
              + 1 // fundingbudgetplan
              + 1 // checkboxes count as 1
              ;
    }

    public function _getUserFieldsVerifiedCount(): int {
        $count = 0;

        if ($this->verified_fields !== null) {
            $count = count($this->verified_fields);
        }

        if ($this->description_status == self::STATUS_DATA_OK) {
            $count++;
        }

        if ($this->budgetplan_status == self::STATUS_DATA_OK) {
            $count++;
        }

        if ($this->checkboxes_status == self::STATUS_CHECKBOXES_OK) {
            $count++;
        }

        return $count;
    }

    public function _getAdminFieldsVerifiedCount(): int {
        
        $count = 0;

        if ($this->workshop->funding_activity_proof_required && $this->activity_proof_status == self::STATUS_VERIFIED_BY_ADMIN) {
            $count++;
        }

        if ($this->freistellungsbescheid_status == self::STATUS_VERIFIED_BY_ADMIN) {
            $count++;
        }

        return $count;
    }

    public function _getUserFieldsCount(): int {
        return self::getFieldsCount();
    }

    public function _getAdminFieldsCount(): int {
        $count = 0;
        if ($this->workshop->funding_activity_proof_required) {
            $count++;
        };
        $count++; // freistellungsbescheid
        return $count;

    }

    public function _getAllFieldsCount(): int {
        return $this->user_fields_count + $this->admin_fields_count;
    }

    public function _getAllFieldsVerifiedCount(): int {
        return $this->user_fields_verified_count + $this->admin_fields_verified_count;
    }

    public function _getUserFieldsVerified(): int {
        return $this->user_fields_verified_count == $this->user_fields_count;
    }

    public function _getIsSubmittable(): bool {
        return $this->all_fields_verified_count == $this->all_fields_count;
    }

    public function _getIsSubmitted(): bool {
        return $this->submit_date !== null;
    }

    public function _getSubmitDateFormatted(): string {
        if ($this->submit_date !== null) {
            return $this->submit_date->format('d.m.Y H:i');
        }
        return '';
    }

    public function _getSubmitDateFormattedForFilename(): string {
        if ($this->submit_date !== null) {
            return $this->submit_date->format('Y-m-d_H-i');
        }
        return '';
    }

    public function _getActivityProofsCount(): int {
        return count($this->fundinguploads_activity_proofs);
    }

    public function _getFreistellungsbescheidsCount(): int {
        return count($this->fundinguploads_freistellungsbescheids);
    }

}