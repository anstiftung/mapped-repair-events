<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\ORM\TableRegistry;
use Authorization\IdentityInterface;

class InfoSheetsPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if (!Configure::read('AppConfig.statisticsEnabled')) {
            return false;
        }

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
                $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
                $workshop = $workshopsTable->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
                if ($workshopsTable->isUserInOrgaTeam($identity, $workshop)) {
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
            $infoSheetsTable = TableRegistry::getTableLocator()->get('InfoSheets');

            // orgas are allowed to edit and delete only info sheets of associated workshops
            if ($identity->isOrga()) {

                $infoSheet = $infoSheetsTable->find('all',
                    conditions: [
                        'InfoSheets.uid' => $infoSheetUid,
                        'InfoSheets.status > ' . APP_DELETED,
                    ],
                    contain: [
                        'Events',
                    ],
                )->first();

                $workshopUid = $infoSheet->event->workshop_uid;
                $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
                $workshop = $workshopsTable->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
                if ($workshopsTable->isUserInOrgaTeam($identity, $workshop)) {
                    return true;
                }

            }

            // repairhelpers are allowed to edit and delete only own info sheets
            if ($identity->isRepairhelper()) {
                $infoSheet = $infoSheetsTable->find('all',
                    conditions: [
                        'InfoSheets.uid' => $infoSheetUid,
                        'InfoSheets.owner' => $identity !== null ? $identity->uid : 0,
                        'InfoSheets.status > ' . APP_DELETED,
                    ]
                )->first();
                if (!empty($infoSheet)) {
                    return true;
                }
            }

            return false;

        }

        return $identity !== null;

    }

}