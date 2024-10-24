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
        // complicated is-user-orga-check no needed again because this page is only accessible for orga users
        if ($this->isAdmin()) {
            $workshops = $workshopsTable->getWorkshopsForAdmin(APP_DELETED);
        } else {
            $workshops = $workshopsTable->getWorkshopsForAssociatedUser($this->isLoggedIn() ? $this->loggedUser->uid : 0, APP_DELETED);
        }
        $this->set('workshops', $workshops);

    }

    public function detail() {

        $workshopUid = (int) $this->getRequest()->getParam('workshopUid');
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $workshop = $workshopsTable->get($workshopUid);
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