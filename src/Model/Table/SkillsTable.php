<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SkillsTable extends Table
{

    public $allowedBasicHtmlFields = [];
    public $name_de = 'Kenntnis';

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsToMany('Users', [
            'through' => 'UsersSkills',
            'foreignKey' => 'skill_id',
            'targetForeignKey' => 'user_uid',
            'sort' => [
                'Users.nick' => 'ASC'
            ]
        ]);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        return $validator;
    }

    public function addSkills($request, $appAuth)
    {

        $skills = $request->getData('Users.skills._ids');
        if (empty($skills)) {
            return $request;
        }

        $skillsToAdd = [];
        foreach($skills as $key => $skill) {
            if (!is_numeric($skill)) {
                unset($skills[$key]);
                $skillsToAdd[] = $this->newEntity([
                    'name' => $skill,
                    'status' => $appAuth->isAdmin() ? APP_ON : APP_OFF,
                    'owner' => $appAuth->getUserUid()
                ]);
            }
        }
        $request = $request->withData('Users.skills._ids', $skills);

        $addedSkillIds = [];
        if (!empty($skillsToAdd)) {
            $addedSkills = $this->saveMany($skillsToAdd);
            foreach($addedSkills as $addedSkill) {
                $addedSkillIds[] = $addedSkill->id;
            }
            $request = $request->withData('Users.skills._ids', array_merge($skills, $addedSkillIds));
        }

        return $request;

    }

    /**
     * @return array
     */
    public function getForDropdown($includeOffline)
    {

        $conditions = [
            'Skills.status' => APP_ON
        ];
        if ($includeOffline) {
            $conditions = [
                'Skills.status >= ' => APP_OFF
            ];
        }
        $skills = $this->find('all', [
            'conditions' => $conditions,
            'fields' => [
                'Skills.id',
                'Skills.name'
            ],
            'order' => [
                'LOWER(Skills.name)' => 'ASC'
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