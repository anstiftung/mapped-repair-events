<?php
namespace App\Model\Table;

use App\Controller\Component\StringComponent;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Datasource\FactoryLocator;

class SkillsTable extends Table
{

    public $allowedBasicHtmlFields = [];
    public $name_de = 'Kenntnis';
    private $Category;

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsToMany('Users', [
            'through' => 'UsersSkills',
            'foreignKey' => 'skill_id',
            'targetForeignKey' => 'user_uid',
            'sort' => [
                'Users.nick' => 'ASC',
            ]
        ]);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner',
        ]);
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        return $validator;
    }

    public function getNewSkillsFromRequest($associatedSkills) {
        if ($associatedSkills === null) {
            return [];
        }
        $skills = array_filter($associatedSkills, function($value) {
            return !is_numeric($value);
        });
        return $skills;
    }

    public function getExistingSkillsFromRequest($associatedSkills) {
        if ($associatedSkills === null) {
            return [];
        }
        $skills = array_filter($associatedSkills, function($value) {
            return is_numeric($value);
        });
        return $skills;
    }

    public function addSkills($newSkills, $loggedUser, $entity)
    {

        $skillsToAdd = [];
        foreach($newSkills as $skill) {
            $preparedSkill = strip_tags($skill);
            $skillsToAdd[] = $this->newEntity([
                'name' => $preparedSkill,
                'status' => !empty($loggedUser) && $loggedUser->isAdmin() ? APP_ON : APP_OFF,
                'owner' => !empty($loggedUser) ? $loggedUser->uid : 0,
            ]);
        }

        $addedSkillIds = [];
        if (!empty($skillsToAdd)) {
            $addedSkills = $this->saveMany($skillsToAdd);
            foreach($addedSkills as $addedSkill) {
                $addedSkillIds[] = $addedSkill->id;
            }
        }

        return $addedSkillIds;

    }

    public function getForDropdownIncludingCategories($includeOffline): array
    {
        $skillsForDropdown = $this->getForDropdown(false);
        $this->Category = FactoryLocator::get('Table')->get('Categories');
        $categoriesForDropdown = $this->Category->getMainCategoriesForFrontend();
        $preparedCategoriesForDropdown = [];
        foreach($categoriesForDropdown as $c) {
            $slugifiedCategoryName = StringComponent::slugify($c->name);
            $preparedCategoriesForDropdown[$slugifiedCategoryName] = $c->name;
        }
        $skillsForDropdown = $preparedCategoriesForDropdown + $skillsForDropdown;
        asort($skillsForDropdown);
        return $skillsForDropdown;
    }

    public function getForDropdown($includeOffline): array
    {

        $conditions = [
            'Skills.status' => APP_ON,
        ];
        if ($includeOffline) {
            $conditions = [
                'Skills.status >= ' => APP_OFF,
            ];
        }
        $skills = $this->find('all', [
            'conditions' => $conditions,
            'fields' => [
                'Skills.id',
                'Skills.name',
            ],
            'order' => [
                'LOWER(Skills.name)' => 'ASC',
            ]
        ]);

        $preparedSkills = [];
        foreach($skills as $skill) {
            $preparedSkills[$skill->id] = $skill->name;
        }

        return $preparedSkills;
    }

}

?>