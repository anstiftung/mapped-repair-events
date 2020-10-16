<?php

namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Utility\Hash;

class UserLinkToWorkshopRule
{

    public $User;
    public $Group;
    public $Workshop;

    public function __invoke(EntityInterface $entity, array $options)
    {

        $userUid = $entity->get('uid');

        $this->User = FactoryLocator::get('Table')->get('Users');
        $user = $this->User->find('all', [
            'conditions' => [
                'Users.uid' => $userUid,
            ],
            'contain' => [
                'Groups',
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

        $this->Group = FactoryLocator::get('Table')->get('Groups');
        if (!$this->Group->isOrga($user)) {
            return true;
        }

        $this->Workshop = FactoryLocator::get('Table')->get('Workshops');
        $workshops = $this->Workshop->getWorkshopsForAssociatedUser($userUid, APP_DELETED);
        $associatedWorkshopsWhereUserIsLastOrgaUser = $this->User->getWorkshopsWhereUserIsLastOrgaUser($workshops);

        $errorMessage = 'Der User ist noch bei folgenden Initiativen als letzter Organisator zugeordnet: ';
        $errorMessage .= join(', ', Hash::extract($associatedWorkshopsWhereUserIsLastOrgaUser, '{n}.name'));
        return $errorMessage;

    }

}

?>