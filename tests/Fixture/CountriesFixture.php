<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class CountriesFixture extends AppFixture
{

    public function init(): void
    {
        $this->records = [
            [
                'code' => 'DE',
                'name_de' => 'Deutschland'
            ]
        ];
        parent::init();
    }

}
?>