<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Mailer\Email;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class NewslettersController extends AppController {
    
    public function beforeFilter(EventInterface $event) {
        
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'activate',
            'unsubscribe',
            'index'
        ]);
        
    }
    
    public function index() {
        
        $metaTags = [
            'title' => 'Newsletter ' . Configure::read('AppConfig.platformName'),
            'description' => 'Abonniere hier den Netzwerk-Newsletter mit Informationen rund ums gemeinsame Reparieren in ehrenamtlichen Reparatur-Projekten',
            'keywords' => ' reparatur-initaitiven, reparatur initiativen, repair café, repaircafé, newsletter, netzwerk, reparieren, repair, reparatur'
        ];
        $this->set('metaTags', $metaTags);
        
        $this->Newsletter = TableRegistry::getTableLocator()->get('Newsletters');
        $this->loadComponent('CptNewsletter');
        $newsletter = $this->CptNewsletter->getConfirmedNewsletterForEmail($this->AppAuth->getUserEmail());
        
        if (!empty($this->request->getData())) {
            
            $newsletter = $this->CptNewsletter->prepareEntity($this->request->getData());
            
            if (empty($newsletter->getErrors())) {
                $this->CptNewsletter->save($newsletter);
                
                $email = new Email('default');
                $email->viewBuilder()->setTemplate('activate_newsletter');
                $email->setSubject(__('Please activate your newsletter subscription'))
                ->setViewVars([
                    'domain' => Configure::read('App.fullBaseUrl'),
                    'confirmationCode' => $this->CptNewsletter->getConfirmationCode(),
                    'unsubscribeCode' => $this->CptNewsletter->getUnsubscribeCode()
                ])->setTo($this->request->getData('Newsletters.email'));
                
                $email->send();
                $this->AppFlash->setFlashMessage(__('Please activate your subscription using the activation link sent to') . ' ' . $this->request->getData('Newsletters.email'));
                
            } else {
                $this->AppFlash->setFlashError('Es ist ein Fehler aufgetreten!');
            }
            
        } else {
            if (empty($newsletter)) {
                // prefill field email with email of logged user
                $newsletter = $this->Newsletter->newEntity(
                    [
                        'email' => $this->AppAuth->getUserEmail(),
                        'plz' => $this->AppAuth->user('zip')
                    ],
                    ['validate' => false]
                    );
            }
        }
        
        $subscribed = $newsletter->confirm == 'ok' && $this->AppAuth->user() && $newsletter->email == $this->AppAuth->getUserEmail();
        $this->set('subscribed', $subscribed);
        $this->set('newsletter', $newsletter);
        
    }
    
    
    public function activate() {
        
        if (empty($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('param not found');
        }
        
        $this->Newsletter = TableRegistry::getTableLocator()->get('Newsletters');
        $newsletter = $this->Newsletter->find('all', [
            'conditions' => [
                'Newsletters.confirm' => $this->request->getParam('pass')['0'],
                'Newsletters.confirm != \'ok\''
            ]
        ])->first();
        
        if (empty($newsletter)) {
            $this->AppFlash->setFlashError(__('Invalid activation code.'));
            $this->redirect('/');
            return;
        }
        
        $this->loadComponent('CptNewsletter');
        $this->CptNewsletter->activateNewsletterAndSendNotification($newsletter);
        
        $this->AppFlash->setFlashMessage(__('Your subscription is activated!'));
        
        $this->redirect('/');
        
    }
    
    public function unsubscribe() {
        
        if (empty($this->request->getParam('pass')['0'])) {
            throw new NotFoundException('param not found');
        }
        
        $this->Newsletter = TableRegistry::getTableLocator()->get('Newsletters');
        $newsletter = $this->Newsletter->find('all', [
            'conditions' => [
                'Newsletters.unsub' => $this->request->getParam('pass')['0'],
            ]
        ])->first();
        
        if (empty($newsletter)) {
            $this->AppFlash->setFlashError(__('Invalid unsubscribe code.'));
            $this->redirect('/');
            return;
        }
        
        $this->Newsletter->delete(
            $this->Newsletter->get($newsletter->id)
            );
        
        $email = new Email('default');
        $email->viewBuilder()->setTemplate('activated_newsletter_notification');
        $email->setSubject('Neue E-Mailadresse ausgetragen')
        ->setViewVars([
            'newsletter' => $newsletter
        ])->setTo(Configure::read('AppConfig.notificationMailAddress'));
        
        $email->send();
        
        $this->AppFlash->setFlashMessage($newsletter->email . ' ' . __('is removed!'));
        $this->redirect('/');
        
    }
    
}
?>