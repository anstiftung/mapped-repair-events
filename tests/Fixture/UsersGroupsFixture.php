<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersGroupsFixture extends TestFixture
{
    public $import = ['table' => 'users_groups', 'connection' => 'default'];
      
      public $records = [
          [
              'user_uid' => '1',
              'group_id' => '7'
          ],
      ];
      
 }
 ?>