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
        }
        if (!$this->funding_was_registered_before_fundings_start_date) {
            $errors[] = 'Die Initiative wurde nach dem Start der Förderungen (' . $formattedFundingStartDate . ') registriert.';
        }
        if (!$this->funding_is_past_events_count_ok) {
            $errors[] = 'Keine Veranstaltungen vor dem  ' . $formattedFundingStartDate . ' vorhanden.';
        }
        if (!$this->funding_is_future_events_count_ok) {
            $errors[] = 'Weniger als 4 Veranstaltungen nach dem ' . $formattedFundingStartDate . ' vorhanden.';
        }
        if (!$this->funding_is_activity_proof_ok) {
            $errors[] = 'Kein geprüfter Aktivitätsnachweis vorhanden.';
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

        if (!isset($this->all_events)) {
            return false;
        }

        $pastEventsCount = 0;
        foreach ($this->all_events as $event) {
            if ($event->datumstart->i18nFormat(Configure::read('DateFormat.Database')) 
                <= Configure::read('AppConfig.fundingsStartDate')) {
                $pastEventsCount++;
            }
        }
        return $pastEventsCount > 0;
    }

    public function _getFundingIsFutureEventsCountOk(): bool {

        if (!isset($this->all_events)) {
            return false;
        }

        $futureEventsCount = 0;

        foreach ($this->all_events as $event) {
            if ($event->datumstart->i18nFormat(Configure::read('DateFormat.Database')) 
                >= Configure::read('AppConfig.fundingsStartDate')) {
                $futureEventsCount++;
            }
        }
        return $futureEventsCount > 3;
    }

    public function _getFundingIsActivityProofOk(): bool {
        // TODO implement
        return true;
    }

    public function _getFundingIsAllowed(): bool {
        
        if (!$this->funding_is_country_code_ok) {
            return false;
        }

        if ($this->funding_was_registered_before_fundings_start_date && $this->funding_is_past_events_count_ok) {
            return true;
        }

        return $this->funding_is_activity_proof_ok && $this->funding_is_future_events_count_ok; 
    }

}
