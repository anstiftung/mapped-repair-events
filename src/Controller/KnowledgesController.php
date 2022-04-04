<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\ORM\Query;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

class KnowledgesController extends AppController
{

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->Knowledge = $this->getTableLocator()->get('Knowledges');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->AppAuth->allow([
            'all',
        ]);
    }

    public function all()
    {

        // pass[0] can contain "44-tag-name" or "category-name"
        $filteredCategory = null;
        if (isset($this->getRequest()->getParam('pass')[0])) {
            $this->Category = $this->getTableLocator()->get('Categories');
            $categories = $this->Category->getMainCategoriesForFrontend();
            $categorySlug = $this->getRequest()->getParam('pass')[0];
            foreach($categories as $category) {
                if (StringComponent::slugify($category->name) == $categorySlug) {
                    $filteredCategory = $category;
                }
            }
        }
        $this->set('filteredCategoryIcon', !is_null($filteredCategory) ? $filteredCategory->icon : null);
        $this->set('filteredCategoryName', !is_null($filteredCategory) ? $filteredCategory->name : null);

        $skillId = 0;
        if (is_null($filteredCategory) && isset($this->getRequest()->getParam('pass')[0])) {
            $skillId = (int) $this->getRequest()->getParam('pass')[0];
            $this->Skill = $this->getTableLocator()->get('Skills');
            $skill = $this->Skill->find('all', [
                'conditions' => [
                    'Skills.id' => $skillId,
                    'Skills.status' => APP_ON
                ]
            ])->first();

            if (empty($skill)) {
                throw new NotFoundException('skill not found');
            }

            $this->set('skill', $skill);
        }

        $conditions = [
            'Knowledges.status' => APP_ON
        ];

        $knowledges = $this->Knowledge->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'Skills',
                'Categories',
            ]
        ]);

        if ($skillId > 0) {
            $knowledges->matching('Skills', function(Query $q) use ($skillId) {
                return $q->where(['Skills.id' => $skillId]);
            });
        }

        if (!is_null($filteredCategory)) {
            $knowledges->matching('Categories', function(Query $q) use ($filteredCategory) {
                return $q->where(['Categories.id' => $filteredCategory->id]);
            });
        }

        $knowledges = $this->paginate($knowledges, [
            'order' => [
                'Knowledges.title' => 'ASC',
            ],
        ]);
        $this->set('knowledges', $knowledges);

        /*
        if ($skillId > 0) {
            $correctSlug = Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skillId, $skill->name);
            if ($correctSlug != Configure::read('AppConfig.htmlHelper')->urlSkillDetail($skillId, StringComponent::removeIdFromSlug($this->getRequest()->getParam('pass')[0]))) {
                $this->redirect($correctSlug);
                return;
            }
        }
        */

        $this->Skill = $this->getTableLocator()->get('Skills');
        $skillsForDropdown = $this->Skill->getForDropdownIncludingCategories(false);
        $this->set('skillsForDropdown', $skillsForDropdown);

        $metaTags = [
            'title' => 'Reparaturwissen'
        ];
        $this->set('metaTags', $metaTags);

        $overviewLink = Configure::read('AppConfig.htmlHelper')->urlKnowledges();
        /*
        if (preg_match('`/kenntnisse`', $this->referer())) {
            $overviewLink = Configure::read('AppConfig.htmlHelper')->urlSkills();
        }
        */
        $this->set('overviewLink', $overviewLink);
    }

}
