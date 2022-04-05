<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;

class KnowledgesController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Knowledge = $this->getTableLocator()->get('Knowledges');
    }

    public function insert()
    {
        $knowledge = [
            'name' => 'Neuer Reparaturwissens-Beitrag von ' . $this->AppAuth->getUserName(),
        ];
        $entity = $this->Knowledge->newEntity($knowledge);
        $knowledge = $this->Knowledge->save($entity);
        $this->AppFlash->setFlashMessage('Knowledge erfolgreich erstellt. UID: ' . $knowledge->uid); // uid for fixture
        $this->redirect($this->getReferer());
    }

    public function edit($uid)
    {

        if (empty($uid)) {
            throw new NotFoundException;
        }

        $knowledge = $this->Knowledge->find('all', [
            'conditions' => [
                'Knowledges.uid' => $uid,
                'Knowledges.status >= ' . APP_DELETED
            ],
            'contain' => [
                'Categories',
                'Skills',
            ]
        ])->first();

        if (empty($knowledge)) {
            throw new NotFoundException;
        }

        $this->set('uid', $uid);

        $this->setReferer();
        $this->setIsCurrentlyUpdated($uid);

        if (!empty($this->request->getData())) {

            $this->Skill = $this->getTableLocator()->get('Skills');
            $this->request = $this->Skill->addSkills($this->request, $this->AppAuth, 'Knowledges');

            $patchedEntity = $this->Knowledge->getPatchedEntityForAdminEdit($knowledge, $this->request->getData(), $this->useDefaultValidation);

            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $this->saveObject($patchedEntity, $this->useDefaultValidation);
            } else {
                $knowledge = $patchedEntity;
            }
        }

        $this->set('knowledge', $knowledge);

        $this->Category = $this->getTableLocator()->get('Categories');
        $this->set('categories', $this->Category->getForDropdown(APP_ON));

        $this->Skill = $this->getTableLocator()->get('Skills');
        $skillsForDropdown = $this->Skill->getForDropdown(false);
        $this->set('skillsForDropdown', $skillsForDropdown);

    }

    public function index()
    {
        parent::index();

        $conditions = [
            'Knowledges.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        $query = $this->Knowledge->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Categories',
                'OwnerUsers',
                'Skills',
            ]
        ]);

        $objects = $this->paginate($query, [
            'order' => [
                'Knowledges.uid' => 'ASC'
            ]
        ]);
        foreach($objects as $object) {
            if ($object->owner_user) {
                $object->owner_user->revertPrivatizeData();
            }
        }
        $this->set('objects', $objects->toArray());

        $metaTags = [
            'title' => 'Reparaturwissen'
        ];
        $this->set('metaTags', $metaTags);

    }
}
