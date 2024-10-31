<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Core\Configure;

class Workshop extends Entity
{

    protected function _getFundingErrors(): array {
        
        $formattedFundingStartDate = date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate')));
        $errors = [];
        
        if (!$this->funding_is_country_code_ok) {
            $errors[] = 'Die Förderung ist nur für Initiativen aus Deutschland möglich.';
            return $errors;
        }

        if (!$this->funding_is_activity_proof_ok) {
            if (!($this->funding_was_registered_before_fundings_start_date && $this->funding_is_past_events_count_ok)) {
                if (!$this->funding_is_past_events_count_ok) {
                    $errors[] = 'Es muss zumindest eine Veranstaltung vor dem  ' . $formattedFundingStartDate . ' vorhanden sein.';
                }
            }
        }

        if ($this->funding_is_activity_proof_ok) {
            if (!$this->funding_is_future_events_count_ok) {
                if (!$this->funding_is_activity_proof_ok) {
                    $errors[] = 'Aktivitätsnachweis: nicht geprüft';
                } else {
                    $errors[] = 'Aktivitätsnachweis: geprüft';
                }
                $errors[] = ' und mindestens 4 Veranstaltungen nach dem ' . $formattedFundingStartDate . ' vorhanden.';
            }
        }

        return $errors;
    }

    public function _getFundingIsCountryCodeOk(): bool {
        return $this->country_code == 'DE';
    }

    public function _getFundingWasRegisteredBeforeFundingsStartDate(): bool {
        return $this->created->i18nFormat(Configure::read('DateFormat.Database')) 
            <= Configure::read('AppConfig.fundingsStartDate');
    }

    public function _getFundingIsPastEventsCountOk(): bool {
        if (empty($this->funding_all_past_events)) {
            return false;
        }
        return $this->funding_all_past_events[0]['count'] > 0;
    }

    public function _getFundingIsFutureEventsCountOk(): bool {
        if (empty($this->funding_all_future_events)) {
            return false;
        }
        return $this->funding_all_future_events[0]['count'] > 3;
    }

    public function _getFundingIsActivityProofOk(): bool {
        if (empty($this->funding)) {
            return false;
        }
        return $this->funding->activity_proof_ok == 1;
    }

    public function _getFundingIsAllowed(): bool {
        
        if (!$this->funding_is_country_code_ok) {
            return false;
        }

        if ($this->funding_was_registered_before_fundings_start_date && $this->funding_is_past_events_count_ok) {
            return true;
        }

        if ($this->funding_is_activity_proof_ok && $this->funding_is_future_events_count_ok) {
            return true;
        }

        return false;
    }

}
