<?php

namespace App\Controller;

use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class WorknewsController extends AppController {

    public function beforeFilter(Event $event) {
        
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'worknewsActivate',
            'worknewsUnsubscribe'
        ]);
        
    }
    
    public function worknewsActivate() {
        
        if (empty($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('param not found');
        }
        
        $this->Worknews = TableRegistry::getTableLocator()->get('Worknews');
        $worknews = $this->Worknews->find('all', [
            'conditions' => [
                'Worknews.confirm' => $this->request->getParam('pass')['0'],
                'Worknews.confirm != \'ok\'' 
            ],
            'contain' => [
                'Workshops'
            ]
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
                    'modified' => Time::now()
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
        
        $this->Worknews = TableRegistry::getTableLocator()->get('Worknews');
        $worknews = $this->Worknews->find('all', [
            'conditions' => [
                'Worknews.unsub' => $this->request->getParam('pass')['0'],
            ],
            'contain' => [
                'Workshops'
            ]
        ])->first();
        
        if (empty($worknews)) {
            $this->AppFlash->setFlashError(__('Invalid unsubscribe code.'));
            $this->redirect('/');
            return;
        }
        
        $this->Worknews->delete(
            $this->Worknews->get($worknews->id)
        );
        $this->AppFlash->setFlashMessage($worknews->email . ' ' . __('is removed!'));
        $this->redirect($worknews->workshop->url);
        
    }
    
}
?>