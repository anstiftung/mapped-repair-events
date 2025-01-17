<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Event\EventInterface;
use App\Model\Table\EventsTable;
use App\Model\Table\UsersTable;

class EventsController extends AdminAppController
{

    public EventsTable $Event;
    public UsersTable $User;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Event = $this->getTableLocator()->get('Events');
    }

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

        $query = $this->Event->find('all',
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

        $this->User = $this->getTableLocator()->get('Users');
        $this->set('users', $this->User->getForDropdown());
    }

}