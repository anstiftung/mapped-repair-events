<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Workshop extends Entity
{

    const STATISTICS_DISABLED = 0;
    const STATISTICS_SHOW_ALL = 1;
    const STATISTICS_SHOW_ONLY_CHART = 2;

    /**
     * @return string[]
     */
    protected function _getFundingErrors(): array {

        $errors = [];

        if (!$this->funding_is_country_code_ok) {
            $errors[] = 'Die Förderung ist nur für Initiativen aus Deutschland möglich.';
            return $errors;
        }
        if (!$this->funding_is_future_events_count_ok) {
            $errors[] = 'Zum Erstellen eines Förderantrages müssen mindestens 4 Termine für das Jahr 2025 eingetragen sein. ' . $this->funding_all_future_events_count . ' ' . ($this->funding_all_future_events_count == 1 ? 'Termin' : 'Termine') . ' vorhanden.';
        }

        return $errors;
    }

    public function _getFundingIsCountryCodeOk(): bool {
        return $this->country_code == 'DE';
    }

    public function _getFundingAllPastEventsCount(): int {
        if (empty($this->funding_all_past_events)) {
            return 0;
        }
        return $this->funding_all_past_events[0]['count'];
    }
    public function _getFundingIsPastEventsCountOk(): bool {
        return $this->funding_all_past_events_count > 0;
    }

    public function _getFundingAllFutureEventsCount(): int {
        if (empty($this->funding_all_future_events)) {
            return 0;
        }
        return $this->funding_all_future_events[0]['count'];
    }

    public function _getFundingIsFutureEventsCountOk(): bool {
        return $this->funding_all_future_events_count >= 4;
    }

    public function _getFundingIsActivityProofOk(): bool {
        if (empty($this->workshop_funding)) {
            return false;
        }
        return $this->workshop_funding->activity_proof_ok == 1;
    }

    public function _getFundingIsAllowed(): bool {

        if (!$this->funding_is_country_code_ok) {
            return false;
        }

        return $this->funding_is_future_events_count_ok;
    }

    public function _getFundingActivityProofRequired(): bool {
        return !$this->funding_is_past_events_count_ok;
    }

    public function _getIsTextEmpty(): bool {
        return trim(strip_tags($this->text)) === '';
    }

}