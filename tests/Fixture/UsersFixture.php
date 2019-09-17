<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public $import = ['table' => 'users', 'connection' => 'default'];
      
      public $records = [
          [
              'uid' => 1,
              'firstname' => 'John',
              'lastname' => 'Doe',
              'email' => 'johndoe@example.com',
              'status' => APP_ON,
              'private' => 'lastname',
              'created' => '2019-09-17 08:23:23',
              'modified' => '2019-09-17 08:23:23'
          ]
      ];
 }
 ?>