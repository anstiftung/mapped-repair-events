<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Event\EventInterface;

class FundingsController extends AdminAppController
{

    public $searchName = false;
    public $searchText = false;
    public $searchUid = false;


    public function beforeFilter(EventInterface $event) {
        $this->addSearchOptions([
            'Workshops.name' => [
                'name' => 'Workshops.name',
                'searchType' => 'search'
            ],
        ]);
        parent::beforeFilter($event);
    }

    public function edit($uid)
    {

        if (empty($uid)) {
            throw new NotFoundException;
        }

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find('all',
        conditions: [
            $fundingsTable->aliasField('uid') => $uid,
        ],
        contain: [
            'Workshops',
            'Fundinguploads' => function($q) {
                return $q->order(['Fundinguploads.created' => 'DESC']);
            },
        ])->first();

        if (empty($funding)) {
            throw new NotFoundException;
        }

        $this->set('uid', $funding->uid);

        $this->setReferer();

        if (!empty($this->request->getData())) {
            $associtions =  ['associated' => ['Fundinguploads']];
            $patchedEntity = $fundingsTable->patchEntity($funding, $this->request->getData(), $associtions);
            if (!($patchedEntity->hasErrors())) {
                $fundingsTable->save($patchedEntity, $associtions);
                $this->redirect($this->getReferer());
            } else {
                $funding = $patchedEntity;
            }
        }

        $this->set('funding', $funding);
    }

    public function index()
    {
        parent::index();
        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $workshopsTable = $this->getTableLocator()->get('Workshops');

        $query = $fundingsTable->find('all',
        conditions: $this->conditions,
        contain: [
            'Workshops' => $workshopsTable->getFundingContain(),
            'OwnerUsers',
            'Fundinguploads',
            'Fundingbudgetplans',
        ]);

        $objects = $this->paginate($query, [
            'sortableFields' => [
                'Workshops.name',
            ],
            'order' => [
                'Workshops.name' => 'ASC'
            ],
        ]);

        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }

        $this->set('objects', $objects);

    }

}
?>