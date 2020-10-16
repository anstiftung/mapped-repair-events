<?php

namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;

class UserIsWorkshopOwnerRule
{

    public $User;

    public function __invoke(EntityInterface $entity, array $options)
    {

        $this->User = FactoryLocator::get('Table')->get('Users');
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $entity->get('uid'),
            ],
            'contain' => [
                'OwnerWorkshops' => [
                    'conditions' => [
                        'OwnerWorkshops.status > ' . APP_DELETED,
                    ]
                ]
            ],
        ])->first();

        if (empty($user->owner_workshops)) {
            return true;
        }

        $errorMessage = 'Der User ist bei folgenden Initiativen als Owner zugeordnet: ';
        $errorMessage .= join(', ', Hash::extract($user->owner_workshops, '{n}.name'));
        return $errorMessage;

    }

}

?>