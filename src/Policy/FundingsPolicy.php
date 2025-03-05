<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Funding;

class FundingsPolicy implements RequestPolicyInterface
{

    private function getOwnerEntity($fundingUid, $identity): ?Funding
    {
        $fundingsTable = TableRegistry::getTableLocator()->get('Fundings');
        $entity = $fundingsTable->find()->where([
            $fundingsTable->aliasField('uid') => $fundingUid,
            $fundingsTable->aliasField('owner') => $identity->uid,
        ])->first();
        return $entity;
    }

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


        if (in_array($request->getParam('action'), ['uploadZuwendungsbestaetigung'])) {
            
            $fundingUid = (int) $request->getParam('uid');
            $entity = $this->getOwnerEntity($fundingUid, $identity);
            if (empty($entity)) {
                return false;
            }

            return true;

        }

        if (in_array($request->getParam('action'), ['download'])) {

            if  ($identity->isAdmin()) {
                return true;
            }
            
            $fundingUid = (int) $request->getParam('uid');
            $entity = $this->getOwnerEntity($fundingUid, $identity);
            if (empty($entity)) {
                return false;
            }

            return true;

        }

        if (in_array($request->getParam('action'), ['uploadDetail'])) {

            if  ($identity->isAdmin()) {
                return true;
            }

            $fundinguploadId = $request->getParam('uid');
            $fundinguploadsTable = TableRegistry::getTableLocator()->get('Fundinguploads');
            $fundinguploadEntity = $fundinguploadsTable->find()->where([
                $fundinguploadsTable->aliasField('id') => $fundinguploadId,
            ])->first();

            if (empty($fundinguploadEntity)) {
                return false;
            }
            
            $entity = $this->getOwnerEntity($fundinguploadEntity->funding_uid, $identity);
            if (empty($entity)) {
                return false;
            }

            return true;

        }

        if (in_array($request->getParam('action'), ['delete'])) {
            $fundingUid = (int) $request->getParam('uid');
            $entity = $this->getOwnerEntity($fundingUid, $identity);
            if (empty($entity)) {
                return false;
            }

            if ($entity->is_submitted) {
                return false;
            }

            return true;
        }

        if (in_array($request->getParam('action'), ['edit'])) {

            $workshopUid = (int) $request->getParam('uid');

            // only approved orgas are allowed to edit fundings
            $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
            $workshop = $workshopsTable->find('all',
            conditions: [
                'Workshops.uid' => $workshopUid,
                'Workshops.status >= ' . APP_OFF
            ]
            )->first();
            if (empty($workshop)) {
                return false;
            }
    
            $workshop = $workshopsTable->getWorkshopForIsUserInOrgaTeamCheck($workshopUid);
            if ($workshopsTable->isUserInOrgaTeam($identity, $workshop)) {
                return true;
            }

            return false;
        }

        return true;

    }

}