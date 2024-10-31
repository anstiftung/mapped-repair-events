<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;

class FundingsPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request): bool|ResultInterface
    {

        if (Configure::read('AppConfig.fundingsEnabled') === false) {
            return false;
        }

        if (is_null($identity)) {
            return false;
        }        

        if (!($identity->isAdmin() || $identity->isOrga())) {
            return false;
        }

        if (in_array($request->getParam('action'), ['edit', 'uploadActivityProof'])) {

            if ($identity->isAdmin()) {
                return true;
            }

            $workshopUid = (int) $request->getParam('workshopUid');

            // all approved orgas are allowed to edit fundings
            $workshopsTable = FactoryLocator::get('Table')->get('Workshops');
            $workshop = $workshopsTable->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($workshopsTable->isUserInOrgaTeam($identity, $workshop)) {
                return true;
            }

            return false;
        }

        return true;

    }

}