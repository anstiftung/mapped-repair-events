<?php
namespace Admin\Controller;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class WorkshopsController extends AdminAppController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        
        $this->addSearchOptions([
            'Workshops.owner' => [
                'name' => 'Workshops.owner',
                'searchType' => 'equal',
                'extraDropdown' => true
            ],
            'UsersWorkshops.user_uid' => [
                'name' => 'UsersWorkshops.user_uid',
                'association' => 'Users',
                'searchType' => 'matching',
                'extraDropdown' => true
            ]
        ]);
        
        // für optional dropdown
        $this->generateSearchConditions('opt-1');
        $this->generateSearchConditions('opt-2');
        $this->generateSearchConditions('opt-3');
    }

    public function index()
    {
        parent::index();
        
        $conditions = [
            'Workshops.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);
        
        $this->Workshop = TableRegistry::getTableLocator()->get('Workshops');
        $this->User = TableRegistry::getTableLocator()->get('Users');
        
        $query = $this->Workshop->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Countries',
                'OwnerUsers',
                'Users' => [
                    'fields' => [
                        'UsersWorkshops.workshop_uid'
                    ]
                ]
            ]
        ]);
        
        $query = $this->addMatchingsToQuery($query);
        
        $objects = $this->paginate($query, [
            'order' => [
                'Workshops.name' => 'ASC'
            ]
        ]);
        
        $this->InfoSheet = TableRegistry::getTableLocator()->get('InfoSheets');
        foreach($objects as $object) {
            $object->workshop_info_sheets_count = $this->InfoSheet->workshopInfoSheetsCount($object->uid);
        }
        
        $this->set('objects', $objects->toArray());
        
        $this->set('users', $this->User->getForDropdown());
    }
}
?>