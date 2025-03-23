<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\ORM\TableRegistry;
use Authorization\IdentityInterface;

class EventsPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($request->getParam('action') == 'myEvents') {
            return $identity !== null;
        }

        if ($request->getParam('action') == 'add') {

            if (is_null($identity)) {
                return false;
            }

            if ($identity->isAdmin()) {
                return true;
            }

            // repair helpers are not allowed to add events
            if (!$identity->isOrga()) {
                return false;
            }

            $workshopUid = (int) $request->getParam('pass')[0];
            $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
            $workshop = $workshopsTable->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($workshopsTable->isUserInOrgaTeam($identity, $workshop)) {
                return true;
            }

        }

        if (in_array($request->getParam('action'), ['edit', 'delete', 'duplicate'])) {

            if (is_null($identity)) {
                return false;
            }

            // repair helpers are not allowed to edit, delete or duplicate events (even not own content - which does not exist because "add" is locked for repairhelpers too)
            if (!($identity->isOrga() || $identity->isAdmin())) {
                return false;
            }

            $eventUid = (int) $request->getParam('pass')[0];

            $eventsTable = TableRegistry::getTableLocator()->get('Events');
            $event = $eventsTable->find('all',
                conditions: [
                    'Events.uid' => $eventUid,
                    'Events.status > ' . APP_DELETED,
                ]
            )->first();
            $workshopUid = $event->workshop_uid;

            if ($request->getParam('action') == 'edit' && $event->datumstart->isPast()) {
                return false;
            }

            if ($identity->isAdmin()) {
                return true;
            }

            // all approved orgas are allowed to edit their events
            $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
            $workshop = $workshopsTable->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($workshopsTable->isUserInOrgaTeam($identity, $workshop)) {
                return true;
            }

            return false;
        }
        
        return true;

    }

}