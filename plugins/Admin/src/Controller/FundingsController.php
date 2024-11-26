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
        $workshopsTable = $this->getTableLocator()->get('Workshops');
        $funding = $fundingsTable->find('all',
        conditions: [
            $fundingsTable->aliasField('uid') => $uid,
        ],
        contain: [
            'Workshops' => $workshopsTable->getFundingContain(),
            'OwnerUsers',
            'Fundingdatas',
            'Fundingbudgetplans',
            'Fundingsupporters',
            'FundinguploadsActivityProofs' => function($q) {
                return $q->order(['FundinguploadsActivityProofs.created' => 'DESC']);
            },
            'FundinguploadsFreistellungsbescheids' => function($q) {
                return $q->order(['FundinguploadsFreistellungsbescheids.created' => 'DESC']);
            },
        ])->first();

        if ($funding->owner_user) {
            $funding->owner_user->revertPrivatizeData();
        }

        if (empty($funding)) {
            throw new NotFoundException;
        }

        $this->set('uid', $funding->uid);

        $this->setReferer();

        if (!empty($this->request->getData())) {
            $associtions =  ['associated' => ['FundinguploadsActivityProofs', 'FundinguploadsFreistellungsbescheids']];
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
            'Fundingdatas',
            'FundinguploadsActivityProofs',
            'FundinguploadsFreistellungsbescheids',
            'Fundingbudgetplans',
        ]);

        // TODO Sorting not yet working
        $objects = $this->paginate($query, [
            'sortableFields' => [
                'Fundings.uid',
                'Fundings.created',
                'Fundings.modified',
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