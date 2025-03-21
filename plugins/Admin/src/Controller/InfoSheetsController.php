<?php
declare(strict_types=1);
namespace Admin\Controller;

use App\Model\Table\InfoSheetsTable;
use Cake\Event\EventInterface;
use App\Model\Table\UsersTable;

class InfoSheetsController extends AdminAppController
{

    public InfoSheetsTable $InfoSheet;
    public UsersTable $User;

    public bool $searchName = false;
    public bool $searchText = false;

    public function initialize(): void
    {
        parent::initialize();
        // keep that because of AppController::stripTagsFromFields()
        $this->InfoSheet = $this->getTableLocator()->get('InfoSheets');
    }

    public function beforeFilter(EventInterface $event): void
    {
        $this->addSearchOptions([
            'Brands.name' => [
                'name' => 'Marke',
                'searchType' => 'equal'
            ],
            'Categories.name' => [
                'name' => 'Unterkategorie',
                'searchType' => 'equal'
            ],
        ]);
        $this->addSearchOptions([
            'InfoSheets.owner' => [
                'name' => 'InfoSheets.owner',
                'searchType' => 'equal',
                'extraDropdown' => true
            ],
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
            'InfoSheets.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->InfoSheet->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
            'Events',
            'Events.Workshops',
            'Brands',
            'Categories.ParentCategories',
        ]);

        $objects = $this->paginate($query, [
            'order' => [
                'InfoSheets.created' => 'DESC'
            ],
        ]);

        foreach($objects as $object) {
            if (empty($object->owner_user)) {
                continue;
            }
            $object->owner_user->revertPrivatizeData();
        }

        $this->set('objects', $objects);

        $this->User = $this->getTableLocator()->get('Users');
        $this->set('users', $this->User->getForDropdown());

        $metaTags = [
            'title' => 'Laufzettel'
        ];
        $this->set('metaTags', $metaTags);

        $usersTable = $this->getTableLocator()->get('Users');
        $this->set('users', $usersTable->getForDropdown());

    }

}