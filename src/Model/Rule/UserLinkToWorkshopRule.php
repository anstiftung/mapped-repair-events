<?php

namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;

class UserLinkToWorkshopRule
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
                'Workshops' => [
                    'conditions' => [
                        'Workshops.status > ' . APP_DELETED,
                    ]
                ]
            ],
        ])->first();

        if (empty($user->workshops)) {
            return true;
        }

        $errorMessage = 'Der User ist noch bei folgenden Initiativen als Mitarbeiter zugeordnet: ';
        $errorMessage .= join(', ', Hash::extract($user->workshops, '{n}.name'));
        return $errorMessage;

    }

}

?>