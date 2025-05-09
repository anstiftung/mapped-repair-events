<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class BrandsFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Abacom',
                'status' => 1
            ]
        ];
        parent::init();
    }

}
?>