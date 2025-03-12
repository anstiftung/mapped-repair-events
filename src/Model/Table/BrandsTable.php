<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;

class BrandsTable extends Table
{

    public array $allowedBasicHtmlFields = [];
    public string $name_de = 'Marke';

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
    }

    public function setApprovedMultiple(array $brandIds): void
    {
        if (empty($brandIds)) {
            return;
        }
        $this->updateAll(
            ['status' => APP_ON],
            ['id IN' => $brandIds]
        );
    }

    public function getForDropdown()
    {

        $brands = $this->find('all',
        fields: [
            'Brands.id',
            'Brands.name',
            'Brands.status'
        ],
        order: [
            'LOWER(Brands.name)' => 'ASC'
        ],
        conditions: [
            'Brands.status > ' . APP_DELETED
        ]);

        $preparedBrands = [];
        foreach($brands as $brand) {
            $name = $brand->name;
            if ($brand->status == APP_OFF) {
                $name .= ' (unbestätigt)';
            }
            $preparedBrands[$brand->id] = $name;
        }

        return $preparedBrands;
    }

}

?>