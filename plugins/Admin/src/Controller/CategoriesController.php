<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Event\EventInterface;
use App\Model\Table\CategoriesTable;
use Cake\Http\Response;

class CategoriesController extends AdminAppController
{

    public function beforeFilter(EventInterface $event): void
    {
        $this->searchUid = false;
        $this->searchText = false;
        parent::beforeFilter($event);
    }

    public function setApprovedMultiple(): Response {
        $selectedIds = $this->request->getQuery('selectedIds', '');
        $selectedIds = explode(',', $selectedIds);

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $affectedCount = $categoriesTable->setApprovedMultiple($selectedIds);
        $this->AppFlash->setFlashMessage($affectedCount . ' Kategorien erfolgreich bestätigt.');
        return $this->redirect($this->getReferer());
    }

    public function insert(): Response
    {
        $category = [
            'name' => 'Neue Kategorie',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $entity = $categoriesTable->newEntity($category);
        $category = $categoriesTable->save($entity);
        $this->AppFlash->setFlashMessage('Kategorie erfolgreich erstellt.');
        return $this->redirect($this->getReferer());
    }

    public function edit(int $id): ?Response
    {

        /** @var \App\Model\Table\CategoriesTable */
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

            $patchedEntity = $categoriesTable->patchEntity(
                $category,
                $this->request->getData(),
                ['validate' => true]
            );

            if (!($patchedEntity->hasErrors())) {
                return $this->saveObject($patchedEntity);
            } else {
                $category = $patchedEntity;
            }
        }

        $this->set('category', $category);

        $metaTags = ['title' => 'Kategorie bearbeiten'];
        $this->set('metaTags', $metaTags);

        /** @var \App\Model\Table\OrdsCategoriesTable */
        $ordsCategoriesTable = $this->getTableLocator()->get('OrdsCategories');
        $this->set('ordsCategories', $ordsCategoriesTable->getForDropdown());
        $this->set('mainCategories', $categoriesTable->getForDropdown([APP_ON, APP_OFF]));

        return null;

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Categories.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        /** @var \App\Model\Table\CategoriesTable */
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