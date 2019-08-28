<?php

namespace App\Model\Table;
use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class WorknewsTable extends Table
{
 
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id');
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_uid'
        ]);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->email('email', false, 'Bitte trage eine gültige E-Mail ein.');
        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->add('email', 'unique', [
            'rule' => ['validateUnique', ['scope' => 'workshop_uid']],
            'provider' => 'table',
            'message' => 'Diese E-Mail-Adresse wird bereits verwendet.'
        ]);
        return $validator;
    }
    
    public function getSubscribers($workshopUid)
    {
        $subscribers = $this->find('all', [
            'conditions' => [
                'Worknews.workshop_uid' => $workshopUid,
                'Worknews.confirm' => 'ok'
            ],
            'contain' => [
                'Workshops'
            ]
        ]);
        return $subscribers;
    }
    
    public function sendNotifications($subscribers, $subject, $template, $workshop, $event)
    {
        $email = new Email('default');
        $email->viewBuilder()->setTemplate($template);
        foreach ($subscribers as $subscriber) {
            $email->setTo($subscriber->email)
            ->setSubject($subject)
            ->setViewVars([
                'url' => Configure::read('AppConfig.htmlHelper')->urlEventDetail($workshop->url, $event->uid, $event->datumstart),
                'unsub' => $subscriber->unsub,
                'workshop' => $workshop
            ]);
            $email->send();
        }
    }
    
}
?>