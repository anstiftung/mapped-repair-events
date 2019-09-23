<?php

namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class NewslettersTable extends Table
{
 
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setPrimaryKey('id');
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator->email('email', false, 'Bitte trage eine gültige E-Mail ein.');
        $validator->notEmptyString('email', 'Bitte trage deine E-Mail-Adresse ein.');
        $validator->add('email', 'unique', [
            'rule' => 'validateUnique',
            'provider' => 'table',
            'message' => 'Diese E-Mail-Adresse wird bereits verwendet.'
        ]);
        $validator->allowEmptyString('plz');
        $validator->add('plz', 'validFormat', [
            'rule' => ['custom', ZIP_REGEX],
            'message' => 'Die PLZ ist nicht gültig.'
        ]);
        return $validator;
    }
    
}
?>