<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;

class SkillsController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Skill = $this->getTableLocator()->get('Skills');
    }

    public function insert()
    {
        $skill = [
            'name' => 'Neue Kenntnis',
            'owner' => $this->isLoggedIn() ? $this->loggedUser->uid : 0,
            'status' => APP_OFF
        ];
        $entity = $this->Skill->newEntity($skill);
        $skill = $this->Skill->save($entity);
        $this->AppFlash->setFlashMessage('Kenntnis erfolgreich erstellt.');
        $this->redirect($this->getReferer());
    }

    public function edit($id)
    {

        if (empty($id)) {
            throw new NotFoundException;
        }

        $skill = $this->Skill->find('all', [
            'conditions' => [
                'Skills.id' => $id,
                'Skills.status >= ' . APP_DELETED
            ]
        ])->first();

        if (empty($skill)) {
            throw new NotFoundException;
        }

        $this->set('id', $skill->id);

        $this->setReferer();

        if (!empty($this->request->getData())) {

            $patchedEntity = $this->Skill->patchEntity(
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

    public function index()
    {
        parent::index();

        $conditions = [
            'Skills.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->Skill->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'OwnerUsers',
            ],
            'order' => [
                'Skills.name' => 'ASC'
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
            'title' => 'Kenntnisse'
        ];
        $this->set('metaTags', $metaTags);

    }

}