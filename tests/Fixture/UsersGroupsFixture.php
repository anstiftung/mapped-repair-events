<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersGroupsFixture extends TestFixture
{
    public $import = ['table' => 'users_groups', 'connection' => 'default'];

      public array $records = [
          [
              'user_uid' => 1,
              'group_id' => GROUPS_ORGA,
          ],
          [
              'user_uid' => 3,
              'group_id' => GROUPS_REPAIRHELPER,
          ],
          [
              'user_uid' => 8,
              'group_id' => GROUPS_ADMIN,
          ],
      ];

}
?>