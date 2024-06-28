<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class ProvincesTable extends Table
{

    public function initialize(array $config): void
    {
        $this->belongsTo('Countries', [
            'foreignKey' => 'country_code',
            'sort' => [
                'Countries.name' => 'ASC'
            ]
        ]);
    }

    public function getForDropdown()
    {
        $provinces = $this->find('all',
            order: [
                'Provinces.name' => 'ASC'
            ],
            contain: [
                'Countries',
            ],
        );

        $preparedProvinces = [];
        foreach($provinces as $province) {
            $preparedProvinces[$province->id] = $province->name;
        }
        return $preparedProvinces;
    }
}

?>