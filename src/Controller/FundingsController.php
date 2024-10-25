<?php

namespace App\Controller;

use Cake\Core\Configure;

class FundingsController extends AppController
{

    public function index() {

        $this->set('metaTags', [
            'title' => 'Förderantrag',
        ]);

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_OFF, ['AllEvents']);
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_OFF, ['AllEvents']);
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
        ->contain(['AllEvents'])
        ->first();

        $this->set('workshop', $workshop);

        if (!$workshop->funding_is_allowed) {
            $this->AppFlash->setFlashError('Förderantrag für diese Initiative nicht möglich.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFunding());
        }

        $this->set('metaTags', [
            'title' => 'Förderantrag für "' . h($workshop->name) . '"',
        ]);

    }

}