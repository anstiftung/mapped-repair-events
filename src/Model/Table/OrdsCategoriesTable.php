<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdsCategoriesTable extends Table
{

    /**
     * @var string[]
     */
    public array $allowedBasicHtmlFields = [];

    public string $name_de = 'ORDS-Kategorie';

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        return $validator;
    }

    /**
     * @return array<int, string>
     */
    public function getForDropdown(): array
    {

        $categories = $this->find('all',
        conditions: [
            'OrdsCategories.status' => APP_ON,
        ],
        fields: [
            'OrdsCategories.id',
            'OrdsCategories.name'
        ],
        order: [
            'LOWER(OrdsCategories.name)' => 'ASC'
        ]);

        $preparedCategories = [];
        foreach($categories as $category) {
            $preparedCategories[$category->id] = $category->name;
        }

        return $preparedCategories;
    }

}

?>