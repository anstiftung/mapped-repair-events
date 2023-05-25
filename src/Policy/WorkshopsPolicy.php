<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Cake\Datasource\FactoryLocator;
use Authorization\Policy\RequestPolicyInterface;

class WorkshopsPolicy implements RequestPolicyInterface
{

    protected $Workshop;

    public function canAccess($identity, ServerRequest $request)
    {

        if ($request->getParam('action') == 'verwalten') {

            if (is_null($identity)) {
                return false;
            }

            if (!($identity->isAdmin() || $identity->isOrga())) {
                return false;
            }

            return true;
            
        }

        // die action "edit" ist für alle eingeloggten user erlaubt, die orga-mitglieder der initiative sind
        if ($request->getParam('action') == 'add') {

            if (is_null($identity)) {
                return false;
            }

            if ($identity->isAdmin()) {
                return true;
            }

            if ($identity->isOrga()) {
                return true;
            }

            return false;

        }

        if ($request->getParam('action') == 'edit') {

            if (is_null($identity)) {
                return false;
            }

            if (!($identity->isOrga() || $identity->isAdmin())) {
                return false;
            }

            if ($identity->isAdmin()) {
                return true;
            }

            $workshopUid = (int) $request->getParam('pass')[0];

            // all approved orgas are alloewed to edit and add workshops
            $this->Workshop = FactoryLocator::get('Table')->get('Workshops');

            $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($this->Workshop->isUserInOrgaTeam($identity, $workshop)) {
                return true;
            }

            return false;
        }

        return true;

    }

}