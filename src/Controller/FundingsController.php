<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\Query\SelectQuery;

class FundingsController extends AppController
{

    public function index() {

        $this->set('metaTags', [
            'title' => 'Förderantrag',
        ]);

        $workshopsTable = $this->getTableLocator()->get('Workshops');
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsWithUsers(APP_DELETED, ['AllEvents']);
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->loggedUser->uid, APP_DELETED, ['AllEvents']);
        }
        $this->set('workshops', $workshops);

    }

    public function detail() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');
        $workshopsTable = $this->getTableLocator()->get('Workshops');

        $workshop = $workshopsTable->find()->where([
            $workshopsTable->aliasField('uid') => $workshopUid,
            $workshopsTable->aliasField('status >=') => APP_DELETED
        ])
        ->contain(['AllEvents'])
        ->first();

        $this->set('workshop', $workshop);

        if (!$workshop->is_funding_allowed) {
            $this->AppFlash->setFlashError('Förderantrag für diese Initiative nicht möglich.');
            return $this->redirect(Configure::read('AppConfig.htmlHelper')->urlFunding());
        }

        $this->set('metaTags', [
            'title' => 'Förderantrag für "' . h($workshop->name) . '"',
        ]);

    }

}