<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;

class OrdsCategoriesController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->OrdsCategory = $this->getTableLocator()->get('OrdsCategories');
    }

    public function insert()
    {
        $ordsCategory = [
            'name' => 'Neue ORDS-Kategorie',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_ON,
        ];
        $entity = $this->OrdsCategory->newEntity($ordsCategory);
        $this->OrdsCategory->save($entity);
        $this->AppFlash->setFlashMessage('ORDS-Kategorie erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function edit($id)
    {

        if (empty($id)) {
            throw new NotFoundException;
        }

        $ordsCategory = $this->OrdsCategory->find('all', [
            'conditions' => [
                'OrdsCategories.id' => $id,
                'OrdsCategories.status >= ' . APP_DELETED
            ]
        ])->first();

        if (empty($ordsCategory)) {
            throw new NotFoundException;
        }

        $this->set('id', $ordsCategory->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $this->OrdsCategory->patchEntity(
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

    public function index()
    {
        parent::index();

        $conditions = [
            'OrdsCategories.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->OrdsCategory->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OwnerUsers',
            ],
            'order' => [
                'OrdsCategories.name' => 'ASC',
            ]
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