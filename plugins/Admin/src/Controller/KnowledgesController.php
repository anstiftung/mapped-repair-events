<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

class KnowledgesController extends AdminAppController
{

    public function insert(): Response
    {
        $knowledge = [
            'name' => 'Neuer Reparaturwissens-Beitrag von ' . $this->loggedUser->name,
        ];

        /** @var \App\Model\Table\KnowledgesTable */
        $knowledgesTable = $this->getTableLocator()->get('Knowledges');
        $entity = $knowledgesTable->newEntity($knowledge);
        $knowledge = $knowledgesTable->save($entity);
        $this->AppFlash->setFlashMessage('Knowledge erfolgreich erstellt. UID: ' . $knowledge->uid); // uid for fixture
        return $this->redirect($this->getReferer());
    }

    public function edit(int $uid): ?Response
    {
        /** @var \App\Model\Table\KnowledgesTable */
        $knowledgesTable = $this->getTableLocator()->get('Knowledges');
        $knowledge = $knowledgesTable->find('all',
            conditions: [
                'Knowledges.uid' => $uid,
                'Knowledges.status >= ' . APP_DELETED
            ],
            contain: [
                'Categories',
                'Skills',
            ])->first();

        if (empty($knowledge)) {
            throw new NotFoundException;
        }

        $this->set('uid', $uid);

        $this->setReferer();
        $this->setIsCurrentlyUpdated($uid);

        /** @var \App\Model\Table\SkillsTable */
        $skillsTable = $this->getTableLocator()->get('Skills');

        if (!empty($this->request->getData())) {

            $associatedSkills = $this->request->getData('Knowledges.skills._ids');
            $newSkills = $skillsTable->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $skillsTable->getExistingSkillsFromRequest($associatedSkills);
            $this->request->getSession()->write('newSkillsKnowledges', $newSkills);
            $this->request = $this->request->withData('Knowledges.skills._ids', $existingSkills);

            $knowledgesTable = $this->getTableLocator()->get('Knowledges');
            $patchedEntity = $knowledgesTable->getPatchedEntityForAdminEdit($knowledge, $this->request->getData());

            if (!($patchedEntity->hasErrors())) {
                $patchedEntity = $this->patchEntityWithCurrentlyUpdatedFields($patchedEntity);
                $this->saveObject($patchedEntity);

                $newSkills = $this->request->getSession()->read('newSkillsKnowledges');
                if (!empty($newSkills)) {
                    // save new skills
                    $addedSkillIds = $skillsTable->addSkills($newSkills, $this->loggedUser->isAdmin(), $this->loggedUser->uid);
                    // save id associations to knowledge
                    $this->request = $this->request->withData('Knowledges.skills._ids', array_merge($this->request->getData('Knowledges.skills._ids'), $addedSkillIds));
                    $patchedEntity = $knowledgesTable->getPatchedEntityForAdminEdit($knowledge, $this->request->getData());
                    $this->request->getSession()->delete('newSkillsKnowledges');
                    return $this->saveObject($patchedEntity);
                }

            } else {
                $knowledge = $patchedEntity;
            }
        } else {
            $this->request->getSession()->delete('newSkillsKnowledges');
        }

        $this->set('knowledge', $knowledge);

        /** @var \App\Model\Table\CategoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $this->set('categories', $categoriesTable->getForDropdown([APP_ON]));

        /** @var \App\Model\Table\SkillsTable */
        $skillsForDropdown = $skillsTable->getForDropdown(false);
        $this->set('skillsForDropdown', $skillsForDropdown);
        return null;

    }

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Knowledges.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

        /** @var \App\Model\Table\KnowledgesTable */
        $knowledgesTable = $this->getTableLocator()->get('Knowledges');
        $query = $knowledgesTable->find('all',
            conditions: $conditions,
            contain: [
                'Categories',
                'OwnerUsers',
                'Skills',
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
