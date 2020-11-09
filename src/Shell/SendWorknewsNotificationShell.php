<?php

namespace App\Shell;

class SendWorknewsNotificationShell extends AppShell
{

    public function main()
    {

        $this->Event = $this->getTableLocator()->get('Events');

        // find all events that start in one week
        $events = $this->Event->find('all', [
            'conditions' => [
                'Events.status' => APP_ON,
                'DATEDIFF(Events.datumstart, NOW()) = 7'
            ],
            'contain' => [
                'Workshops' => [
                    'conditions' => [
                        'Workshops.status' => APP_ON,
                    ]
                ]
            ]
        ]);

        // send notification mail to all subscribers
        $this->Worknews = $this->getTableLocator()->get('Worknews');
        foreach($events as $event) {
            $subscribers = $this->Worknews->getSubscribers($event->workshop_uid);
            if (!empty($subscribers)) {
                $this->Worknews->sendNotifications($subscribers, 'Reparatur-Termin nächste Woche: ' . $event->workshop->name, 'event_next_week', $event->workshop, $event);
            }
        }

    }

}
