<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Model\Table\SkillsTable;
use Cake\Event\EventInterface;
use Cake\Http\Response;

class SkillsController extends AdminAppController
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

        /** @var \App\Model\Table\SkillsTable */
        $skillsTable = $this->getTableLocator()->get('Skills');
        $affectedCount = $skillsTable->setApprovedMultiple($selectedIds);
        $this->AppFlash->setFlashMessage($affectedCount . ' Kenntnisse erfolgreich bestätigt.');
        return $this->redirect($this->getReferer());
    }

    public function insert(): Response
    {
        $skill = [
            'name' => 'Neue Kenntnis',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];

        /** @var \App\Model\Table\SkillsTable */
        $skillsTable = $this->getTableLocator()->get('Skills');
        $entity = $skillsTable->newEntity($skill);
        $skill = $skillsTable->save($entity);
        $this->AppFlash->setFlashMessage('Kenntnis erfolgreich erstellt.');
        return $this->redirect($this->getReferer());
    }

    public function edit(int $id): ?Response
    {
        /** @var \App\Model\Table\SkillsTable */
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
                return $this->saveObject($patchedEntity);
            } else {
                $skill = $patchedEntity;
            }
        }

        $this->set('skill', $skill);

        $metaTags = ['title' => 'Kenntnis bearbeiten'];
        $this->set('metaTags', $metaTags);
        return null;
    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Skills.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        /** @var \App\Model\Table\SkillsTable */
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