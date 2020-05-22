<?php
namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

class EventsController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Event = TableRegistry::getTableLocator()->get('Events');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->addSearchOptions(array(

            'Event.ort' => array(
                'name' => 'Event.ort',
                'searchType' => 'search'
            ),
            'Event.author' => array(
                'name' => 'Event.author',
                'searchType' => 'search'
            ),

            'Event.owner' => array(
                'name' => 'Event.owner',
                'searchType' => 'equal',
                'extraDropdown' => true
            )
        ));

        // für optional dropdown
        $this->generateSearchConditions('opt-1');
        $this->generateSearchConditions('opt-2');

    }

    public function index()
    {
        parent::index();

        $conditions = [
            'Workshops.status' => APP_ON,
            'DATE(Events.datumstart) >= DATE(NOW())'
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->Event->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OwnerUsers',
                'Workshops'
            ]
        ]);

        $query = $this->addMatchingsToQuery($query);

        $objects = $this->paginate($query, [
            'order' => [
                'Events.updated' => 'DESC'
            ]
        ]);
        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }
        $this->set('objects', $objects->toArray());

        $this->User = TableRegistry::getTableLocator()->get('Users');
        $this->set('users', $this->User->getForDropdown());
    }

}