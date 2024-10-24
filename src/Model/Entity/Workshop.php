<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Core\Configure;

class Workshop extends Entity
{

    protected array $_hidden = [
        'is_funding_allowed',
    ];

    protected array $_virtual = ['is_funding_allowed'];

    public function _getIsFundingAllowed(): bool {

        $criteriumA = $this->country_code == 'DE';
        
        $pastEventsCount = 0;
        foreach ($this->all_events as $event) {
            if ($event->datumstart->i18nFormat(Configure::read('DateFormat.Database')) <= Configure::read('AppConfig.fundingsStartDate')) {
                $pastEventsCount++;
            }
        }
        $workshopWasRegisteredBeforeFundingsStartDate = $this->created->i18nFormat(Configure::read('DateFormat.Database')) 
            <= Configure::read('AppConfig.fundingsStartDate');
        $criteriumB = $workshopWasRegisteredBeforeFundingsStartDate && $pastEventsCount > 0;

        if ($criteriumA && $criteriumB) {
            return true;
        }

        $futureEventsCount = 0;
        foreach ($this->all_events as $event) {
            if ($event->datumstart->i18nFormat(Configure::read('DateFormat.Database')) >= Configure::read('AppConfig.fundingsStartDate')) {
                $futureEventsCount++;
            }
        }

        // TODO implement activity proof confirmed
        $activityProofConfirmed = true;
        $criteriumC = $activityProofConfirmed && $futureEventsCount > 3;

        return $criteriumA && $criteriumC; 
    }

}
