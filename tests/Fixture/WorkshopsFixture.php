<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class WorkshopsFixture extends TestFixture
{
    public $import = ['table' => 'workshops', 'connection' => 'default'];
      
      public $records = [
          [
              'uid' => 2,
              'name' => 'Test Workshop',
              'url' => 'test-workshop',
              'status' => APP_ON
          ]
      ];
      
 }
 ?>