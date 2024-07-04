<?php

namespace App\Test\Fixture;

class UsersGroupsFixture extends AppFixture
{
    public $import = ['table' => 'users_groups'];

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