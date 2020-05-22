<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class BrandsTable extends Table
{

    public $allowedBasicHtmlFields = [];
    public $name_de = 'Marke';

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
    }

    /**
     * @return array
     */
    public function getForDropdown()
    {

        $brands = $this->find('all', [
            'fields' => [
                'Brands.id',
                'Brands.name',
                'Brands.status'
            ],
            'order' => [
                'LOWER(Brands.name)' => 'ASC'
            ],
            'conditions' => [
                'Brands.status > ' . APP_DELETED
            ]
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