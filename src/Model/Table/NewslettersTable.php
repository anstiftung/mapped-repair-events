<?php

namespace App\Model\Table;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class NewslettersTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator->email('email', true, 'Bitte trage eine gültige E-Mail-Adresse ein.');
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