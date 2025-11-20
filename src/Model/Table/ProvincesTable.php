<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use App\Model\Entity\Province;

class ProvincesTable extends Table
{

    public function initialize(array $config): void
    {
        $this->belongsTo('Countries', [
            'foreignKey' => 'country_code',
        ]);
    }

    public function findByName(string $name): ?Province {
        $province = $this->find()->where([
            'OR' =>
                [
                    $this->aliasField('name') => $name,
                    'FIND_IN_SET(:long_name, alternative_names) !=' => 0,
                ],
        ])
        ->bind(':long_name', $name, 'string')
        ->first();
        return $province;
    }

    /**
     * @param array<int, int> $provinceCountsMap
     * @return array<array<string>>
     */
    public function getForDropdown(array $provinceCountsMap): array
    {
        $provinces = $this->find('all',
            order: [
                'Countries.name_de' => 'ASC',
                'Provinces.name' => 'ASC',
            ],
            contain: [
                'Countries',
            ],
        );

        $preparedProvinces = [];
        foreach($provinces as $province) {
            if (!isset($provinceCountsMap[$province->id])) {
                continue;
            }
            $preparedProvinces[$province->country->name_de][$province->id] = $province->name . ' (' . $provinceCountsMap[$province->id] . ')';
        }
        return $preparedProvinces;
    }
}

?>