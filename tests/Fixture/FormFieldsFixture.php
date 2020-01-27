<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FormFieldsFixture extends TestFixture
{
    public $import = ['table' => 'form_fields', 'connection' => 'default'];
    
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Stromversorgung',
                'identifier' => 'power_supply',
                'status' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Fehler gefunden?',
                'identifier' => 'defect_found',
                'status' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Reparatur',
                'identifier' => 'defect_found_reason',
                'status' => 1,
            ],
            [
                'id' => 4,
                'name' => 'vertagt weil...',
                'identifier' => 'repair_postponed_reason',
                'status' => 1,
            ],
            [
                'id' => 5,
                'name' => 'nicht erfolgt weil...',
                'identifier' => 'no_repair_reason',
                'status' => 1,
            ],
            [
                'id' => 6,
                'name' => 'Abbruch: Gerät darf nicht mehr benutzt werden',
                'identifier' => 'device_must_not_be_used_anymore',
                'status' => 1,
            ],
        ];
        parent::init();
    }
    
}
?>