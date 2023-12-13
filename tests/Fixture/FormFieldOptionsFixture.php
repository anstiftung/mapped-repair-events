<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class FormFieldOptionsFixture extends TestFixture
{
    public $import = ['table' => 'form_field_options', 'connection' => 'default'];

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'form_field_id' => 1,
                'value' => 1,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 2,
                'form_field_id' => 1,
                'value' => 2,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 3,
                'form_field_id' => 1,
                'value' => 3,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 4,
                'form_field_id' => 1,
                'value' => 4,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 5,
                'form_field_id' => 1,
                'value' => 5,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 8,
                'form_field_id' => 4,
                'value' => 1,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 9,
                'form_field_id' => 4,
                'value' => 2,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 10,
                'form_field_id' => 4,
                'value' => 3,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 11,
                'form_field_id' => 3,
                'value' => 1,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 12,
                'form_field_id' => 3,
                'value' => 2,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 13,
                'form_field_id' => 3,
                'value' => 3,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 14,
                'form_field_id' => 3,
                'value' => 4,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 15,
                'form_field_id' => 5,
                'value' => 1,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 16,
                'form_field_id' => 5,
                'value' => 2,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 17,
                'form_field_id' => 5,
                'value' => 3,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 18,
                'form_field_id' => 5,
                'value' => 4,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 19,
                'form_field_id' => 5,
                'value' => 5,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 20,
                'form_field_id' => 5,
                'value' => 6,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 21,
                'form_field_id' => 6,
                'value' => 7,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 22,
                'form_field_id' => 6,
                'value' => 8,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 23,
                'form_field_id' => 5,
                'value' => 9,
                'name' => '',
                'status' => 1,
            ],
            [
                'id' => 24,
                'form_field_id' => 5,
                'value' => 10,
                'name' => '',
                'status' => 1,
            ],
        ];
        parent::init();
    }

}
?>