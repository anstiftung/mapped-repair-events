<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Validation\Validator;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;

class InfoSheetsTable extends AppTable
{

    public string $name_de = 'Laufzettel';

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->belongsTo('Events', [
            'foreignKey' => 'event_uid'
        ]);
        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id'
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id'
        ]);
        $this->belongsToMany('FormFieldOptions', [
            'joinTable' => 'info_sheets_form_field_options',
            'foreignKey' => 'info_sheet_id',
            'targetForeignKey' => 'form_field_option_id'
        ]);
    }

    public function validationAdmin(Validator $validator): Validator
    {
        return $this->validationDefault($validator);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('category_id', 'Bitte wähle eine Kategorie aus.');
        $validator = $this->getNumberRangeValidator($validator, 'device_age', 0, 400);
        $validator->allowEmptyString('device_age');
        $validator = $this->getNumberRangeValidator($validator, 'visitor_age', 0, 120);
        $validator->allowEmptyString('visitor_age');

        // add subcategories
        $validator->notEmptyString('new_subcategory_parent_id', 'Bitte wähle die Oberkategorie aus.', function($context): bool {
            return $this->isSubcategoryCreateModeEnabled($context);
        });
        $validator->notEmptyString('new_subcategory_name', 'Bitte gib den Namen der Unterkategorie an.', function($context): bool {
            return $this->isSubcategoryCreateModeEnabled($context);
        });
        $validator->minLength('new_subcategory_name', 3, 'Mindestens 3 Zeichen bitte.', function($context): bool {
            return $this->isSubcategoryCreateModeEnabled($context);
        });

        // add brands
        $validator->notEmptyString('new_brand_name', 'Bitte gib den Namen der Marke an.', function($context): bool {
            return $this->isBrandCreateModeEnabled($context);
        });
        $validator->minLength('new_brand_name', 3, 'Mindestens 3 Zeichen bitte.', function($context): bool {
            return $this->isBrandCreateModeEnabled($context);
        });

        $validator->notEmptyString('defect_description', 'Bitte gib die Fehlerbeschreibung an (maximal 1.000 Zeichen).', function($context): bool {
            return $this->isDefectFound($context);
        });
        $validator->maxLength('defect_description', 1000, 'Maximal 1.000 Zeichen bitte.');

        $validator->allowEmptyString('no_repair_reason_text');
        $validator->maxLength('no_repair_reason_text', 200, 'Maximal 200 Zeichen bitte.');

        $validator->notEmptyString('defect_found_reason', 'Bitte gib Details zur Reparatur an.', function($context): bool {
            return $this->isDefectFound($context);
        });

        $validator->notEmptyString('repair_postponed_reason', 'Bitte gib an, warum die Reparatur vertagt wurde.', function($context): bool {
            return $this->isRepairPostponed($context);
        });

        $validator->notEmptyString('no_repair_reason', 'Bitte gib an, warum nicht repariert wurde.', function($context): bool {
            return $this->isNoRepair($context);
        });

        return $validator;
    }

    private function prepareStatisticsDataGlobal(?string $dateFrom=null, ?string $dateTo=null): SelectQuery
    {
        $query = $this->find();
        $query->contain([
            'Events'
        ]);
        $query->where([
            'InfoSheets.status' => APP_ON,
        ]);

        if (!is_null($dateFrom) && !is_null($dateTo)) {
            $dateFrom = new DateTime($dateFrom);
            $dateTo = new DateTime($dateTo);
            $query->where(function($exp) use ($dateFrom, $dateTo) {
                return $exp->between('Events.datumstart', $dateFrom, $dateTo, 'date');
            });
        }

        return $query;
    }

    private function prepareStatisticsDataByWorkshopUid(int $workshopUid, ?string $dateFrom, ?string $dateTo): SelectQuery
    {
        $query = $this->prepareStatisticsDataGlobal($dateFrom, $dateTo);
        $query->where([
            'Events.workshop_uid' => $workshopUid,
        ]);
        return $query;
    }

    private function prepareStatisticsDataGlobalByMainCategory(int $categoryId, ?string $dateFrom, ?string $dateTo): SelectQuery
    {
        $query = $this->prepareStatisticsDataGlobal($dateFrom, $dateTo);
        $query->contain([
            'Categories'
        ]);
        $query->where([
            'Categories.parent_id' => $categoryId,
        ]);
        return $query;
    }

    private function prepareStatisticsDataByMainCategory(int $workshopUid, int $categoryId, ?string $dateFrom, ?string $dateTo): SelectQuery
    {
        $query = $this->prepareStatisticsDataByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $query->contain([
            'Categories'
        ]);
        $query->where([
            'Categories.parent_id' => $categoryId,
        ]);
        return $query;
    }

    public function getRepairedGlobalByMainCategoryId(int $categoryId, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobalByMainCategory($categoryId, $dateFrom, $dateTo);
        $query = $this->setRepairedConditions($query);
        return $query->count();
    }

    public function getRepairableGlobalByMainCategoryId(int $categoryId, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobalByMainCategory($categoryId, $dateFrom, $dateTo);
        $query = $this->setRepairableConditions($query);
        return $query->count();
    }

    public function getNotRepairedGlobalByMainCategoryId(int $categoryId, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobalByMainCategory($categoryId, $dateFrom, $dateTo);
        $query = $this->setNotRepairedConditions($query);
        return $query->count();
    }

    public function getRepairedByMainCategoryId(int $workshopUid, int $categoryId, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataByMainCategory($workshopUid, $categoryId, $dateFrom, $dateTo);
        $query = $this->setRepairedConditions($query);
        return $query->count();
    }

    public function getRepairableByMainCategoryId(int $workshopUid, int $categoryId, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataByMainCategory($workshopUid, $categoryId, $dateFrom, $dateTo);
        $query = $this->setRepairableConditions($query);
        return $query->count();
    }

    public function getNotRepairedByMainCategoryId(int $workshopUid, int $categoryId, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataByMainCategory($workshopUid, $categoryId, $dateFrom, $dateTo);
        $query = $this->setNotRepairedConditions($query);
        return $query->count();
    }

    public function getRepaired(?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobal($dateFrom, $dateTo);
        $query = $this->setRepairedConditions($query);
        return $query->count();
    }

    public function getRepairable(?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobal($dateFrom, $dateTo);
        $query = $this->setRepairableConditions($query);
        return $query->count();
    }

    public function getNotRepaired(?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobal($dateFrom, $dateTo);
        $query = $this->setNotRepairedConditions($query);
        return $query->count();
    }

    public function getRepairedByWorkshopUid(int $workshopUid, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $query = $this->setRepairedConditions($query);
        return $query->count();
    }

    public function getRepairableByWorkshopUid(int $workshopUid, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $query = $this->setRepairableConditions($query);
        return $query->count();
    }

    public function getNotRepairedByWorkshopUid(int $workshopUid, ?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataByWorkshopUid($workshopUid, $dateFrom, $dateTo);
        $query = $this->setNotRepairedConditions($query);
        return $query->count();
    }

    public function workshopInfoSheetsCount(int $workshopUid): int
    {
        $count = 0;
        $count += $this->getRepairedByWorkshopUid($workshopUid, null, null);
        $count += $this->getRepairableByWorkshopUid($workshopUid, null, null);
        $count += $this->getNotRepairedByWorkshopUid($workshopUid, null, null);
        return $count;
    }

    public function getWorkshopCountWithInfoSheets(?string $dateFrom, ?string $dateTo): int
    {
        $query = $this->prepareStatisticsDataGlobal($dateFrom, $dateTo);
        $query->select(['Events.workshop_uid']);
        $query->groupBy('Events.workshop_uid');
        return $query->count();
    }

    private function setNotRepairedConditions(SelectQuery $query): SelectQuery
    {
        $query->where(function ($exp, $query) {
            return $exp->or([
                $query->newExpr()->and([
                    'InfoSheets.defect_found_reason' => 3,
                    'InfoSheets.no_repair_reason IN' => [2,3,4,6,10,11,12,13,14,15],
                ]),
                $query->newExpr()->and([
                    'InfoSheets.defect_found_reason' => 4,
                ]),
            ]);
        });
        return $query;
    }

    private function setRepairableConditions(SelectQuery $query): SelectQuery
    {
        $query->where(function ($exp, $query) {
            return $exp->or([
                $query->newExpr()->and([
                    'InfoSheets.defect_found_reason' => 2,
                    'InfoSheets.repair_postponed_reason IN' => [1,2,3],
                ]),
                $query->newExpr()->and([
                    'InfoSheets.defect_found_reason' => 3,
                    'InfoSheets.no_repair_reason IN' => [1,5,9],
                ]),
            ]);
        });
        return $query;
    }

    private function setRepairedConditions(SelectQuery $query): SelectQuery
    {
        $query->where([
            'InfoSheets.defect_found_reason' => 1,
        ]);
        return $query;
    }

    private function isSubcategoryCreateModeEnabled(array $context): bool
    {
        return !empty($context['data']) && $context['data']['category_id'] == -1;
    }

    private function isBrandCreateModeEnabled(array $context): bool
    {
        return !empty($context['data']) && $context['data']['brand_id'] == -1;
    }

    private function isDefectFound(array $context): bool
    {
        return !empty($context['data']) && !($context['data']['defect_found_reason'] == 3 && $context['data']['no_repair_reason'] == 15);
    }

    private function isRepairPostponed(array $context): bool
    {
        return !empty($context['data']) && $context['data']['defect_found_reason'] == 2;
    }

    private function isNoRepair(array $context): bool
    {
        return !empty($context['data']) && $context['data']['defect_found_reason'] == 3;
    }

}

?>
