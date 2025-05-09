<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class UsersGroupsFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
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
        parent::init();
    }

}
?>