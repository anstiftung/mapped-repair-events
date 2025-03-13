<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Model\Table\BrandsTable;

class BrandsController extends AdminAppController
{

    public BrandsTable $Brand;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        // keep that because of AppController::stripTagsFromFields()
        $this->Brand = $this->getTableLocator()->get('Brands');
    }
    
    public function insert(): void
    {
        $brand = [
            'name' => 'Neue Marke',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];
        $brandsTable = $this->getTableLocator()->get('Brands');
        $entity = $brandsTable->newEntity($brand);
        $brand = $brandsTable->save($entity);
        $this->AppFlash->setFlashMessage('Marke erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function setApprovedMultiple(): void {
        $selectedIds = $this->request->getQuery('selectedIds', '');
        $selectedIds = explode(',', $selectedIds);
        $brandsTable = $this->getTableLocator()->get('Brands');
        $affectedCount = $brandsTable->setApprovedMultiple($selectedIds);
        $this->AppFlash->setFlashMessage($affectedCount . ' Marken erfolgreich bestätigt.');
        $this->redirect($this->getReferer());
    }
    
    public function edit($id): void
    {

        if (empty($id)) {
            throw new NotFoundException;
        }

        $brandsTable = $this->getTableLocator()->get('Brands');
        $brand = $brandsTable->find('all', conditions: [
            'Brands.id' => $id,
            'Brands.status >= ' . APP_DELETED
        ])->first();

        if (empty($brand)) {
            throw new NotFoundException;
        }

        $this->set('id', $brand->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $brandsTable->patchEntity(
                $brand,
                $this->request->getData(),
                ['validate' => true]
            );

            if (!($patchedEntity->hasErrors())) {
                $this->saveObject($patchedEntity);
            } else {
                $brand = $patchedEntity;
            }
        }

        $this->set('brand', $brand);

        $metaTags = ['title' => 'Marke bearbeiten'];
        $this->set('metaTags', $metaTags);

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Brands.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $brandsTable = $this->getTableLocator()->get('Brands');
        $query = $brandsTable->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers'
        ]);
        $objects = $this->paginate($query, [
            'order' => [
                'Brands.name' => 'ASC'
            ]
        ]);

        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }

        $this->set('objects', $objects);

        $metaTags = [
            'title' => 'Marken'
        ];
        $this->set('metaTags', $metaTags);

    }

}