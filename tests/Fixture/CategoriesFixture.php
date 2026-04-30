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
                'carbon_footprint' => 0,
                'material_footprint' => 0,
                'parent_id' => null,
            ],
            [
                'id' => self::MAIN_CATEGORY_ID_THREE_D_REPAIR,
                'name' => '3D-Reparatur',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
                'carbon_footprint' => 0,
                'material_footprint' => 0,
                'parent_id' => null,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_ELECTRO_SONSTIGES,
                'name' => 'Elektro Sonstiges',
                'icon' => 'elektro',
                'status' => APP_ON,
                'carbon_footprint' => 10,
                'material_footprint' => 20,
                'parent_id' => self::MAIN_CATEGORY_ID_ELECTRO,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_ELECTRO_KLEINGERAETE,
                'name' => 'Elektro Kleingeräte',
                'icon' => 'elektro',
                'status' => APP_ON,
                'carbon_footprint' => 10,
                'material_footprint' => 20,
                'parent_id' => self::MAIN_CATEGORY_ID_ELECTRO,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_ELECTRO_AUDIO,
                'name' => 'Elektro Audio',
                'icon' => 'elektro',
                'status' => APP_ON,
                'carbon_footprint' => 10,
                'material_footprint' => 20,
                'parent_id' => self::MAIN_CATEGORY_ID_ELECTRO,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_DRUCKER,
                'name' => '3D-Drucker',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
                'carbon_footprint' => 30,
                'material_footprint' => 40,
                'parent_id' => self::MAIN_CATEGORY_ID_THREE_D_REPAIR,
            ],
            [
                'id' => self::SUB_CATEGORY_ID_THREE_D_REPAIR_3D_STIFTE,
                'name' => '3D-Stifte',
                'icon' => 'drei-d-reparatur',
                'status' => APP_ON,
                'carbon_footprint' => 30,
                'material_footprint' => 40,
                'parent_id' => self::MAIN_CATEGORY_ID_THREE_D_REPAIR,
            ],
        ];
        parent::init();
    }
}
?>