<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class WorknewsFixture extends TestFixture
{
    public $import = ['table' => 'worknews', 'connection' => 'default'];
    
    public $records = [
        [
            'workshop_uid' => 2,
            'email' => 'worknews-test@mailinator.com',
            'confirm' => 'ok'
        ]
    ];
    
}
?>