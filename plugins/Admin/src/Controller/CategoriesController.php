<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Event\EventInterface;
use App\Model\Table\CategoriesTable;

class CategoriesController extends AdminAppController
{

    public CategoriesTable $Category;

    public function initialize(): void
    {
        parent::initialize();
        // keep that because of AppController::stripTagsFromFields()
        $this->Category = $this->getTableLocator()->get('Categories');
    }

    public function beforeFilter(EventInterface $event): void
    {
        $this->searchUid = false;
        $this->searchText = false;
        parent::beforeFilter($event);
    }

    public function setApprovedMultiple(): void {
        $selectedIds = $this->request->getQuery('selectedIds', '');
        $selectedIds = explode(',', $selectedIds);
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $affectedCount = $categoriesTable->setApprovedMultiple($selectedIds);
        $this->AppFlash->setFlashMessage($affectedCount . ' Kategorien erfolgreich bestätigt.');
        $this->redirect($this->getReferer());
    }

    public function insert(): void
    {
        $category = [
            'name' => 'Neue Kategorie',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $entity = $categoriesTable->newEntity($category);
        $category = $categoriesTable->save($entity);
        $this->AppFlash->setFlashMessage('Kategorie erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function edit(int $id): void
    {
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $category = $categoriesTable->find('all', conditions: [
            'Categories.id' => $id,
            'Categories.status >= ' . APP_DELETED
        ])->first();

        if (empty($category)) {
            throw new NotFoundException;
        }

        $this->set('id', $category->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $this->request = $this->request->withData('Categories.carbon_footprint', str_replace(',', '.', $this->request->getData('Categories.carbon_footprint')));
            $this->request = $this->request->withData('Categories.material_footprint', str_replace(',', '.', $this->request->getData('Categories.material_footprint')));

            $categoriesTable = $this->getTableLocator()->get('Categories');
            $patchedEntity = $categoriesTable->patchEntity(
                $category,
                $this->request->getData(),
                ['validate' => true]
            );

            if (!($patchedEntity->hasErrors())) {
                $this->saveObject($patchedEntity);
            } else {
                $category = $patchedEntity;
            }
        }

        $this->set('category', $category);

        $metaTags = ['title' => 'Kategorie bearbeiten'];
        $this->set('metaTags', $metaTags);

        $ordsCategoriesTable = $this->getTableLocator()->get('OrdsCategories');
        $this->set('ordsCategories', $ordsCategoriesTable->getForDropdown());
        $this->set('mainCategories', $categoriesTable->getForDropdown([APP_ON, APP_OFF]));

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Categories.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $categoriesTable = $this->getTableLocator()->get('Categories');
        $query = $categoriesTable->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
            'ParentCategories',
            'OrdsCategories',
        ],
        order: [
            'ParentCategories.name' => 'ASC',
            'Categories.name' => 'ASC'
        ]);

        $objects = $this->paginate($query);

        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
            $object->info_sheet_count = $categoriesTable->getInfoSheetCount($object->id);
        }
        $this->set('objects', $objects);

        $metaTags = [
            'title' => 'Kategorien'
        ];
        $this->set('metaTags', $metaTags);

    }

}