<?php
declare(strict_types=1);

namespace App\Test\Fixture;

class CategoriesFixture extends AppFixture
{

    public const int MAIN_CATEGORY_ID_ELECTRO = 1;
    public const int MAIN_CATEGORY_ID_THREE_D_REPAIR = 630;

    public const int SUB_CATEGORY_ID_ELECTRO_SONSTIGES = 87;
    public const int SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE = 88;
    public const int SUB_CATEGORY_ID_ELECTRO_AUDIO = 89;
    public const int SUB_CATEGORY_ID_THREE_D_REPAIR_3D_DRUCKER = 631;
    public const int SUB_CATEGORY_ID_THREE_D_REPAIR_3D_STIFTE = 632;

    public function init(): void
    {
        $this->records = [
            [
                'id' => self::MAIN_CATEGORY_ID_ELECTRO,
                'name' => 'Elektro',
                'icon' => 'elektro',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'parent_id' => null,
                'lft' => 1,
                'rght' => 8,
            ],
            [
                'id' => self::MAIN_CATEGORY_ID_THREE_D_REPAIR,
                'name' => '3D-Reparatur',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'parent_id' => null,
                'lft' => 9,
                'rght' => 14,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_ELECTRO_SONSTIGES,
                'name' => 'Elektro Sonstiges',
                'icon' => 'elektro',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'carbon_footprint' => 10,
                'material_footprint' => 20,
                'parent_id' => self::MAIN_CATEGORY_ID_ELECTRO,
                'lft' => 2,
                'rght' => 3,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE,
                'name' => 'Elektro Kleingeräte',
                'icon' => 'elektro',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'carbon_footprint' => 10,
                'material_footprint' => 20,
                'parent_id' => self::MAIN_CATEGORY_ID_ELECTRO,
                'lft' => 4,
                'rght' => 5,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_ELECTRO_AUDIO,
                'name' => 'Elektro Audio',
                'icon' => 'elektro',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'parent_id' => self::MAIN_CATEGORY_ID_ELECTRO,
                'lft' => 6,
                'rght' => 7,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_DRUCKER,
                'name' => '3D-Drucker',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'carbon_footprint' => 30,
                'material_footprint' => 40,
                'parent_id' => self::MAIN_CATEGORY_ID_THREE_D_REPAIR,
                'lft' => 10,
                'rght' => 11,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_STIFTE,
                'name' => '3D-Stifte',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
                'visible_on_platform' => APP_ON,
                'carbon_footprint' => 30,
                'material_footprint' => 40,
                'parent_id' => self::MAIN_CATEGORY_ID_THREE_D_REPAIR,
                'lft' => 12,
                'rght' => 13,
            ],
        ];
        parent::init();
    }
}
?>