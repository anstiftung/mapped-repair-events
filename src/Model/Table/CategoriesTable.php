<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Cake\ORM\Query\SelectQuery;
use App\Model\Traits\ApproveMultipleTrait;

class CategoriesTable extends Table
{

    use ApproveMultipleTrait;

    public array $allowedBasicHtmlFields = [];
    public string $name_de = 'Kategorie';

    public function initialize(array $config): void
    {
        $this->addBehavior('Tree');
        $this->addBehavior('Timestamp');
        parent::initialize($config);
        $this->belongsTo('ParentCategories', [
            'className' => 'Categories',
            'foreignKey' => 'parent_id'
        ]);
        $this->belongsTo('OwnerUsers', [
            'className' => 'Users',
            'foreignKey' => 'owner'
        ]);
        $this->belongsTo('OrdsCategories', [
            'foreignKey' => 'ords_category_id'
        ]);
        $this->belongsToMany('Workshops', [
            'through' => 'WorkshopsCategories',
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'workshop_uid',
            'sort' => [
                'Workshops.name' => 'ASC',
            ],
        ]);
        $this->belongsToMany('Users', [
            'through' => 'UsersCategories',
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'user_uid',
            'sort' => [
                'Users.firstname' => 'ASC',
            ],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('name', 'Bitte trage den Namen ein.');
        return $validator;
    }

    public function getInfoSheetCount(int $categoryId): int
    {
        $infoSheetTable = TableRegistry::getTableLocator()->get('InfoSheets');
        $result = $infoSheetTable->find('all',
            conditions: [
                $infoSheetTable->aliasField('category_id') => $categoryId,
                $infoSheetTable->aliasField('status') => APP_ON,
            ])->count();

        return $result;
    }

    public function getMaterialFootprintByParentCategoryId(int $parentCategoryId): float
    {
        $category = $this->find('all', conditions: [
            'Categories.parent_id' => $parentCategoryId,
        ])->first();

        $result = 0;
        if (!empty($category)) {
            $result = $category->material_footprint;
        }

        return (float) $result;
    }

    public function getCarbonFootprintByParentCategoryId(int $parentCategoryId): float
    {
        $category = $this->find('all', conditions: [
            'Categories.parent_id' => $parentCategoryId,
        ])->first();

        $result = 0;
        if (!empty($category)) {
            $result = $category->carbon_footprint;
        }

        return (float) $result;
    }

    /**
     * @return array<array<string>>
     */
    public function getForSubcategoryDropdown(): array
    {
        $categories = $this->find('threaded',
        conditions: [
            'Categories.visible_on_platform' => APP_ON,
            'Categories.status > ' . APP_DELETED
        ],
        order: [
            'Categories.status' => 'DESC',
            'Categories.icon' => 'ASC'
        ])
        ->where(function ($exp, $query) {
            return $exp->or([
                'Categories.parent_id IS NULL',
                'Categories.parent_id NOT IN' => Configure::read('AppConfig.mainCategoryIdsWhereSubCategoriesAreShown')
            ]);
        });
        $preparedCategories = [];
        foreach($categories as $category) {
            $subCategories = [];
            foreach($category->children as $subCategory) {
                $name = $subCategory->name;
                if ($subCategory->status == APP_OFF) {
                    $name .= ' (unbestätigt)';
                }
                $subCategories[$subCategory->id] = $name;
            }
            $preparedCategories[$category->name] = $subCategories;
        }

        return $preparedCategories;

    }

    public function calculateMaterialFootprint(float $repairedCount, float $materialFootprintFactor): float
    {
        $savedEnergyPart = 0.3;
        return $repairedCount * $materialFootprintFactor * $savedEnergyPart;
    }

    public function calculateCarbonFootprint(float $repairedCount, float $carbonFootprintFactor): float
    {
        $savedEnergyPart = 0.3;
        return $repairedCount * $carbonFootprintFactor * $savedEnergyPart;
    }

    /**
     * @return array<array<string, string|int|float>>
     */
    public function getCategoriesForStatisticsGlobal(): array
    {

        $categories = [];

        $mainCategories = $this->getMainCategoriesForFrontend();
        foreach($mainCategories as $category) {
            $categories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'carbon_footprint' => $category->carbon_footprint,
                'material_footprint' => $category->material_footprint,
            ];
        }

        $thirdPartySubCategories = $this->find('all',
        conditions: [
            'Categories.parent_id IN' => Configure::read('AppConfig.mainCategoryIdsWhereSubCategoriesAreShown')
        ],
        order: [
            'Categories.id' => 'ASC'
        ]);
        foreach($thirdPartySubCategories as $category) {
            $categories[] = [
                'id' => $category->id,
                'name' => $category->name,
                'carbon_footprint' => $category->carbon_footprint,
                'material_footprint' => $category->material_footprint,
            ];
        }

        $categories = Hash::sort($categories, '{n}.name');

        return $categories;
    }

    /**
     * @return array<int, \App\Model\Entity\Category>
     */
    public function getMainCategoriesForFrontendIndexedById(): array
    {
        return $this->getMainCategoriesForFrontend()->formatResults(function ($results) {
            return $results->indexBy('id');
        })->toArray();
    }

    public function getMainCategoriesForFrontend(): SelectQuery
    {
        $categories = $this->find('all',
        conditions: [
            'Categories.parent_id IS NULL',
            'Categories.visible_on_platform' => APP_ON
        ],
        order: [
            'Categories.name' => 'ASC'
        ]);
        return $categories;
    }

    /**
     * @param array<int> $visibleOnPlatform
     * @return array<int, string>
     */
    public function getForDropdown(array $visibleOnPlatform): array
    {

        $categories = $this->find('all',
        conditions: [
            'Categories.parent_id IS NULL',
            'Categories.visible_on_platform IN' => $visibleOnPlatform,
        ],
        fields: [
            'Categories.id',
            'Categories.name',
        ],
        order: [
            'LOWER(Categories.name)' => 'ASC',
        ]);

        $preparedCategories = [];
        foreach($categories as $category) {
            $preparedCategories[$category->id] = $category->name;
        }

        return $preparedCategories;
    }

}

?>