<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersWorkshopsFixture extends TestFixture
{
    public $import = ['table' => 'users_workshops', 'connection' => 'default'];
      
      public $records = [
          [
              'user_uid' => '1',
              'workshop_uid' => '2'
          ],
      ];
      
 }
 ?>