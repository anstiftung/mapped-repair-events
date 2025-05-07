<?php
declare(strict_types=1);
namespace Admin\Controller;

use App\Model\Table\InfoSheetsTable;
use Cake\Event\EventInterface;
use App\Model\Table\UsersTable;

class InfoSheetsController extends AdminAppController
{

    public bool $searchName = false;
    public bool $searchText = false;

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

        /** @var \App\Model\Table\InfoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $query = $infoSheetsTable->find('all',
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

        /** @var \App\Model\Table\UsersTable */
        $usersTable = $this->getTableLocator()->get('Users');
        $this->set('users', $usersTable->getForDropdown());

        $metaTags = [
            'title' => 'Laufzettel'
        ];
        $this->set('metaTags', $metaTags);

    }

}