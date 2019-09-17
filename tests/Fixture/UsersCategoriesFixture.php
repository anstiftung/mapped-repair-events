<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersCategoriesFixture extends TestFixture
{
    public $import = ['table' => 'users_categories', 'connection' => 'default'];
      
      public $records = [
          [
              'user_uid' => '2',
              'category_id' => '87'
          ],
      ];
      
 }
 ?>