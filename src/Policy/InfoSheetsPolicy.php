<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Cake\Datasource\FactoryLocator;
use Authorization\Policy\RequestPolicyInterface;

class InfoSheetsPolicy implements RequestPolicyInterface
{

    protected $InfoSheet;
    protected $Workshop;

    public function canAccess($identity, ServerRequest $request)
    {

        if (in_array($request->getParam('action'), ['fullDownload'])) {
            if ($identity->isAdmin()) {
                return true;
            }
        }

        if (in_array($request->getParam('action'), ['download'])) {

            if ($identity->isAdmin()) {
                return true;
            }

            if ($identity->isOrga()) {
                $workshopUid = (int) $request->getParam('pass')[0];
                $this->Workshop = FactoryLocator::get('Table')->get('Workshops');
                $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
                if ($this->Workshop->isUserInOrgaTeam($identity, $workshop)) {
                    return true;
                }
            }

        }

        if (in_array($request->getParam('action'), ['edit', 'delete'])) {

            // admin are allowd to edit and delete all info sheets
            if ($identity->isAdmin()) {
                return true;
            }

            $infoSheetUid = (int) $request->getParam('pass')[0];
            $this->InfoSheet = FactoryLocator::get('Table')->get('InfoSheets');

            // orgas are allowed to edit and delete only info sheets of associated workshops
            if ($identity->isOrga()) {

                $infoSheet = $this->InfoSheet->find('all', [
                    'conditions' => [
                        'InfoSheets.uid' => $infoSheetUid,
                        'InfoSheets.status > ' . APP_DELETED
                    ],
                    'contain' => [
                        'Events'
                    ]
                ])->first();

                $workshopUid = $infoSheet->event->workshop_uid;
                $this->Workshop = FactoryLocator::get('Table')->get('Workshops');
                $workshop = $this->Workshop->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
                if ($this->Workshop->isUserInOrgaTeam($identity, $workshop)) {
                    return true;
                }

            }

            // repairhelpers are allowed to edit and delete only own info sheets
            if ($identity->isRepairhelper()) {
                $infoSheet = $this->InfoSheet->find('all', [
                    'conditions' => [
                        'InfoSheets.uid' => $infoSheetUid,
                        'InfoSheets.owner' => $identity !== null ? $identity->uid : 0,
                        'InfoSheets.status > ' . APP_DELETED
                    ]
                ])->first();
                if (!empty($infoSheet)) {
                    return true;
                }
            }

            return false;

        }

        return $identity !== null;

    }

}