<?php

namespace App\Test\Fixture;

class GroupsFixture extends AppFixture
{
    public $import = ['table' => 'groups'];

    public array $records = [
        [
          'id' => '2',
          'name' => 'admin'
        ],
        [
          'id' => '7',
          'name' => 'repairhelper'
        ],
        [
          'id' => '9',
          'name' => 'orga'
        ],
    ];

}
?>