<?php

namespace App\Controller;

use App\Controller\Component\StringComponent;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

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
        $this->Authentication->allowUnauthenticated([
            'all',
        ]);
    }

    public function all()
    {

        $knowledges = $this->Knowledge->find('all', [
            'conditions' => [
                'Knowledges.status' => APP_ON
            ],
            'contain' => [
                'Skills',
                'Categories',
            ],
            'order' => [
                'Knowledges.title' => 'ASC',
            ],
        ])->toArray();

        foreach($knowledges as &$knowledge) {
            $categoryNames = Hash::extract($knowledge, 'categories.{n}.name');
            $itemSkillClasses = [];
            foreach($categoryNames as $categoryName) {
                $itemSkillClasses[] = StringComponent::slugify($categoryName);
            }
            $skills = Hash::extract($knowledge, 'skills.{n}.id');
            $itemSkillClasses = array_merge($itemSkillClasses, $skills);
            $knowledge->itemSkillClasses = $itemSkillClasses;
        }
        $this->set('knowledges', $knowledges);

        $skillsForDropdown = [];

        $categories = Hash::extract($knowledges, '{n}.categories.{n}');
        foreach($categories as $category) {
            $categorySlug = StringComponent::slugify($category['name']);
            if (!isset($skillsForDropdown[$categorySlug])) {
                $skillsForDropdown[$categorySlug] = $category['name'];
            }
        }

        $skills = Hash::extract($knowledges, '{n}.skills.{n}');
        foreach($skills as $skill) {
            if (!isset($skillsForDropdown[$skill['id']])) {
                $skillsForDropdown[$skill['id']] = $skill['name'];
            }
        }
        asort($skillsForDropdown);
        $this->set('skillsForDropdown', $skillsForDropdown);

        $metaTags = [
            'title' => 'Reparaturwissen'
        ];
        $this->set('metaTags', $metaTags);

    }

}
