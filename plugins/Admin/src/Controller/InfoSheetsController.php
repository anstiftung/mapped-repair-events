<?php
namespace Admin\Controller;

use Cake\Event\EventInterface;

class InfoSheetsController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->addSearchOptions(array(
            'InfoSheets.owner' => array(
                'name' => 'InfoSheets.owner',
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
            'InfoSheets.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->InfoSheet->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OwnerUsers',
                'Events',
                'Events.Workshops',
                'Brands',
                'Categories.ParentCategories'
            ]
        ]);

        $objects = $this->paginate($query, [
            'order' => [
                'InfoSheets.created' => 'DESC'
            ],
            'limit' => 100,
        ]);

        foreach($objects as $object) {
            $object->owner_user->revertPrivatizeData();
        }

        $this->set('objects', $objects->toArray());

        $this->User = $this->getTableLocator()->get('Users');
        $this->set('users', $this->User->getForDropdown());

        $metaTags = [
            'title' => 'Laufzettel'
        ];
        $this->set('metaTags', $metaTags);

    }

}