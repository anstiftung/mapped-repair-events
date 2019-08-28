<?php
namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;

class CptNewsletterComponent extends AppComponent
{
    
    private $confirmationCode = '';
    private $unsubscribeCode = '';
    
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->Newsletter = TableRegistry::getTableLocator()->get('Newsletters');
    }
    
    public function setConfirmationCode($confirmationCode)
    {
        $this->confirmationCode = $confirmationCode;
    }
    
    public function setUnsubscribeCode($unsubscribeCode)
    {
        $this->unsubscribeCode = $unsubscribeCode;
    }
    
    public function getUnsubscribeCode() {
        return $this->unsubscribeCode;
    }
    
    public function getConfirmationCode() {
        return $this->confirmationCode;
    }
    
    public function save($data)
    {
        return $this->Newsletter->save($data);
    }
    
    public function getConfirmedNewsletterForEmail($email)
    {
        $newsletter = $this->Newsletter->find('all', [
            'conditions' => [
                'Newsletters.email' => $email,
                'Newsletters.confirm' => 'ok'
            ]
        ])->first();
        return $newsletter;
    }
    
    public function prepareEntity($data)
    {
        $this->setConfirmationCode(md5(StringComponent::createRandomString()));
        $this->setUnsubscribeCode(md5(StringComponent::createRandomString()));
        $mergedData = array_merge(
            $data,
            [
                'created' => Time::now(),
                'confirm' => $this->getConfirmationCode(),
                'unsub' => $this->getUnsubscribeCode()
            ]
        );
        $newsletter = $this->Newsletter->newEntity($mergedData);
        return $newsletter;
    }
    
    public function activateNewsletterAndSendNotification($newsletter)
    {
        $this->Newsletter->save(
            $this->Newsletter->patchEntity(
                $this->Newsletter->get($newsletter->id),
                [
                    'confirm' => 'ok',
                    'modified' => Time::now()
                ]
            )
        );
        
        $email = new Email('default');
        $email->viewBuilder()->setTemplate('activated_newsletter_notification');
        $email->setSubject('Neue E-Mailadresse eingetragen')
        ->setViewVars([
            'newsletter' => $newsletter
        ])->setTo(Configure::read('AppConfig.notificationMailAddress'));
        
        $email->send();
        
    }
    
}

?>