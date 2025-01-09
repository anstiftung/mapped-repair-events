<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Validation\Validator;

class KnowledgesTable extends AppTable
{

    public $name_de = 'Reparaturwissens-Beitrag';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('Blogs');
        $this->belongsToMany('Categories', [
            'joinTable' => 'knowledges_categories',
            'foreignKey' => 'knowledge_uid',
            'targetForeignKey' => 'category_id',
            'dependent' => true,
        ]);
        $this->belongsToMany('Skills', [
            'joinTable' => 'knowledges_skills',
            'foreignKey' => 'knowledge_uid',
            'targetForeignKey' => 'skill_id',
            'sort' => [
                'Skills.name' => 'ASC'
            ],
            'dependent' => true,
        ]);
    }

    public function validationAdmin(Validator $validator)
    {
        $validator->notEmptyString('title', 'Bitte gib einen Titel an.');
        $validator->minLength('title', 2, 'Bitte gib einen gültigen Titel an.');
        return $validator;
    }

}
?>