<?php
namespace App\Controller;

use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class SkillsController extends AppController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'all',
            'detail'
        ]);
    }
    
    public function all()
    {
        $metaTags = [
            'title' => 'Kenntnisse & Interessen',
            'description' => '',
            'keywords' => ''
        ];
        $this->set('metaTags', $metaTags);
        
        $this->Skill = TableRegistry::getTableLocator()->get('Skills');
        $skills = $this->Skill->find('all', [
            'order' => [
                'Skills.name'=> 'ASC'
            ],
            'conditions' => [
                'Skills.status' => APP_ON
            ],
            'contain' => [
                'Users' => [
                    'fields' => [
                        'UsersSkills.skill_id',
                    ],
                    'conditions' => [
                        'Users.status' => APP_ON
                    ]
                ]
            ]
        ]);
        
        $preparedSkills = [];
        foreach($skills as $skill) {
            $preparedSkills[strtoupper(substr($skill->name, 0, 1))][] = $skill;
        }
        $this->set('skills', $preparedSkills);
    }
}
?>