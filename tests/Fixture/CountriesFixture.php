<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CountriesFixture extends TestFixture
{
    public $import = ['table' => 'countries'];

      public array $records = [
          [
              'code' => 'DE',
              'name_de' => 'Deutschland'
          ]
      ];
}
?>