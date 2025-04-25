<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\Component\StringComponent;
use App\Model\Table\CategoriesTable;
use App\Model\Table\SkillsTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

class SkillsController extends AppController
{

    public CategoriesTable $Category;
    public SkillsTable $Skill;
    
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'all',
            'detail'
        ]);
    }

    public function all(): void
    {
        $metaTags = [
            'title' => 'Kenntnisse & Interessen',
            'description' => '',
            'keywords' => ''
        ];
        $this->set('metaTags', $metaTags);

        $this->Skill = $this->getTableLocator()->get('Skills');
        $skills = $this->Skill->find('all',
        order: [
            'Skills.name'=> 'ASC'
        ],
        conditions: [
            'Skills.status' => APP_ON
        ],
        contain: [
            'Users' => [
                'conditions' => [
                    'FIND_IN_SET("skills", private) = ' => 0, // Users.private would be converted to users.private on prod so leave it out!
                    'Users.status' => APP_ON
                ]
            ]
        ]);

        $this->Category = $this->getTableLocator()->get('Categories');
        $categories = $this->Category->find('all',
        order: [
            'Categories.name'=> 'ASC'
        ],
        conditions: [
            'Categories.parent_id IS NULL',
            'Categories.status' => APP_ON,
            'Categories.visible_on_platform' => APP_ON,
        ],
        contain: [
            'Users' => [
                'conditions' => [
                    'FIND_IN_SET("categories", private) = ' => 0, // Users.private would be converted to users.private on prod so leave it out!
                    'Users.status' => APP_ON,
                ]
            ]
        ]);

        $preparedSkills = [];
        $skillCount = 0;
        foreach($categories as $category) {
            if (count($category->users) > 0) {
                $category->url = Configure::read('AppConfig.htmlHelper')->urlUsers($category->name);
                $preparedSkills[strtoupper(substr((string) $category->name, 0, 1))][] = $category;
                $skillCount++;
            }
        }

        foreach($skills as $skill) {
            if (count($skill->users) > 0) {
                $skill->url = Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skill->id, StringComponent::slugify($skill->name));
                $preparedSkills[strtoupper(substr((string) $skill->name, 0, 1))][] = $skill;
                $skillCount++;
            }
        }

        foreach($preparedSkills as &$preparedSkill) {
            $preparedSkill = Hash::sort($preparedSkill, '{n}.name');
        }
        ksort($preparedSkills, SORT_STRING);

        $this->set('skills', $preparedSkills);
        $this->set('skillCount', $skillCount);
    }
}
?>