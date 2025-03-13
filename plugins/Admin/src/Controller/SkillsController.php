<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Model\Table\SkillsTable;
use Cake\Event\EventInterface;

class SkillsController extends AdminAppController
{
    
    public SkillsTable $Skill;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        // keep that because of AppController::stripTagsFromFields()
        $this->Skill = $this->getTableLocator()->get('Skills');
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
        $skillsTable = $this->getTableLocator()->get('Skills');
        $affectedCount = $skillsTable->setApprovedMultiple($selectedIds);
        $this->AppFlash->setFlashMessage($affectedCount . ' Kenntnisse erfolgreich bestätigt.');
        $this->redirect($this->getReferer());
    }    

    public function insert(): void
    {
        $skill = [
            'name' => 'Neue Kenntnis',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];
        $skillsTable = $this->getTableLocator()->get('Skills');
        $entity = $skillsTable->newEntity($skill);
        $skill = $skillsTable->save($entity);
        $this->AppFlash->setFlashMessage('Kenntnis erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function edit($id): void
    {

        if (empty($id)) {
            throw new NotFoundException;
        }

        $skillsTable = $this->getTableLocator()->get('Skills');
        $skill = $skillsTable->find('all', conditions: [
            'Skills.id' => $id,
            'Skills.status >= ' . APP_DELETED
        ])->first();

        if (empty($skill)) {
            throw new NotFoundException;
        }

        $this->set('id', $skill->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $skillsTable->patchEntity(
                $skill,
                $this->request->getData(),
                ['validate' => true]
            );

            if (!($patchedEntity->hasErrors())) {
                $this->saveObject($patchedEntity);
            } else {
                $skill = $patchedEntity;
            }
        }

        $this->set('skill', $skill);

        $metaTags = ['title' => 'Kenntnis bearbeiten'];
        $this->set('metaTags', $metaTags);

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Skills.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $skillsTable = $this->getTableLocator()->get('Skills');
        $query = $skillsTable->find('all',
        conditions: $conditions,
        contain: [
            'OwnerUsers',
        ],
        order: [
            'Skills.name' => 'ASC'
        ]);

        $objects = $this->paginate($query);

        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }

        $this->set('objects', $objects);

        $metaTags = [
            'title' => 'Kenntnisse'
        ];
        $this->set('metaTags', $metaTags);

    }

}