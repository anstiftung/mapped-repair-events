<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Cake\Datasource\FactoryLocator;
use Authorization\Policy\RequestPolicyInterface;

class EventsPolicy implements RequestPolicyInterface
{

    protected $Event;
    protected $Workshop;

    public function canAccess($identity, ServerRequest $request)
    {

        if ($request->getParam('action') == 'myEvents') {
            return $identity !== null;
        }

        if ($request->getParam('action') == 'add') {

            if ($identity->isAdmin()) {
                return true;
            }

            // repair helpers are not allowed to add events
            if (!$identity->isOrga()) {
                return false;
            }

            $workshopUid = (int) $request->getParam('pass')[0];
            $this->Workshop = FactoryLocator::get('Table')->get('Workshops');
            $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($this->Workshop->isUserInOrgaTeam($identity, $workshop)) {
                return true;
            }

        }

        if (in_array($request->getParam('action'), ['edit', 'delete', 'duplicate'])) {

            // repair helpers are not allowed to edit, delete or duplicate events (even not own content - which does not exist because "add" is locked for repairhelpers too)
            if (!($identity->isOrga() || $identity->isAdmin())) {
                return false;
            }

            $eventUid = (int) $request->getParam('pass')[0];

            $this->Event = FactoryLocator::get('Table')->get('Events');
            $event = $this->Event->find('all', [
                'conditions' => [
                    'Events.uid' => $eventUid,
                    'Events.status > ' . APP_DELETED
                ]
            ])->first();
            $workshopUid = $event->workshop_uid;

            if ($request->getParam('action') == 'edit' && $event->datumstart->isPast()) {
                return false;
            }

            if ($identity->isAdmin()) {
                return true;
            }

            // all approved orgas are allowed to edit their events
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