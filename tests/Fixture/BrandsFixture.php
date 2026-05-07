<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class BrandsFixture extends AppFixture
{

    public const BRAND_ABACOM_ID = 1;

    public function init(): void
    {
        $this->records = [
            [
                'id' => self::BRAND_ABACOM_ID,
                'name' => 'Abacom',
                'status' => 1
            ]
        ];
        parent::init();
    }

}
?>