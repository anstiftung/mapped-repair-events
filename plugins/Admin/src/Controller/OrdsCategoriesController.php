<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Model\Table\OrdsCategoriesTable;

class OrdsCategoriesController extends AdminAppController
{

    public function insert(): void
    {
        $ordsCategory = [
            'name' => 'Neue ORDS-Kategorie',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_ON,
        ];
        /** @var \App\Model\Table\OrdsCategoriesTable */
        $ordsCategoriesTable = $this->getTableLocator()->get('OrdsCategories');
        $entity = $ordsCategoriesTable->newEntity($ordsCategory);
        $ordsCategoriesTable->save($entity);
        $this->AppFlash->setFlashMessage('ORDS-Kategorie erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function edit(int $id): void
    {
        /** @var \App\Model\Table\OrdsCategoriesTable */
        $ordsCategoriesTable = $this->getTableLocator()->get('OrdsCategories');
        $ordsCategory = $ordsCategoriesTable->find('all', conditions: [
            'OrdsCategories.id' => $id,
            'OrdsCategories.status >= ' . APP_DELETED
        ])->first();

        if (empty($ordsCategory)) {
            throw new NotFoundException;
        }

        $this->set('id', $ordsCategory->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $ordsCategoriesTable->patchEntity(
                $ordsCategory,
                $this->request->getData(),
                ['validate' => true]
            );

            if (!($patchedEntity->hasErrors())) {
                $this->saveObject($patchedEntity);
            } else {
                $ordsCategory = $patchedEntity;
            }
        }

        $this->set('ordsCategory', $ordsCategory);

        $metaTags = ['title' => 'ORDS-Kategorie bearbeiten'];
        $this->set('metaTags', $metaTags);

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'OrdsCategories.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        /** @var \App\Model\Table\OrdsCategoriesTable */
        $ordsCategoriesTable = $this->getTableLocator()->get('OrdsCategories');
        $query = $ordsCategoriesTable->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
        ],
        order: [
            'OrdsCategories.name' => 'ASC',
        ]);

        $objects = $this->paginate($query);

        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }

        $this->set('objects', $objects);

        $metaTags = [
            'title' => 'ORDS-Kategorien'
        ];
        $this->set('metaTags', $metaTags);

    }

}