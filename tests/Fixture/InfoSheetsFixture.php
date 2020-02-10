<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class InfoSheetsFixture extends TestFixture
{
    public $import = ['table' => 'info_sheets', 'connection' => 'default'];
    
    public $records = [
        [
            'uid' => 7,
            'status' => APP_ON,
            'event_uid' => 6
        ],
    ];
}
?>