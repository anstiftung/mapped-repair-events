<?php
declare(strict_types=1);

namespace App\Controller\Traits\Api;

use App\Model\Entity\Province;
use App\Model\Table\CategoriesTable;
use App\Model\Table\InfoSheetsTable;
use App\Model\Table\ProvincesTable;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Utility\Hash;

/** @mixin \App\Controller\ApiController */
trait ApiGetStatisticsTrait
{
    public function getStatistics(): ?Response
    {
        $dateFrom = $this->getValidatedDateQueryParam('dateFrom');
        $dateTo = $this->getValidatedDateQueryParam('dateTo');
        if ($dateFrom === false || $dateTo === false) {
            return $this->getResponse()->withStatus(400)->withType('json')->withStringBody('{"error":"dateFrom and dateTo must be valid dates"}');
        }

        $city = $this->getRequest()->getQuery('city');
        $city = is_string($city) && $city !== '' ? $city : null;

        $provinceName = $this->getRequest()->getQuery('province');
        $provinceName = is_string($provinceName) && $provinceName !== '' ? $provinceName : null;
        $province = $this->resolveProvince($provinceName);
        if ($provinceName !== null && $province === null) {
            return $this->getResponse()->withStatus(404)->withType('json')->withStringBody('{"error":"province not found"}');
        }

        /** @var InfoSheetsTable $infoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        $dataRepaired = $infoSheetsTable->getRepaired($dateFrom, $dateTo, $city, $province);
        $dataRepairable = $infoSheetsTable->getRepairable($dateFrom, $dateTo, $city, $province);
        $dataNotRepaired = $infoSheetsTable->getNotRepaired($dateFrom, $dateTo, $city, $province);
        $initiativeCount = $infoSheetsTable->getWorkshopCountWithInfoSheets($dateFrom, $dateTo, $city, $province);
        $categories = $this->getPreparedStatisticsCategories($dateFrom, $dateTo, $city, $province);

        $counts = [
            'repaired' => $dataRepaired,
            'repairable' => $dataRepairable,
            'notRepaired' => $dataNotRepaired,
            'total' => $dataRepaired + $dataRepairable + $dataNotRepaired,
        ];

        $carbonFootprint = 0.0;
        $materialFootprint = 0.0;
        foreach($categories as $category) {
            $carbonFootprint += $category['environmentalImpact']['carbonFootprint'];
            $materialFootprint += $category['environmentalImpact']['materialFootprint'];
        }

        $statistics = [
            'filters' => [
                'city' => $city,
                'province' => $province?->name,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ],
            'counts' => $counts,
            'environmentalImpact' => [
                'carbonFootprint' => round($carbonFootprint, 3),
                'materialFootprint' => round($materialFootprint, 3),
            ],
            'initiativeCount' => $initiativeCount,
            'categories' => $categories,
        ];

        $this->set([
            'statistics' => $statistics,
        ]);
        $this->viewBuilder()->setOption('serialize', ['statistics']);
        return null;
    }

    /**
     * @return list<array{label: string, counts: array{repaired: int, repairable: int, notRepaired: int, total: int}, environmentalImpact: array{carbonFootprint: float, materialFootprint: float}, initiativeCount: int}>
     */
    private function getPreparedStatisticsCategories(?string $dateFrom, ?string $dateTo, ?string $city, ?Province $province): array
    {
        /** @var InfoSheetsTable $infoSheetsTable */
        $infoSheetsTable = $this->getTableLocator()->get('InfoSheets');
        /** @var CategoriesTable $categoriesTable */
        $categoriesTable = $this->getTableLocator()->get('Categories');
        $categoriesForStatistics = $categoriesTable->getMainCategoriesForFrontend()->toArray();

        $categories = [];
        foreach($categoriesForStatistics as $category) {
            $repaired = $infoSheetsTable->getRepairedGlobalByMainCategoryId($category->id, $dateFrom, $dateTo, $city, $province);
            $repairable = $infoSheetsTable->getRepairableGlobalByMainCategoryId($category->id, $dateFrom, $dateTo, $city, $province);
            $notRepaired = $infoSheetsTable->getNotRepairedGlobalByMainCategoryId($category->id, $dateFrom, $dateTo, $city, $province);
            $total = $repaired + $repairable + $notRepaired;
            if ($total === 0) {
                continue;
            }

            $carbonFootprint = $categoriesTable->getCarbonFootprintByParentCategoryId($category->id);
            $materialFootprint = $categoriesTable->getMaterialFootprintByParentCategoryId($category->id);

            $categories[] = [
                'label' => $category->name,
                'counts' => [
                    'repaired' => $repaired,
                    'repairable' => $repairable,
                    'notRepaired' => $notRepaired,
                    'total' => $total,
                ],
                'environmentalImpact' => [
                    'carbonFootprint' => round($categoriesTable->calculateCarbonFootprint($repaired, $carbonFootprint) / 1000, 3),
                    'materialFootprint' => round($categoriesTable->calculateMaterialFootprint($repaired, $materialFootprint) / 1000, 3),
                ],
                'initiativeCount' => $infoSheetsTable->getWorkshopCountGlobalByMainCategoryId($category->id, $dateFrom, $dateTo, $city, $province),
            ];
        }

        /** @var list<array{label: string, counts: array{repaired: int, repairable: int, notRepaired: int, total: int}, environmentalImpact: array{carbonFootprint: float, materialFootprint: float}, initiativeCount: int}> $sortedCategories */
        $sortedCategories = array_values(Hash::sort($categories, '{n}.label', 'asc'));

        return $sortedCategories;
    }

    private function getValidatedDateQueryParam(string $name): string|false|null
    {
        $value = $this->getRequest()->getQuery($name);
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            return false;
        }

        try {
            new DateTime($value);
        } catch (\Exception) {
            return false;
        }

        return $value;
    }

    private function resolveProvince(?string $provinceName): ?Province
    {
        if ($provinceName === null) {
            return null;
        }

        /** @var ProvincesTable $provincesTable */
        $provincesTable = $this->getTableLocator()->get('Provinces');
        $province = $provincesTable->findByName($provinceName);
        return $province instanceof Province ? $province : null;
    }
}