<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Event\EventInterface;

class EventsController extends AdminAppController
{

    public function beforeFilter(EventInterface $event): void
    {

        $this->searchText = false;
        $this->searchName = false;
        
        $this->addSearchOptions([
            'Events.eventbeschreibung' => [
                'name' => 'Events.eventbeschreibung',
                'searchType' => 'search'
            ],
            'Events.ort' => [
                'name' => 'Events.ort',
                'searchType' => 'search'
            ],
            'Events.author' => [
                'name' => 'Events.author',
                'searchType' => 'search'
            ],
            'Events.workshop_uid' => [
                'name' => 'Events.workshop_uid',
                'searchType' => 'equal'
            ],
            'Events.owner' => [
                'name' => 'Events.owner',
                'searchType' => 'equal',
                'extraDropdown' => true
            ]
        ]);

        // für optional dropdown
        $this->generateSearchConditions('opt-1');
        $this->generateSearchConditions('opt-2');

        parent::beforeFilter($event);
    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Workshops.status' => APP_ON,
            'DATE(Events.datumstart) >= DATE(NOW())'
        ];
        $conditions = array_merge($this->conditions, $conditions);

        /** @var \App\Model\Table\EventsTable */
        $eventsTable = $this->getTableLocator()->get('Events');
        $query = $eventsTable->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
            'Workshops',
            'Provinces',
        ]);

        $query = $this->addMatchingsToQuery($query);

        $objects = $this->paginate($query, [
            'order' => [
                'Events.uid' => 'DESC'
            ]
        ]);
        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }
        $this->set('objects', $objects);

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $this->set('users', $usersTable->getForDropdown());
    }

}