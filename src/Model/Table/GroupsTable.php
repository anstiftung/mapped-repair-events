<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use App\Model\Entity\User;

class GroupsTable extends Table
{

    public function isAdmin(User $user): bool
    {
        if (empty($user['groups']))
            return false;
        foreach ($user['groups'] as $group) {
            if ($group['id'] == GROUPS_ADMIN) {
                return true;
            }
        }
        return false;
    }

    public function isOrga(User $user): bool
    {
        if (empty($user['groups']))
            return false;
        foreach ($user['groups'] as $group) {
            if ($group['id'] == GROUPS_ORGA) {
                return true;
            }
        }
        return false;
    }

    public function isRepairhelper(User $user): bool
    {
        if (empty($user['groups']))
            return false;
            foreach ($user['groups'] as $group) {
                if ($group['id'] == GROUPS_REPAIRHELPER) {
                    return true;
                }
            }
            return false;
    }

}
?>