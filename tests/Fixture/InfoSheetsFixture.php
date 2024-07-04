<?php

namespace App\Test\Fixture;

class InfoSheetsFixture extends AppFixture
{
    public $import = ['table' => 'info_sheets'];

    public array $records = [
        [
            'uid' => 7,
            'status' => APP_ON,
            'event_uid' => 6
        ],
    ];
}
?>