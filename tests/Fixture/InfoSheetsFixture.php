<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class InfoSheetsFixture extends AppFixture
{

    public function init(): void {
        $this->records = [
            [
                'uid' => 7,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_SONSTIGES,
            ],
            [
                'uid' => 100,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE,
            ],
            [
                'uid' => 101,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_AUDIO,
            ],
            [
                'uid' => 102,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_ELECTRO_SONSTIGES,
            ],
            [
                'uid' => 103,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_DRUCKER,
            ],
            [
                'uid' => 104,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_STIFTE,
            ],
            [
                'uid' => 105,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_DRUCKER,
            ],
            [
                'uid' => 106,
                'status' => APP_ON,
                'event_uid' => 6,
                'category_id' => CategoriesFixture::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_STIFTE,
            ],
        ];
        parent::init();
    }
}
?>