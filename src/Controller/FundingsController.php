<?php

namespace App\Controller;

use Cake\Core\Configure;
use App\Model\Table\WorkshopsTable;

class FundingsController extends AppController
{

    private $contain = [
        'FundingAllPastEvents',
        'FundingAllFutureEvents',
    ];

    public function index() {

        $this->set('metaTags', [
            'title' => 'Förderantrag',
        ]);

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_OFF, $this->contain);
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_OFF, $this->contain);
        }

        $workshopsWithFundingAllowed = 0;
        $workshopsWithFundingNotAllowed = 0;
        if ($this->isAdmin()) {
            foreach ($workshops as $workshop) {
                if ($workshop->funding_is_allowed) {
                    $workshopsWithFundingAllowed++;
                } else {
                    $workshopsWithFundingNotAllowed++;
                }
            }
        }

        $this->set([
            'workshops' => $workshops,
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
        ->contain($this->contain)
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

}