<?php

namespace App\Controller;

use App\Model\Table\WorknewsTable;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\DateTime;

class WorknewsController extends AppController {

    public WorknewsTable $Worknews;

    public function beforeFilter(EventInterface $event) {

        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'worknewsActivate',
            'worknewsUnsubscribe'
        ]);

    }

    public function worknewsActivate() {

        if (empty($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('param not found');
        }

        $this->Worknews = $this->getTableLocator()->get('Worknews');
        $worknews = $this->Worknews->find('all',
        conditions: [
            'Worknews.confirm' => $this->request->getParam('pass')['0'],
            'Worknews.confirm != \'ok\''
        ],
        contain: [
            'Workshops'
        ])->first();

        if (empty($worknews)) {
            $this->AppFlash->setFlashError(__('Invalid activation code.'));
            $this->redirect('/');
            return;
        }

        $this->Worknews->save(
            $this->Worknews->patchEntity(
                $this->Worknews->get($worknews->id),
                [
                    'confirm' => 'ok',
                    'modified' => DateTime::now()
                ]
            )
        );
        $this->AppFlash->setFlashMessage(__('Your subscription is activated!'));

        $this->redirect($worknews->workshop->url);

    }

    public function worknewsUnsubscribe() {

        if (empty($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('param not found');
        }

        $this->Worknews = $this->getTableLocator()->get('Worknews');
        $worknews = $this->Worknews->find('all',
        conditions: [
            'Worknews.unsub' => $this->request->getParam('pass')['0'],
        ],
        contain: [
            'Workshops',
        ])->first();

        if (empty($worknews)) {
            $this->AppFlash->setFlashError('Der Abmeldecode ist ungültig oder wurde bereits verwendet.');
            $this->redirect('/');
            return;
        }

        $this->Worknews->delete(
            $this->Worknews->get($worknews->id)
        );
        $this->AppFlash->setFlashMessage('Deine Abmeldung aus der abonnierten Liste ist erfolgt.');
        $this->redirect($worknews->workshop->url);

    }

}
?>