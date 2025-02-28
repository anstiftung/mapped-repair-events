<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Event\EventInterface;
use App\Model\Table\InfoSheetsTable;
use App\Model\Table\UsersTable;
use App\Model\Table\WorkshopsTable;

class WorkshopsController extends AdminAppController
{

    public WorkshopsTable $Workshop;
    public UsersTable $User;
    public InfoSheetsTable $InfoSheet;

    public function beforeFilter(EventInterface $event): void
    {

        $this->addSearchOptions([
            'Workshops.zip' => [
                'name' => 'PLZ',
                'searchType' => 'search'
            ],
            'Workshops.city' => [
                'name' => 'Stadt',
                'searchType' => 'search'
            ],
            'Workshops.street' => [
                'name' => 'Straße + Hausnummer',
                'searchType' => 'search'
            ],
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

        parent::beforeFilter($event);

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Workshops.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $workshopsTable = $this->getTableLocator()->get('Workshops');

        $query = $workshopsTable->find('all',
        conditions: $conditions,
        contain: [
            'Countries',
            'OwnerUsers',
            'Users',
            'Provinces',
        ]);

        $query = $this->addMatchingsToQuery($query);

        $objects = $this->paginate($query, [
            'order' => [
                'Workshops.created' => 'DESC'
            ],
        ]);

        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $worknewsTable = $this->getTableLocator()->get('Worknews');
        foreach($objects as $object) {
            $object->workshop_info_sheets_count = $infoSheetsTable->workshopInfoSheetsCount($object->uid);
            $object->worknews_count = $worknewsTable->getSubscribers($object->uid)->count();
            foreach($object->users as $user) {
                $user->revertPrivatizeData();
            }
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }

        $this->set('objects', $objects);

        $usersTable = $this->getTableLocator()->get('Users');
        $this->set('users', $usersTable->getForDropdown());
    }
}
?>