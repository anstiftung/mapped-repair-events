<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SkillsTable extends Table
{

    public $allowedBasicHtmlFields = [];
    public $name_de = 'Kenntnis';
    
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        return $validator;
    }
    
    /**
     * @return array
     */
    public function getForSubcategoryDropdown()
    {
        
        $skills = $this->find('all', [
            'conditions' => [
                'Skills.status > ' . APP_DELETED
            ],
            'order' => [
                'Skills.status' => 'DESC'
            ]
        ]);
        foreach($skills as &$skill) {
            if ($skill->status == APP_OFF) {
                $skill->name .= ' (unbestätigt)';
            }
        }
        
        return $skills;
        
    }
    
    /**
     * @return array
     */
    public function getForDropdown()
    {
        
        $skills = $this->find('all', [
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