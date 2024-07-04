<?php

namespace App\Test\Fixture;

class UsersSkillsFixture extends AppFixture
{
    public $import = ['table' => 'users_skills'];

      public array $records = [
          [
              'user_uid' => '1',
              'skill_id' => '1'
          ],
      ];

}
?>