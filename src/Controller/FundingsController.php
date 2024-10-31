<?php

namespace App\Controller;

use Cake\Core\Configure;
use App\Model\Table\WorkshopsTable;
use Cake\Database\Query;

class FundingsController extends AppController
{

    private function getContain() {
        return [
            'Fundings',
            'FundingAllPastEvents' => function (Query $q) {
                return $q->select(['workshop_uid', 'count' => $q->func()->count('*')])->groupBy('workshop_uid');
            },
            'FundingAllFutureEvents' => function (Query $q) {
                return $q->select(['workshop_uid', 'count' => $q->func()->count('*')])->groupBy('workshop_uid');
            }
        ];
    }

    public function index() {

        $this->set('metaTags', [
            'title' => 'Förderantrag',
        ]);

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_OFF, $this->getContain());
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_OFF, $this->getContain());
        }

        /*
        $workshops->where([
            'Workshops.url' => 'umweltzentrum-stockach-e-v',
        ]);
        */

        $workshopsWithFundingAllowed = [];
        $workshopsWithFundingNotAllowed = [];
        if ($this->isAdmin()) {
            foreach ($workshops as $workshop) {
                if ($workshop->funding_is_allowed) {
                    $workshopsWithFundingAllowed[] = $workshop;
                } else {
                    $workshopsWithFundingNotAllowed[] = $workshop;
                }
            }
        }

        unset($workshops);

        $this->set([
            'workshopsWithFundingAllowed' => $workshopsWithFundingAllowed,
            'workshopsWithFundingNotAllowed' => $workshopsWithFundingNotAllowed,
        ]);

    }

    public function edit() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');
        $workshopsTable = $this->getTableLocator()->get('Workshops');

        $workshop = $workshopsTable->find()->where([
            $workshopsTable->aliasField('uid') => $workshopUid,
            $workshopsTable->aliasField('status') => APP_ON,
        ])
        ->contain($this->getContain())
        ->first();

        $this->setReferer();

        if (!$workshop->funding_is_allowed) {
            $this->AppFlash->setFlashError('Förderantrag für diese Initiative nicht möglich.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFunding());
        }

        if (!empty($this->request->getData())) {

            $patchedEntity = $workshopsTable->getPatchedEntityForAdminEdit($workshop, $this->request->getData());
            $errors = $patchedEntity->getErrors();

            if (empty($errors)) {
                $entity = $this->stripTagsFromFields($patchedEntity, 'Workshop');
                if ($workshopsTable->save($entity)) {
                    $this->AppFlash->setFlashMessage('Förderantrag erfolgreich gespeichert.');
                }
            }

        }

        $this->set('metaTags', [
            'title' => 'Förderantrag für "' . h($workshop->name) . '"',
        ]);
        $this->set('workshop', $workshop);

    }

    public function uploadActivityProof() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        
        $funding = $fundingsTable->findOrCreate([
            $fundingsTable->aliasField('workshop_uid') => $workshopUid,
        ], function ($entity) use ($workshopUid) {
            $entity->workshop_uid = $workshopUid;
            $entity->user_uid = $this->loggedUser->uid;
        });

        $funding = $fundingsTable->find()->where([
            $fundingsTable->aliasField('workshop_uid') => $workshopUid,
        ])->contain([
            'Workshops',
        ])->first();


        $this->setReferer();

        if (!empty($this->request->getData())) {

            $activityProof = $this->request->getData('Fundings.activity_proof');

            if (!in_array($activityProof->getClientMediaType(), ['application/pdf', 'image/jpeg', 'image/png'])) {
                $funding->setError('activity_proof', ['type' => 'Erlaubte Dateiformate: PDF, JPG oder PNG.']);
            }

            if ($activityProof->getSize() > 5 * 1024 * 1024) {
                $funding->setError('activity_proof', ['size' => 'Max. Dateigröße: 5 MB.']);
            }

            $fileName = $activityProof->getClientFilename();
            $funding->activity_proof_filename = $fileName;

            if ($fundingsTable->save($funding)) {

                $filePath = ROOT . DS . 'files_private' . DS . 'fundings' . DS . $workshopUid . DS . $fileName;
                if (!is_dir(dirname($filePath))) {
                    mkdir(dirname($filePath), 0777, true);
                }
                $activityProof->moveTo($filePath);

                $this->AppFlash->setFlashMessage(__('Der Aktivitätsnachweis wurde erfolgreich hochgeladen.'));
                $this->redirect($this->getPreparedReferer());
            } else {
                $this->AppFlash->setFlashError('Der Aktivitätsnachweis konnte nicht hochgeladen werden.');
            }

        }

        $this->set('metaTags', [
            'title' => 'Aktivitätsnachweis für "' . h($funding->workshop->name) . '" hochladen',
        ]);
        $this->set('funding', $funding);

    }

}