<?php
namespace Admin\Controller;

use Cake\Event\EventInterface;

class UsersController extends AdminAppController
{

    public $searchName = false;

    public $searchText = false;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->User = $this->getTableLocator()->get('Users');
        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->Country = $this->getTableLocator()->get('Countries');
        $this->Group = $this->getTableLocator()->get('Groups');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->addSearchOptions([
            'Users.firstname' => [
                'name' => 'Users.firstname',
                'searchType' => 'search'
            ],
            'Users.lastname' => [
                'name' => 'Users.lastname',
                'searchType' => 'search'
            ],
            'Users.email' => [
                'name' => 'Users.email',
                'searchType' => 'search'
            ],
            'UsersGroups.group_id' => [
                'name' => 'UsersGroups.group_id',
                'association' => 'Groups',
                'searchType' => 'matching',
                'extraDropdown' => true
            ],
            'UsersWorkshops.workshop_uid' => [
                'name' => 'UsersWorkshop.workshop_uid',
                'association' => 'Workshops',
                'searchType' => 'matching',
                'extraDropdown' => true
            ]
        ]);

        // für optional groups dropdown
        $this->generateSearchConditions('opt-1');
        $this->generateSearchConditions('opt-2');
    }

    public function index()
    {
        parent::index();

        $conditions = [
            'Users.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->User->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OwnerUsers',
                'Groups',
                'Workshops' => [
                    'fields' => [
                        'Workshops.name',
                        'UsersWorkshops.user_uid',
                    ],
                    'conditions' => [
                        'Workshops.status > ' . APP_DELETED,
                    ]
                ],
                'OwnerWorkshops',
            ]
        ]);

        $query = $this->addMatchingsToQuery($query);

        $objects = $this->paginate($query, [
            'order' => [
                'Users.created' => 'DESC'
            ]
        ]);
        foreach($objects->toArray() as &$object) {
            $object->revertPrivatizeData();
        }
        $this->set('objects', $objects);

        $this->Workshop = $this->getTableLocator()->get('Workshops');
        $this->set('workshops', $this->Workshop->getForDropdown());
    }
}
?>