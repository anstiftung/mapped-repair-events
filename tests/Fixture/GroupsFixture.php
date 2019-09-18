<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class GroupsFixture extends TestFixture
{
    public $import = ['table' => 'groups', 'connection' => 'default'];
      
      public $records = [
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
          [
              'id' => '10',
              'name' => 'forum_moderator'
          ],
      ];
      
}
?>