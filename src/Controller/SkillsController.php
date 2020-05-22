<?php
namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

class SkillsController extends AppController
{

    public function beforeFilter(EventInterface $event)
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
                    'conditions' => [
                        'FIND_IN_SET("skills", private) = ' => 0, // Users.private would be converted to users.private on prod so leave it out!
                        'Users.status' => APP_ON
                    ]
                ]
            ]
        ]);

        $preparedSkills = [];
        $skillCount = 0;
        foreach($skills as $skill) {
            if (count($skill->users) > 0) {
                $preparedSkills[strtoupper(substr($skill->name, 0, 1))][] = $skill;
                $skillCount++;
            }
        }
        $this->set('skills', $preparedSkills);
        $this->set('skillCount', $skillCount);
    }
}
?>