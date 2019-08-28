<?php

namespace App\Shell;

use Cake\ORM\TableRegistry;

class SendWorknewsNotificationShell extends AppShell
{
    
    public function main()
    {
        
        $this->Event = TableRegistry::getTableLocator()->get('Events');
        
        // find all events that start in one week
        $events = $this->Event->find('all', [
            'conditions' => [
                'Events.status' => APP_ON,
                'DATEDIFF(Events.datumstart, NOW()) = 7'
            ],
            'contain' => [
                'Workshops'
            ]
        ]);
        
        // send notification mail to all subscribers
        $this->Worknews = TableRegistry::getTableLocator()->get('Worknews');
        foreach($events as $event) {
            $subscribers = $this->Worknews->getSubscribers($event->workshop_uid);
            if (!empty($subscribers)) {
                $this->Worknews->sendNotifications($subscribers, 'Reparatur-Termin nächste Woche: ' . $event->workshop->name, 'event_next_week', $event->workshop, $event);
            }
        }
        
    }
    
}
