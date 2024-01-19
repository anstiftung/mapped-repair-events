<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Log\Log;

class SendWorknewsNotificationCommand extends Command
{

    public $Event;

    public $Worknews;

    public function execute(Arguments $args, ConsoleIo $io)
    {

        $this->Event = $this->getTableLocator()->get('Events');

        // find all events that start in one week
        $events = $this->Event->find('all',
        conditions: [
            'Events.status' => APP_ON,
            'DATEDIFF(Events.datumstart, NOW()) = 7'
        ],
        contain: [
            'Workshops' => [
                'conditions' => [
                    'Workshops.status' => APP_ON,
                ]
            ]
        ]);

        // send notification mail to all subscribers
        $this->Worknews = $this->getTableLocator()->get('Worknews');
        foreach($events as $event) {
            $subscribers = $this->Worknews->getSubscribers($event->workshop_uid);
            if (!empty($subscribers)) {
                if (empty($event->workshop)) {
                    Log::error('Workshop not found for event ' . $event->uid);
                    continue;
                }
                $this->Worknews->sendNotifications($subscribers, 'Reparatur-Termin nächste Woche: ' . $event->workshop->name, 'event_next_week', $event->workshop, $event);
            }
        }

        return static::CODE_SUCCESS;

    }

}
