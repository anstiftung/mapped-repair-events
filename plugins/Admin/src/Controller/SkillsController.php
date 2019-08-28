<?php
namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class SkillsController extends AdminAppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Skill = TableRegistry::getTableLocator()->get('Skills');
    }
    
    public function insert()
    {
        $skill = [
            'name' => 'Neue Kenntnis',
            'owner' => $this->AppAuth->getUserUid(),
            'status' => APP_OFF
        ];
        $entity = $this->Skill->newEntity($skill);
        $skill = $this->Skill->save($entity);
        $this->AppFlash->setFlashMessage('Kenntnis erfolgreich erstellt.');
        $this->redirect($this->referer());
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
        
        $this->set('objects', $objects->toArray());
        
        $metaTags = [
            'title' => 'Kenntnisse'
        ];
        $this->set('metaTags', $metaTags);
        
    }
    
}