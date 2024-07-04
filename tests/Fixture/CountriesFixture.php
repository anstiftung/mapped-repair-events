<?php

namespace App\Test\Fixture;

class CountriesFixture extends AppFixture
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