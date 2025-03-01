<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Model\Table\CategoriesTable;
use App\Model\Table\OrdsCategoriesTable;
use Cake\Event\EventInterface;

class CategoriesController extends AdminAppController
{

    public CategoriesTable $Category;
    public OrdsCategoriesTable $OrdsCategory;

    public function beforeFilter(EventInterface $event): void
    {
        $this->searchUid = false;
        $this->searchText = false;
        parent::beforeFilter($event);
    }

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Category = $this->getTableLocator()->get('Categories');
    }

    public function insert(): void
    {
        $category = [
            'name' => 'Neue Kategorie',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];
        $entity = $this->Category->newEntity($category);
        $category = $this->Category->save($entity);
        $this->AppFlash->setFlashMessage('Kategorie erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function edit($id): void
    {

        if (empty($id)) {
            throw new NotFoundException;
        }

        $category = $this->Category->find('all', conditions: [
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

            $patchedEntity = $this->Category->patchEntity(
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

        $this->OrdsCategory = $this->getTableLocator()->get('OrdsCategories');
        $this->set('ordsCategories', $this->OrdsCategory->getForDropdown());
        $this->set('mainCategories', $this->Category->getForDropdown([APP_ON, APP_OFF]));

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Categories.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->Category->find('all',
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
            $object->info_sheet_count = $this->Category->getInfoSheetCount($object->id);
        }
        $this->set('objects', $objects);

        $metaTags = [
            'title' => 'Kategorien'
        ];
        $this->set('metaTags', $metaTags);

    }

}