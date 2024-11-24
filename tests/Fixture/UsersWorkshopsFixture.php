<?php

namespace App\Test\Fixture;

use Cake\I18n\Date;

class UsersWorkshopsFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'user_uid' => '1',
                'workshop_uid' => '2',
                'approved' => new Date('2019-09-19')
            ]
        ];
        parent::init();
    }

}
?>