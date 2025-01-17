<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class SendWorknewsNotificationCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        // find all events that start in one week
        $eventsTable = $this->getTableLocator()->get('Events');
        $events = $eventsTable->find('all',
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
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        foreach($events as $event) {
            $subscribers = $worknewsTable->getSubscribers($event->workshop_uid);
            if (!empty($subscribers)) {
                if (!empty($event->workshop)) {
                    $worknewsTable->sendNotifications($subscribers, 'Reparatur-Termin nächste Woche: ' . $event->workshop->name, 'event_next_week', $event->workshop, $event);
                }
            }
        }

        return static::CODE_SUCCESS;

    }

}
