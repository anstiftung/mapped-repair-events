<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class GroupsTable extends Table
{

    public function isAdmin($user)
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

    public function isOrga($user)
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

    public function isRepairhelper($user)
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

    public function isInGroup($user, $groupNames) {
        if ( empty($user['groups']) ) return false;
        is_array($groupNames) or $groupNames = (array) $groupNames;
        foreach( $user['groups'] as $group ) {
            foreach ( $groupNames as $groupName ) {
                if ( $group['name'] == $groupName ) return true;
            }
        }
    }

}
?>