<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class ThirdPartyStatisticsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    public function getSumsByDate($dateFrom, $dateTo)
    {

        $query = $this->find();
        $dateFrom = new Time($dateFrom);
        $dateTo = new Time($dateTo);
        $query->where(['ThirdPartyStatistics.date_from >= ' => $dateFrom]);
        $query->where(['ThirdPartyStatistics.date_to <= ' => $dateTo]);
        $query->select(
            ['sumRepaired' => $query->func()->sum('ThirdPartyStatistics.repaired')]
        );
        $query->select('ThirdPartyStatistics.category_id');
        $query->group('ThirdPartyStatistics.category_id');

        return $query->toArray();

    }

    public function sumUpForMainCategory($sums)
    {
        $preparedSums = [];
        $this->Categories = TableRegistry::getTableLocator()->get('Categories');
        foreach($sums as $sum) {
            $category = $this->Categories->find('all', [
                'conditions' => [
                    'Categories.id' => $sum->category_id
                ]
            ])->first();
            if (in_array($category->parent_id, Configure::read('AppConfig.mainCategoryIdsWhereSubCategoriesAreShown'))) {
                @$preparedSums[$category->id] += $sum->sumRepaired;
            } else {
                @$preparedSums[$category->parent_id] += $sum->sumRepaired;
            }
        }
        return $preparedSums;
    }

    public function bindCategoryDataToSums($sums)
    {
        $preparedSums = [];
        $this->Categories = TableRegistry::getTableLocator()->get('Categories');
        foreach($sums as $categoryId => $sum) {
            $category = $this->Categories->find('all', [
                'conditions' => [
                    'Categories.id' => $categoryId
                ]
            ])->first();
            $preparedSums[] = [
                'name' => $category->name,
                'id' => $category->id,
                'repaired' => $sum
            ];
        }
        return $preparedSums;
    }

    public function getCategoryNames($sums)
    {
        $result = [];
        foreach($sums as $sum) {
            $result[] = $sum['name'];
        }
        return $result;
    }

    public function getSumsRepaired($sums)
    {
        $result = [];
        foreach($sums as $sum) {
            $result[] = $sum['repaired'];
        }
        return $result;
    }

}