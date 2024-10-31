<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Event\EventInterface;
use App\Model\Entity\Funding;

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

    public function edit($id)
    {

        if (empty($id)) {
            throw new NotFoundException;
        }

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find('all',
        conditions: [
            $fundingsTable->aliasField('id') => $id,
        ],
        contain: [
            'Workshops',
        ])->first();

        if (empty($funding)) {
            throw new NotFoundException;
        }

        $this->set('id', $funding->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $fundingsTable->patchEntity($funding, $this->request->getData());
            if (!($patchedEntity->hasErrors())) {
                $fundingsTable->save($patchedEntity);
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

        $query = $fundingsTable->find('all',
        conditions: $this->conditions,
        contain: [
            'Workshops',
            'OwnerUsers',
        ]);

        $objects = $this->paginate($query, [
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

    public function activityProofDetail($fundingId) {

        $fundingsTable = $this->getTableLocator()->get('Fundings');
        $funding = $fundingsTable->find('all',
        conditions: [
            $fundingsTable->aliasField('id') => $fundingId,
            $fundingsTable->aliasField('activity_proof_filename IS NOT NULL'),
        ],
        contain: [
            'Workshops',
        ])->first();

        if (empty($funding)) {
            throw new NotFoundException;
        }

        $filePath = Funding::UPLOAD_PATH . $funding->id . DS . $funding->activity_proof_filename;
        $extension = strtolower(pathinfo($funding->activity_proof_filename, PATHINFO_EXTENSION));

        $this->request = $this->request->withParam('_ext', $extension);

        $response = $this->response->withType($extension);
        $response = $response->withStringBody(file_get_contents($filePath));
        return $response;

    }

}
?>