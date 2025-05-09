<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ProvincesFixture extends TestFixture
{

    public function init(): void {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Bayern',
                'country_code' => 'DE',
            ]
        ];
        parent::init();
    }

}
?>