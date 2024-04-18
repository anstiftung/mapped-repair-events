<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use App\Model\Entity\Worknews;

class WorknewsFixture extends TestFixture
{
    public $import = ['table' => 'worknews'];

    public array $records = [
        [
            'workshop_uid' => 2,
            'email' => 'worknews-test@mailinator.com',
            'confirm' => Worknews::STATUS_OK,
        ]
    ];

}
?>