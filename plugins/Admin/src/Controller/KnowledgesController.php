<?php
declare(strict_types=1);
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use App\Model\Table\CategoriesTable;
use App\Model\Table\KnowledgesTable;
use App\Model\Table\SkillsTable;

class KnowledgesController extends AdminAppController
{

    public CategoriesTable $Category;
    public SkillsTable $Skill;
    public KnowledgesTable $Knowledge;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        // keep that because of AppController::stripTagsFromFields()
        $this->Knowledge = $this->getTableLocator()->get('Skills');
    }


    public function insert(): void
    {
        $knowledge = [
            'name' => 'Neuer Reparaturwissens-Beitrag von ' . $this->loggedUser->name,
        ];
        $knowledgesTable = $this->getTableLocator()->get('Knowledges');
        $entity = $knowledgesTable->newEntity($knowledge);
        $knowledge = $knowledgesTable->save($entity);
        $this->AppFlash->setFlashMessage('Knowledge erfolgreich erstellt. UID: ' . $knowledge->uid); // uid for fixture
        $this->redirect($this->getReferer());
    }

    public function edit($uid): void
    {

        if (empty($uid)) {
            throw new NotFoundException;
        }

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

        $this->Skill = $this->getTableLocator()->get('Skills');

        if (!empty($this->request->getData())) {

            $associatedSkills = $this->request->getData('Knowledges.skills._ids');
            $newSkills = $this->Skill->getNewSkillsFromRequest($associatedSkills);
            $existingSkills = $this->Skill->getExistingSkillsFromRequest($associatedSkills);
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
                    $addedSkillIds = $this->Skill->addSkills($newSkills, $this->loggedUser->isAdmin(), $this->loggedUser->uid);
                    // save id associations to knowledge
                    $this->request = $this->request->withData('Knowledges.skills._ids', array_merge($this->request->getData('Knowledges.skills._ids'), $addedSkillIds));
                    $patchedEntity = $knowledgesTable->getPatchedEntityForAdminEdit($knowledge, $this->request->getData());
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

    public function index(): void
    {
        parent::index();

        $conditions = [
            'Knowledges.status > ' . APP_DELETED
        ];
        $conditions = array_merge($this->conditions, $conditions);

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
