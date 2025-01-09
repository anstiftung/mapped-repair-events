<?php
declare(strict_types=1);
namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Model\Table\FundingbudgetplansTable;
use App\Model\Table\FundingdatasTable;
use Cake\Log\Log;

class Funding extends Entity
{

    const ADMIN_FILTER_OPTIONS = [
        'to-be-verified-by-admins' => 'Uploads: ' . self::STATUS_MAPPING_UPLOADS[self::STATUS_PENDING],
        'rejected-by-admins' => 'Uploads: ' . self::STATUS_MAPPING_UPLOADS[self::STATUS_REJECTED_BY_ADMIN],
        'no-data-verified' => 'Noch keine Felder bestätigt',
        'not-submitted' => 'Förderantrag nicht eingereicht',
        'submitted' => 'Förderantrag eingereicht',
    ];

    const ADMIN_FILTER_CONDITIONS = [
        'to-be-verified-by-admins' => 'Fundings.activity_proof_status = ' . self::STATUS_PENDING . ' OR Fundings.freistellungsbescheid_status = ' . self::STATUS_PENDING . ' OR (Fundings.submit_date IS NOT NULL AND Fundings.zuwendungsbestaetigung_status = ' . self::STATUS_PENDING . ')',  
        'rejected-by-admins' => 'Fundings.activity_proof_status = ' . self::STATUS_REJECTED_BY_ADMIN . ' OR Fundings.freistellungsbescheid_status = ' . self::STATUS_REJECTED_BY_ADMIN . ' OR (Fundings.submit_date IS NOT NULL AND Fundings.zuwendungsbestaetigung_status = ' . self::STATUS_REJECTED_BY_ADMIN . ')',
        'no-data-verified' => 'Fundings.verified_fields IS NULL',
        'not-submitted' => 'Fundings.submit_date IS NULL',
        'submitted' => 'Fundings.submit_date IS NOT NULL',
    ];

    const STATUS_PENDING = 10;
    const STATUS_VERIFIED_BY_ADMIN = 20;
    const STATUS_REJECTED_BY_ADMIN = 30;
    const STATUS_BUDGETPLAN_DATA_MISSING = 40;
    const STATUS_DATA_OK = 50;
    const STATUS_DESCRIPTION_MISSING = 60;
    const STATUS_CHECKBOXES_MISSING = 70;
    const STATUS_CHECKBOXES_OK = 80;
    const STATUS_UPLOAD_MISSING = 90;

    const MAX_FUNDING_SUM = 3000;

    const STATUS_MAPPING_UPLOADS = [
        self::STATUS_UPLOAD_MISSING => 'wurde noch nicht hochgeladen',
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
        ['name' => 'bic', 'options' => ['label' => 'BIC']],
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
        ['name' => 'checkbox_a', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Mit der zu bewilligende Maßnahme wurde noch nicht begonnen und wird auch nicht vor Erhalt des Bewilligungsbescheides begonnen.', 'escape' => false]],
        ['name' => 'checkbox_b', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Die zugrundeliegende <a href="/seite/richtlinie" target="_blank">Förderrichtlinie</a> habe ich/haben wir zur Kenntnis genommen.', 'escape' => false]],
        ['name' => 'checkbox_c', 'options' => ['type' => 'checkbox', 'class' => 'no-verify', 'label' => 'Mit der Einreichung erkläre ich mein Einverständnis, dass vorstehende Daten erhoben und elektronisch gespeichert werden, sowie das Einverständnis betroffener Dritter dazu eingeholt zu haben.<br /><i>Die Erhebung, Verarbeitung und Nutzung vorstehender personenbezogener Daten sind nur zulässig, wenn der Betroffene (Antragsteller) eingewilligt hat. Für den Fall, dass hierzu die Einwilligung verweigert wird, kann der Antrag nicht bearbeitet und die beantragte Förderung damit nicht bewilligt werden.</i>', 'escape' => false]],
    ];

    const FIELDS_WORKSHOP_LABEL = 'Stammdaten der Reparatur-Initiative';
    const FIELDS_OWNER_USER_LABEL = 'Personenbezogene Daten Ansprechpartner*in';
    const FIELDS_FUNDINGSUPPORTER_ORGANIZATION_LABEL = 'Stammdaten der Trägerorganisation';
    const FIELDS_FUNDINGSUPPORTER_USER_LABEL = 'Ansprechpartner*in der Trägerorganisation';
    const FIELDS_FUNDINGSUPPORTER_BANK_LABEL = 'Bankverbindung der Trägerorganisation';
    const FIELDS_FUNDINGDATA_DESCRIPTION_LABEL = 'Kurzbeschreibung Vorhaben';
    const FIELDS_FUNDINGBUDGETPLAN_LABEL = 'Kostenplan';
    const FIELDS_FUNDING_DATA_CHECKBOXES_LABEL = 'Einverständniserklärungen';
    
    public static function getRenderedFields($fields, $entityString, $form, $disabled, $entity = null) {
        $renderedFields = '';
        $fieldsToBeFormattedWithToDigits = ['amount'];
        foreach($fields as $field) {
            if ($entity !== null && in_array($field['name'], $fieldsToBeFormattedWithToDigits)) {
                $value = $entity[$field['name']];
                if ($value !== null) {
                    $field['options']['value'] = number_format((float) $value, 2, '.', '');
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

    public function _getBudgetplanTotalWithLimit() {
        $total = $this->budgetplan_total;
        if ($total > self::MAX_FUNDING_SUM) {
            return self::MAX_FUNDING_SUM;
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
        return $this->getAdminStatusCssClass('activity_proof_status');
    }

    public function _getFreistellungsbescheidStatusCssClass() {
        return $this->getAdminStatusCssClass('freistellungsbescheid_status');
    }

    public function _getZuwendungsbestaetigungStatusCssClass() {
        return $this->getAdminStatusCssClass('zuwendungsbestaetigung_status');
    }

    private function getAdminStatusCssClass($statusField) {
        if ($this->$statusField == self::STATUS_UPLOAD_MISSING) {
            return 'is-missing';
        }
        if ($this->$statusField == self::STATUS_PENDING) {
            return 'is-pending';
        }
        if ($this->$statusField == self::STATUS_VERIFIED_BY_ADMIN) {
            return 'is-verified';
        }
        if ($this->$statusField == self::STATUS_REJECTED_BY_ADMIN) {
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

    public function _getZuwendungsbestaetigungStatusHumanReadable() {
        return self::STATUS_MAPPING_UPLOADS[$this->zuwendungsbestaetigung_status];
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

    public function _getUserFieldsVerifiedCount(): int
    {
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

    public function _getUserFieldsVerified(): bool {
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

    public function _getActivityProofsCount(): int {
        return count($this->fundinguploads_activity_proofs);
    }

    public function _getFreistellungsbescheidsCount(): int {
        return count($this->fundinguploads_freistellungsbescheids);
    }

    public function _getZuwendungsbestaetigungsCount(): int {
        return count($this->fundinguploads_zuwendungsbestaetigungs);
    }

}