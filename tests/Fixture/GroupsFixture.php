<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class GroupsFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
              'id' => '2',
              'name' => 'admin',
            ],
            [
              'id' => '7',
              'name' => 'repairhelper',
            ],
            [
              'id' => '9',
              'name' => 'orga',
            ],
        ];
        parent::init();
    }

}
?>