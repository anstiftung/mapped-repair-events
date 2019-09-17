<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CategoriesFixture extends TestFixture
{
    public $import = ['table' => 'categories', 'connection' => 'default'];
      
      public $records = [
          [
              'id' => '87',
              'name' => 'Elektro Sonstiges',
              'icon' => 'elektro',
              'status' => APP_ON
          ]
      ];
      
 }
 ?>