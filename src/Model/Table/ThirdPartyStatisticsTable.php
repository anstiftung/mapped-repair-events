<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

class ThirdPartyStatisticsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    /**
     * @return array<int, \App\Model\Entity\ThirdPartyStatistic>
     */
    public function getSumsByDate(string $dateFrom, string $dateTo): array
    {
        $query = $this->find();
        $query->where(['ThirdPartyStatistics.date_from >= ' => new DateTime($dateFrom)]);
        $query->where(['ThirdPartyStatistics.date_to <= ' => new DateTime($dateTo)]);
        $query->select(
            ['sumRepaired' => $query->func()->sum('ThirdPartyStatistics.repaired')]
        );
        $query->select('ThirdPartyStatistics.category_id');
        $query->groupBy('ThirdPartyStatistics.category_id');
        return $query->toArray();
    }

    /**
     * @param \App\Model\Entity\ThirdPartyStatistic[] $sums
     * @return array<int, float>
     */
    public function sumUpForMainCategory(array $sums): array
    {
        $preparedSums = [];
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        foreach($sums as $sum) {
            $category = $categoriesTable->find('all',
                conditions: [
                    'Categories.id' => $sum->category_id,
                ]
            )->first();
            if (in_array($category->parent_id, Configure::read('AppConfig.mainCategoryIdsWhereSubCategoriesAreShown'))) {
                if (!isset($preparedSums[$category->id])) {
                    $preparedSums[$category->id] = 0;
                }
                $preparedSums[$category->id] += $sum->sumRepaired;
            } else {
                if (!isset($preparedSums[$category->id])) {
                    $preparedSums[$category->parent_id] = 0;
                }
                $preparedSums[$category->parent_id] += $sum->sumRepaired;
            }
        }
        return $preparedSums;
    }

    /**
     * @param array<int, float> $sums
     * @return list<array<string, mixed>>
     */
    public function bindCategoryDataToSums(array $sums): array
    {
        $preparedSums = [];
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        foreach($sums as $categoryId => $sum) {
            $category = $categoriesTable->find('all',
                conditions: [
                    'Categories.id' => $categoryId,
                ],
            )->first();
            $preparedSums[] = [
                'name' => $category->name,
                'id' => $category->id,
                'repaired' => $sum,
            ];
        }
        return $preparedSums;
    }

}