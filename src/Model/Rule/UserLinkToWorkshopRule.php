<?php
declare(strict_types=1);

namespace App\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class UserLinkToWorkshopRule
{

    /**
     * @param array<string, string> $options
     */
    public function __invoke(EntityInterface $entity, array $options): bool|string
    {

        $userUid = $entity->get('uid');

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->find('all',
            conditions: [
                'Users.uid' => $userUid,
            ],
            contain: [
                'Groups',
                'Workshops' => [
                    'conditions' => [
                        'Workshops.status > ' . APP_DELETED,
                    ],
                ],
            ],
        )->first();

        if (empty($user->workshops)) {
            return true;
        }

        $groupsTable = TableRegistry::getTableLocator()->get('Groups');
        if (!$groupsTable->isOrga($user)) {
            return true;
        }

        $workshopsTable = TableRegistry::getTableLocator()->get('Workshops');
        $workshops = $workshopsTable->getWorkshopsForAssociatedUser($userUid, APP_DELETED);
        $associatedWorkshopsWhereUserIsLastOrgaUser = $usersTable->getWorkshopsWhereUserIsLastOrgaUser($workshops);

        if (empty($associatedWorkshopsWhereUserIsLastOrgaUser)) {
            return true;
        }
        
        $errorMessage = 'Der User ist bei folgenden Initiativen als letzte(r) Organisator*in zugeordnet: ';
        $errorMessage .= join(', ', Hash::extract($associatedWorkshopsWhereUserIsLastOrgaUser, '{n}.name'));
        return $errorMessage;

    }

}

?>