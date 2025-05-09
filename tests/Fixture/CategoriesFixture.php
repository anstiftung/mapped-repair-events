<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class CategoriesFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'id' => '87',
                'name' => 'Elektro Sonstiges',
                'icon' => 'elektro',
                'status' => APP_ON,
                'parent_id' => 1,
            ],
            [
                'id' => '630',
                'name' => '3D-Reparatur',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
            ],
            [
                'id' => '1',
                'name' => 'Elektro',
                'icon' => 'elektro',
                'status' => APP_ON,
                'parent_id' => null,
            ]
        ];
        parent::init();
    }
}
?>