<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\I18n\FrozenDate;

class UsersWorkshopsFixture extends TestFixture
{
    public $import = ['table' => 'users_workshops'];

    public function init(): void
    {
        $this->records = [
            [
                'user_uid' => '1',
                'workshop_uid' => '2',
                'approved' => new \Cake\I18n\Date('2019-09-19')
            ]
        ];
        parent::init();
    }

}
?>