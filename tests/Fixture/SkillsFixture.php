<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class SkillsFixture extends TestFixture
{
    public $import = ['table' => 'skills', 'connection' => 'default'];

      public $records = [
          [
              'name' => 'Open Source'
          ]
      ];
}
?>