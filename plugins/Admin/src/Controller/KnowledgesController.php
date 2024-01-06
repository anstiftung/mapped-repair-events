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
            'name' => 'Neuer Reparaturwissens-Beitrag von ' . $this->loggedUser->name,
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

        $this->Skill = $this->getTableLocator()->get('Skills');

        if (!empty($this->request->getData())) {

            $associatedSkills = $this->request->getData('Knowledges.skills._ids');
            $newSkills = $this->Skill->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $this->Skill->getExistingSkillsFromRequest($associatedSkills);
            $this->request->getSession()->write('newSkillsKnowledges', $newSkills);
            $this->request = $this->request->withData('Knowledges.skills._ids', $existingSkills);

            $patchedEntity = $this->Knowledge->getPatchedEntityForAdminEdit($knowledge, $this->request->getData());

            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $this->saveObject($patchedEntity);

                $newSkills = $this->request->getSession()->read('newSkillsKnowledges');
                if (!empty($newSkills)) {
                    // save new skills
                    $addedSkillIds = $this->Skill->addSkills($newSkills, $this->loggedUser->isAdmin(), $this->loggedUser->uid);
                    // save id associations to knowledge
                    $this->request = $this->request->withData('Knowledges.skills._ids', array_merge($this->request->getData('Knowledges.skills._ids'), $addedSkillIds));
                    $patchedEntity = $this->Knowledge->getPatchedEntityForAdminEdit($knowledge, $this->request->getData());
                    $this->saveObject($patchedEntity);
                    $this->request->getSession()->delete('newSkillsKnowledges');
                }

            } else {
                $knowledge = $patchedEntity;
            }
        } else {
            $this->request->getSession()->delete('newSkillsKnowledges');
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
        $this->set('objects', $objects);

        $metaTags = [
            'title' => 'Reparaturwissen'
        ];
        $this->set('metaTags', $metaTags);

    }
}
