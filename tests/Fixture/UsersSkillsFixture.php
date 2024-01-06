<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersSkillsFixture extends TestFixture
{
    public $import = ['table' => 'users_skills', 'connection' => 'default'];

      public array $records = [
          [
              'user_uid' => '1',
              'skill_id' => '1'
          ],
      ];

}
?>